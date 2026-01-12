<?php

namespace App\Services\Mail;

use App\Models\CampaignReply;
use App\Models\CampaignSend;
use App\Models\SequenceStepLog;
use App\Models\SequenceSubscriberProgress;
use App\Models\Subscriber;

class ReplyDetectionService
{
    /**
     * Process an email and detect if it's a reply to a campaign
     */
    public function processEmail($message, $inboxId): ?array
    {
        try {
            // Extract email details
            $from = $message->getFrom();
            if (empty($from)) {
                return null;
            }
            
            $fromEmail = $from[0]->mail ?? null;
            if (!$fromEmail) {
                return null;
            }
            
            // Find subscriber by email
            $subscriber = Subscriber::where('email', strtolower($fromEmail))->first();
            if (!$subscriber) {
                return null;
            }
            
            // Try to match reply to campaign
            $match = $this->findRelatedCampaignSend($message, $subscriber);
            if (!$match) {
                return null;
            }
            
            // Extract reply content
            $subject = $message->getSubject() ?? '';
            $bodyText = $message->getTextBody() ?? '';
            $bodyHtml = $message->getHTMLBody() ?? '';
            $messageId = $message->getMessageId() ?? '';
            $receivedAt = $message->getDate() ?? now();
            
            // Create reply record
            $reply = CampaignReply::create([
                'campaign_id' => $match['campaign_id'],
                'subscriber_id' => $subscriber->id,
                'campaign_send_id' => $match['campaign_send_id'],
                'message_id' => $messageId,
                'subject' => $subject,
                'from' => $fromEmail,
                'body_text' => $bodyText,
                'body_html' => $bodyHtml,
                'received_at' => $receivedAt,
            ]);
            
            // Stop any active sequences for this subscriber
            $this->stopSequencesForSubscriber($subscriber->id, $match['campaign_id']);
            
            return [
                'reply' => $reply,
                'subscriber' => $subscriber,
                'campaign_id' => $match['campaign_id'],
            ];
        } catch (\Exception $e) {
            \Log::error('Reply detection failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find the campaign send this email is replying to
     */
    private function findRelatedCampaignSend($message, Subscriber $subscriber): ?array
    {
        // Method 1: Check In-Reply-To header
        try {
            $inReplyTo = $message->getInReplyTo();
            if ($inReplyTo) {
                // Try to match against sequence step logs
                $stepLog = SequenceStepLog::where('message_id', 'LIKE', '%' . trim($inReplyTo, '<>') . '%')->first();
                if ($stepLog) {
                    return [
                        'campaign_id' => $stepLog->sequence->campaign_id ?? null,
                        'subscriber_id' => $subscriber->id,
                        'campaign_send_id' => $stepLog->campaign_send_id,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::debug('In-Reply-To header check failed: ' . $e->getMessage());
        }
        
        // Method 2: Check References header
        try {
            $references = $message->getReferences();
            if ($references) {
                foreach ($references as $reference) {
                    $stepLog = SequenceStepLog::where('message_id', 'LIKE', '%' . trim($reference, '<>') . '%')->first();
                    if ($stepLog) {
                        return [
                            'campaign_id' => $stepLog->sequence->campaign_id ?? null,
                            'subscriber_id' => $subscriber->id,
                            'campaign_send_id' => $stepLog->campaign_send_id,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug('References header check failed: ' . $e->getMessage());
        }
        
        // Method 3: Fallback - find recent campaign send
        $recentSend = CampaignSend::where('subscriber_id', $subscriber->id)
            ->where('status', 'sent')
            ->where('sent_at', '>=', now()->subDays(30))
            ->latest('sent_at')
            ->first();
        
        if ($recentSend) {
            return [
                'campaign_id' => $recentSend->campaign_id,
                'subscriber_id' => $subscriber->id,
                'campaign_send_id' => $recentSend->id,
            ];
        }
        
        return null;
    }
    
    /**
     * Stop all active sequences for a subscriber
     */
    private function stopSequencesForSubscriber(string $subscriberId, string $campaignId): void
    {
        SequenceSubscriberProgress::where('subscriber_id', $subscriberId)
            ->where('status', 'active')
            ->whereHas('sequence', function ($query) use ($campaignId) {
                $query->where('campaign_id', $campaignId);
            })
            ->update([
                'status' => 'stopped',
                'stop_reason' => 'replied',
            ]);
    }
}
