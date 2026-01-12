<?php

namespace App\Jobs\Campaigns;

use App\Models\CampaignSend;
use App\Services\Mail\InboxRotationService;
use App\Services\Mail\MailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public CampaignSend $campaignSend
    ) {}

    public function handle(
        MailerService $mailerService,
        InboxRotationService $rotationService
    ): void {
        $campaign = $this->campaignSend->campaign;
        $subscriber = $this->campaignSend->subscriber;

        // Get sender accounts from campaign options
        $senderAccounts = $campaign->options['sender_accounts'] ?? [];

        if (empty($senderAccounts)) {
            $this->campaignSend->update([
                'status' => 'failed',
                'error' => 'No sender accounts configured',
            ]);
            return;
        }

        // Get next available inbox
        $inbox = $rotationService->getNextAvailableInbox($senderAccounts, $campaign);

        if (!$inbox) {
            $this->campaignSend->update([
                'status' => 'failed',
                'error' => 'No available inboxes (rate limit reached)',
            ]);
            // Re-queue for later
            $this->release(300); // Try again in 5 minutes
            return;
        }

        // Prepare email data
        $emailData = [
            'subject' => $campaign->subject,
            'html_content' => $campaign->content->html_content ?? '<p>No content</p>',
            'from_name' => $campaign->from_name,
            'from_email' => $campaign->from_email,
            'reply_to' => $campaign->reply_to,
            'campaign_send_id' => $this->campaignSend->id,
        ];

        // Send email
        $result = $mailerService->send(
            $inbox,
            $subscriber,
            $emailData,
            $campaign->id
        );

        if ($result['success']) {
            // Update campaign send record
            $this->campaignSend->update([
                'delivery_server_id' => $inbox->id,
                'message_id' => $result['message_id'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Increment inbox usage
            $rotationService->incrementUsage($inbox);

            // Update campaign stats
            $this->updateCampaignStats($campaign);
        } else {
            $this->campaignSend->update([
                'status' => 'failed',
                'error' => $result['error'],
            ]);

            // Throw exception to trigger retry
            if ($this->attempts() < $this->tries) {
                throw new \Exception($result['error']);
            }
        }
    }

    private function updateCampaignStats($campaign): void
    {
        $stats = $campaign->stats ?? [];
        $stats['sent'] = ($stats['sent'] ?? 0) + 1;
        $campaign->update(['stats' => $stats]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaignSend->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ]);
    }
}
