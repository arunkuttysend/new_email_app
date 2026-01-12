<?php

namespace App\Services\Mail;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\DeliveryServer;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailerService
{
    /**
     * Send an email via the specified inbox/delivery server
     */
    public function send(
        DeliveryServer $inbox,
        Subscriber $subscriber,
        array $emailData,
        ?string $campaignId = null
    ): array {
        try {
            // Personalize content
            $personalizedSubject = $this->personalize($emailData['subject'], $subscriber);
            
            // Auto-format content if it looks like plain text (no block tags)
            $htmlContent = $emailData['html_content'];
            if (!preg_match('/<(p|div|br|table|ul|ol|h[1-6])/i', $htmlContent)) {
                // Convert plain text to proper HTML paragraphs
                $paragraphs = explode("\n\n", $htmlContent);
                $htmlContent = '';
                foreach ($paragraphs as $para) {
                    $para = trim($para);
                    if (!empty($para)) {
                        // Replace single line breaks with <br> within paragraphs
                        $para = str_replace("\n", '<br>', $para);
                        $htmlContent .= '<p style="margin: 0 0 16px 0; line-height: 1.5;">' . $para . '</p>';
                    }
                }
            }

            $personalizedHtml = $this->personalize($htmlContent, $subscriber);
            
            // Add unsubscribe link (legal requirement, skip for test emails)
            if ($subscriber->id) {
                $personalizedHtml = $this->addUnsubscribeLink($personalizedHtml, $subscriber->id);
            }
            
            // Add tracking pixel if campaign ID provided
            if ($campaignId && isset($emailData['campaign_send_id'])) {
                $personalizedHtml = $this->addTrackingPixel(
                    $personalizedHtml,
                    $emailData['campaign_send_id']
                );
            }
            
            // Rewrite links for click tracking
            if ($campaignId && isset($emailData['campaign_send_id'])) {
                $personalizedHtml = $this->rewriteLinksForTracking(
                    $personalizedHtml,
                    $campaignId,
                    $emailData['campaign_send_id']
                );
            }
            
            // Generate unique Message-ID
            $messageId = $this->generateMessageId($inbox->from_email);
            
            // Configure SMTP transport for this inbox
            $this->configureDynamicMailer($inbox);
            
            // Send email using Laravel Mail
            Mail::send([], [], function ($message) use (
                $emailData,
                $subscriber,
                $personalizedSubject,
                $personalizedHtml,
                $messageId
            ) {
                $message->to($subscriber->email)
                    ->from($emailData['from_email'], $emailData['from_name'])
                    ->subject($personalizedSubject)
                    ->html($personalizedHtml);
                
                // Add reply-to if specified
                if (!empty($emailData['reply_to'])) {
                    $message->replyTo($emailData['reply_to']);
                }
                
                // Add custom headers
                $message->getHeaders()->addTextHeader('X-Message-ID', $messageId);
                
                // Add threading headers for sequences
                if (!empty($emailData['thread_id'])) {
                    $message->getHeaders()->addTextHeader('In-Reply-To', $emailData['thread_id']);
                    $message->getHeaders()->addTextHeader('References', $emailData['thread_id']);
                }
            });
            
            return [
                'success' => true,
                'message_id' => $messageId,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Configure dynamic SMTP mailer for specific inbox
     */
    private function configureDynamicMailer(DeliveryServer $inbox): void
    {
        $credentials = $inbox->credentials;
        
        // Configure mail transport dynamically
        config([
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $credentials['host'],
            'mail.mailers.smtp.port' => $credentials['port'] ?? 587,
            'mail.mailers.smtp.encryption' => $credentials['encryption'] ?? 'tls',
            'mail.mailers.smtp.username' => $credentials['username'],
            'mail.mailers.smtp.password' => $credentials['password'],
        ]);
        
        // Set default mailer to smtp
        config(['mail.default' => 'smtp']);
    }
    
    /**
     * Generate unique Message-ID
     */
    private function generateMessageId(string $domain): string
    {
        $uniqueId = Str::uuid()->toString();
        $domainPart = explode('@', $domain)[1] ?? $domain;
        
        return "<{$uniqueId}@{$domainPart}>";
    }
    
    /**
     * Personalize content with merge tags
     */
    private function personalize(string $content, Subscriber $subscriber): string
    {
        // For test subscribers (no ID), use pre-set field values from the relation
        if (!$subscriber->id && $subscriber->relationLoaded('fieldValues')) {
            $fieldValues = $subscriber->fieldValues
                ->mapWithKeys(function($item) {
                    return [$item->listField->tag => $item->value];
                })
                ->toArray();
        } else {
            // Get subscriber field values from database
            $fieldValues = $subscriber->fieldValues()
                ->with('listField')
                ->get()
                ->pluck('value', 'listField.tag')
                ->toArray();
        }
        
        // Default merge tags
        $mergeData = [
            'email' => $subscriber->email,
            'first_name' => $fieldValues['first_name'] ?? '',
            'last_name' => $fieldValues['last_name'] ?? '',
            'company' => $fieldValues['company'] ?? '',
        ];
        
        // Add all custom fields
        $mergeData = array_merge($mergeData, $fieldValues);
        
        // Replace merge tags (Case insensitive, supports {tag} and {{tag}})
        foreach ($mergeData as $tag => $value) {
            // Support {{tag}} (specific first)
            $content = str_ireplace('{{' . $tag . '}}', $value, $content);
            // Support {{ tag }}
            $content = str_ireplace('{{ ' . $tag . ' }}', $value, $content);
            // Support {tag} (general last)
            $content = str_ireplace('{' . $tag . '}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Add tracking pixel to HTML content
     */
    private function addTrackingPixel(string $html, string $campaignSendId): string
    {
        $trackingUrl = route('track.open', ['campaignSend' => $campaignSendId]);
        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" />';
        
        // Try to insert before </body>, otherwise append
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $pixel . '</body>', $html);
        } else {
            $html .= $pixel;
        }
        
        return $html;
    }
    
    /**
     * Add unsubscribe link to HTML content
     */
    private function addUnsubscribeLink(string $html, ?string $subscriberId): string
    {
        // Skip unsubscribe link for test emails (no subscriber ID)
        if (!$subscriberId) {
            return $html;
        }
        
        $unsubscribeUrl = route('unsubscribe', ['subscriber' => $subscriberId]);
        
        $footer = '<div style="margin-top:30px;padding-top:20px;border-top:1px solid #e0e0e0;font-size:12px;color:#999;text-align:center;">'
            . '<p style="margin:0;">If you no longer wish to receive these emails, you may '
            . '<a href="' . $unsubscribeUrl . '" style="color:#999;text-decoration:underline;">unsubscribe</a>.</p>'
            . '</div>';
        
        // Try to insert before </body>, otherwise append
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $footer . '</body>', $html);
        } else {
            $html .= $footer;
        }
        
        return $html;
    }
    
    /**
     * Rewrite all links in HTML for click tracking
     */
    private function rewriteLinksForTracking(
        string $html,
        string $campaignId,
        string $campaignSendId
    ): string {
        // Match all href attributes
        preg_match_all('/href=["\']([^"\']+)["\']/i', $html, $matches);
        
        if (empty($matches[1])) {
            return $html;
        }
        
        $links = array_unique($matches[1]);
        
        foreach ($links as $url) {
            // Skip tracking pixel, mailto, and anchors
            if (
                strpos($url, 'track/open') !== false ||
                strpos($url, 'mailto:') === 0 ||
                strpos($url, '#') === 0
            ) {
                continue;
            }
            
            // Create or find campaign link
            $linkHash = md5($url);
            $campaignLink = CampaignLink::firstOrCreate(
                [
                    'campaign_id' => $campaignId,
                    'hash' => $linkHash,
                ],
                [
                    'url' => $url,
                ]
            );
            
            // Generate tracking URL
            $trackingUrl = route('track.click', [
                'linkHash' => $linkHash,
                'campaignSend' => $campaignSendId,
            ]);
            
            // Replace in HTML
            $html = str_replace(
                'href="' . $url . '"',
                'href="' . $trackingUrl . '"',
                $html
            );
            $html = str_replace(
                "href='" . $url . "'",
                "href='" . $trackingUrl . "'",
                $html
            );
        }
        
        return $html;
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection(DeliveryServer $inbox): array
    {
        try {
            $this->configureDynamicMailer($inbox);
            
            // Try to send a test email
            Mail::raw('Connection test', function ($message) use ($inbox) {
                $message->to($inbox->from_email)
                    ->from($inbox->from_email, $inbox->from_name ?: 'Test')
                    ->subject('SMTP Connection Test');
            });
            
            return [
                'success' => true,
                'message' => 'SMTP connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
