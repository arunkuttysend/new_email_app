<?php

use App\Models\Subscriber;
use App\Models\MailingList;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\CampaignReply;
use Illuminate\Support\Facades\DB;

// Create dummy data
$user = \App\Models\User::first() ?? \App\Models\User::factory()->create();
$list = MailingList::first() ?? MailingList::create([
    'user_id' => $user->id,
    'name' => 'Test List',
    'display_name' => 'Test List',
]);

$subscriber = Subscriber::create([
    'mailing_list_id' => $list->id,
    'email' => 'delete_test_' . time() . '@example.com',
    'status' => 'subscribed',
]);

echo "Created Subscriber: {$subscriber->id}\n";

// Add related data (Campaign Send)
$campaign = Campaign::first() ?? Campaign::create([
    'user_id' => $user->id,
    'mailing_list_id' => $list->id, // Use same list
    'name' => 'Test Campaign',
    'subject' => 'Subject',
    'from_name' => 'Me',
    'from_email' => 'me@example.com',
]);

CampaignSend::create([
    'campaign_id' => $campaign->id,
    'subscriber_id' => $subscriber->id,
    'status' => 'sent',
]);
echo "Created CampaignSend\n";

// Try to delete
try {
    echo "Attempting delete...\n";
    $subscriber->delete();
    echo "Delete SUCCESS!\n";
} catch (\Exception $e) {
    echo "Delete FAILED: " . $e->getMessage() . "\n";
}
