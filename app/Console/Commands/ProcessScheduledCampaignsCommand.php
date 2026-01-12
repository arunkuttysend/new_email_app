<?php

namespace App\Console\Commands;

use App\Jobs\Campaigns\InitializeCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class ProcessScheduledCampaignsCommand extends Command
{
    protected $signature = 'campaigns:process-scheduled';
    protected $description = 'Process campaigns scheduled to start now';

    public function handle()
    {
        $this->info('Checking for scheduled campaigns...');

        // Find campaigns scheduled to start now or in the past
        $campaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns scheduled to start.');
            return Command::SUCCESS;
        }

        $this->info("Found {$campaigns->count()} campaign(s) ready to start");

        foreach ($campaigns as $campaign) {
            $this->info("Starting campaign: {$campaign->name}");
            
            // Update status to prevent duplicate processing
            $campaign->update(['status' => 'active']);
            
            // Dispatch initialization job
            InitializeCampaignJob::dispatch($campaign);
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }
}
