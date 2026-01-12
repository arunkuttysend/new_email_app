<?php
use App\Models\Campaign;
use App\Jobs\Campaigns\InitializeCampaignJob;
use Illuminate\Support\Facades\Artisan;

echo "\n--- FIXING CAMPAIGN 'test54444' ---\n";

$failed = Campaign::where('name', 'test54444')->first();
$good   = Campaign::where('name', 'arun')->first();

if (!$failed || !$good) {
    die("Campaigns not found.\n");
}

// 1. Copy Sender Accounts
$options = $failed->options;
$options['sender_accounts'] = $good->options['sender_accounts'] ?? [];
$failed->update([
    'options' => $options,
    'status'  => 'scheduled', 
    'scheduled_at' => now(), // Ready to go now
]);

echo "Updated 'test54444': Status=Scheduled, Senders Copied.\n";

// 2. Dispatch Initialize Job (This failed before, should work now)
echo "Dispatching InitializeCampaignJob...\n";
InitializeCampaignJob::dispatchSync($failed);

// 3. Check if it moved to 'sending'
$failed->refresh();
echo "New Status: {$failed->status}\n";

if ($failed->status === 'sending') {
    echo "Success! Initialization worked. Running sequence processor...\n";
    Artisan::call('sequences:process');
    echo Artisan::output();
} else {
    echo "Failed again. Check logs.\n";
}
