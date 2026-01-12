<?php

namespace App\Livewire\Inbox;

use Livewire\Component;

class ThreadView extends Component
{
    public $subscriberId;
    public $messages = [];
    public $subscriber;

    public function mount($subscriberId)
    {
        $this->subscriberId = $subscriberId;
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $this->subscriber = \App\Models\Subscriber::findOrFail($this->subscriberId);

        // Get all interactions (Sends and Replies)
        $sends = \App\Models\CampaignSend::where('subscriber_id', $this->subscriberId)
            ->whereNotNull('sent_at')
            ->get()
            ->map(function ($send) {
                return [
                    'id' => $send->id,
                    'type' => 'sent',
                    'subject' => $send->campaign->name . ': ' . ($send->campaign->subject ?? 'No subject'),
                    'body' => null, // Content not stored in DB
                    'created_at' => $send->sent_at,
                    'is_reply' => false,
                ];
            });

        $replies = \App\Models\CampaignReply::where('subscriber_id', $this->subscriberId)
            ->get()
            ->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'type' => 'received',
                    'subject' => $reply->subject,
                    'body' => $reply->body_html ?? nl2br($reply->body_text),
                    'created_at' => $reply->received_at,
                    'is_reply' => true,
                    'status' => $reply->status,
                ];
            });

        // Merge and sort
        $this->messages = $sends->concat($replies)->sortBy('created_at')->values()->all();

        // Mark unread as read
        \App\Models\CampaignReply::where('subscriber_id', $this->subscriberId)
            ->where('status', 'unread')
            ->update(['status' => 'read']);
            
        // Emit event to update count
        $this->dispatch('threadUpdated'); 
    }

    public $message = '';

    public function sendReply()
    {
        $this->validate([
            'message' => 'required|string|min:1',
        ]);

        // 1. Get Context
        $lastSend = \App\Models\CampaignSend::where('subscriber_id', $this->subscriberId)
            ->latest('sent_at')
            ->first();
            
        if (!$lastSend) {
            // Should not happen in a thread, but handle safe
            return;
        }

        $inbox = $lastSend->deliveryServer;
        
        // 2. Prepare Data
        $emailData = [
            'subject' => 'Re: ' . ($lastSend->campaign->subject ?? 'Reply'),
            'html_content' => nl2br($this->message),
            'from_email' => $inbox->from_email,
            'from_name' => $inbox->from_name,
            'reply_to' => $inbox->reply_to,
            'thread_id' => $lastSend->message_id // For email client threading
        ];
        
        // 3. Send
        $mailer = app(\App\Services\Mail\MailerService::class);
        $result = $mailer->send($inbox, $this->subscriber, $emailData);
        
        // 4. Log to DB as a "Send"
        if ($result['success']) {
            \App\Models\CampaignSend::create([
                'campaign_id' => $lastSend->campaign_id,
                'subscriber_id' => $this->subscriberId,
                'delivery_server_id' => $inbox->id,
                'message_id' => $result['message_id'],
                // 'email_content' => $this->message, // We don't have this column yet, skipping storage
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            
            $this->message = ''; // Reset input
            $this->loadMessages(); // Refresh UI
        }
    }

    public function setSentiment($value)
    {
        // Update the most recent reply, or all replies in thread?
        // Usually you tag the "Prospect", but here we tag the "Reply".
        // Let's tag all replies from this subscriber in this thread to be safe/consistent
        \App\Models\CampaignReply::where('subscriber_id', $this->subscriberId)
            ->update(['sentiment' => $value]);
            
        $this->loadMessages();
    }

    public function toggleLead()
    {
        // Toggle 'is_lead' for the subscriber's latest reply or all
        // Let's toggle for all to keep state consistent
        $currentStatus = \App\Models\CampaignReply::where('subscriber_id', $this->subscriberId)
            ->latest('received_at')
            ->first()
            ->is_lead ?? false;

        \App\Models\CampaignReply::where('subscriber_id', $this->subscriberId)
            ->update(['is_lead' => !$currentStatus]);

        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.inbox.thread-view');
    }
}
