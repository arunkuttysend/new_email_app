<?php

namespace App\Jobs\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\SequenceSubscriberProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitializeCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(): void
    {
        // Get subscribers based on list/segment
        $query = $this->campaign->mailingList->subscribers()
            ->whereIn('status', ['subscribed', 'confirmed']);

        if ($this->campaign->segment_id) {
            // TODO: Apply segment filters
        }

        $subscribers = $query->get();

        if ($subscribers->isEmpty()) {
            $this->campaign->update([
                'status' => Campaign::STATUS_FAILED,
            ]);
            return;
        }

        // Create CampaignSend records
        foreach ($subscribers as $subscriber) {
            CampaignSend::create([
                'campaign_id' => $this->campaign->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'pending',
            ]);
        }

        // Check if campaign has a sequence
        $sequence = $this->campaign->sequence;

        if ($sequence) {
            // Initialize sequence progress for all subscribers
            foreach ($subscribers as $subscriber) {
                SequenceSubscriberProgress::create([
                    'sequence_id' => $sequence->id,
                    'subscriber_id' => $subscriber->id,
                    'current_step_order' => 0,
                    'next_send_at' => now(), // First step sends immediately
                    'status' => 'active',
                ]);
            }

            // Sequence emails will be sent by ProcessSequencesCommand
        } else {
            // Regular campaign - dispatch send jobs immediately
            $dailyLimit = $this->campaign->options['daily_limit'] ?? 1000;

            $sends = CampaignSend::where('campaign_id', $this->campaign->id)
                ->where('status', 'pending')
                ->limit($dailyLimit)
                ->get();

            foreach ($sends as $send) {
                SendCampaignEmailJob::dispatch($send);
            }
        }

        // Update campaign status
        $this->campaign->update([
            'status' => Campaign::STATUS_SENDING,
            'started_at' => now(),
        ]);
    }
}
