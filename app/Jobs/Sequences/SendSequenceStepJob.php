<?php

namespace App\Jobs\Sequences;

use App\Models\SequenceStepLog;
use App\Models\SequenceSubscriberProgress;
use App\Models\SequenceStep;
use App\Services\Mail\InboxRotationService;
use App\Services\Mail\MailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSequenceStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(
        public SequenceSubscriberProgress $progress,
        public SequenceStep $step
    ) {}

    public function handle(
        MailerService $mailerService,
        InboxRotationService $rotationService
    ): void {
        $sequence = $this->progress->sequence;
        $campaign = $sequence->campaign;
        $subscriber = $this->progress->subscriber;

        // Get sender accounts from campaign
        $senderAccounts = $campaign->options['sender_accounts'] ?? [];

        if (empty($senderAccounts)) {
            $this->markStepFailed('No sender accounts configured');
            return;
        }

        // Get next available inbox
        $inbox = $rotationService->getNextAvailableInbox($senderAccounts, $campaign);

        if (!$inbox) {
            $this->release(300); // Retry in 5 minutes
            return;
        }

        // Get thread ID from first step log for threading
        $firstLog = SequenceStepLog::where('sequence_id', $sequence->id)
            ->where('subscriber_id', $subscriber->id)
            ->where('step_id', $sequence->steps()->where('step_order', 1)->first()?->id)
            ->first();

        $threadId = $firstLog?->message_id;

        // Prepare email data
        $emailData = [
            'subject' => $this->step->subject,
            'html_content' => $this->step->html_content,
            'from_name' => $campaign->from_name,
            'from_email' => $campaign->from_email,
            'reply_to' => $campaign->reply_to,
            'thread_id' => $threadId, // For email threading
        ];

        // Send email
        $result = $mailerService->send($inbox, $subscriber, $emailData);

        if ($result['success']) {
            // Create step log
            $log = SequenceStepLog::create([
                'sequence_id' => $sequence->id,
                'step_id' => $this->step->id,
                'subscriber_id' => $subscriber->id,
                'message_id' => $result['message_id'],
                'thread_id' => $threadId ?? $result['message_id'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Calculate next send time
            $nextStep = $sequence->steps()
                ->where('step_order', '>', $this->step->step_order)
                ->orderBy('step_order')
                ->first();

            if ($nextStep) {
                $nextSendAt = $this->calculateNextSendTime($nextStep);
                
                $this->progress->update([
                    'current_step_id' => $this->step->id,
                    'current_step_order' => $this->step->step_order,
                    'next_send_at' => $nextSendAt,
                ]);
            } else {
                // Sequence completed
                $this->progress->update([
                    'current_step_id' => $this->step->id,
                    'current_step_order' => $this->step->step_order,
                    'status' => 'completed',
                ]);
            }

            // Increment inbox usage
            $rotationService->incrementUsage($inbox);
        } else {
            $this->markStepFailed($result['error']);
            
            if ($this->attempts() < $this->tries) {
                throw new \Exception($result['error']);
            }
        }
    }

    private function calculateNextSendTime(SequenceStep $step)
    {
        $waitValue = $step->wait_value;
        $waitUnit = $step->wait_unit;

        return match ($waitUnit) {
            'minutes' => now()->addMinutes($waitValue),
            'hours' => now()->addHours($waitValue),
            'days' => now()->addDays($waitValue),
            default => now()->addDays($waitValue),
        };
    }

    private function markStepFailed(string $error): void
    {
        SequenceStepLog::create([
            'sequence_id' => $this->progress->sequence_id,
            'step_id' => $this->step->id,
            'subscriber_id' => $this->progress->subscriber_id,
            'status' => 'failed',
        ]);

        $this->progress->update([
            'status' => 'failed',
            'stop_reason' => $error,
        ]);
    }
}
