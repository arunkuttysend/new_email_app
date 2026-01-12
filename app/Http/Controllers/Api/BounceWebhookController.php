<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BounceLog;
use App\Models\Subscriber;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BounceWebhookController extends Controller
{
    /**
     * Handle Postal webhook
     */
    public function postal(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Postal webhook received', ['payload' => $payload]);
            
            // Postal sends bounce data in this format
            $event = $payload['event'] ?? null;
            
            if ($event !== 'MessageBounced') {
                return response()->json(['status' => 'ignored', 'reason' => 'not a bounce event']);
            }
            
            $email = $payload['recipient'] ?? $payload['original_message']['to'] ?? null;
            $bounceType = $this->determineBounceType($payload);
            $reason = $payload['details'] ?? $payload['bounce']['message'] ?? 'Unknown';
            $smtpCode = $payload['status'] ?? null;
            
            if (!$email) {
                return response()->json(['status' => 'error', 'message' => 'No recipient email found'], 400);
            }
            
            $this->processBounce($email, $bounceType, $reason, $smtpCode, 'postal', $payload);
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Postal webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle generic bounce webhook (SendGrid, Mailgun, etc.)
     */
    public function generic(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Generic webhook received', ['payload' => $payload]);
            
            // Try to extract email from common formats
            $email = $payload['email'] 
                ?? $payload['recipient'] 
                ?? $payload['to']
                ?? null;
                
            $bounceType = $payload['bounce_type'] 
                ?? ($payload['reason'] === 'hard' ? 'hard' : 'soft');
                
            $reason = $payload['reason'] ?? $payload['message'] ?? 'Webhook bounce';
            $smtpCode = $payload['smtp_code'] ?? $payload['code'] ?? null;
            
            if (!$email) {
                return response()->json(['status' => 'error', 'message' => 'No email found'], 400);
            }
            
            $this->processBounce($email, $bounceType, $reason, $smtpCode, 'generic', $payload);
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Generic webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process bounce and update database
     */
    private function processBounce(
        string $email, 
        string $bounceType, 
        string $reason, 
        ?string $smtpCode,
        string $source,
        array $rawData
    ): void {
        // Find subscriber
        $subscriber = Subscriber::where('email', $email)->first();
        
        // Create bounce log
        BounceLog::create([
            'subscriber_id' => $subscriber?->id,
            'campaign_id' => null, // TODO: Extract from webhook if available
            'email' => $email,
            'bounce_type' => $bounceType,
            'bounce_reason' => $reason,
            'diagnostic_code' => $rawData['diagnostic_code'] ?? null,
            'smtp_code' => $smtpCode,
            'raw_message' => json_encode($rawData),
            'bounced_at' => now(),
        ]);
        
        // Update subscriber status for hard bounces
        if ($subscriber && $bounceType === 'hard') {
            $subscriber->update(['status' => 'bounced']);
            
            ActivityLog::log(
                'marked_bounced',
                'Subscriber',
                $subscriber->id,
                ['source' => $source, 'email' => $email]
            );
        }
        
        Log::info("Bounce processed via {$source}", [
            'email' => $email,
            'type' => $bounceType,
            'subscriber_updated' => $subscriber ? 'yes' : 'no'
        ]);
    }
    
    /**
     * Determine bounce type from Postal payload
     */
    private function determineBounceType(array $payload): string
    {
        $details = strtolower($payload['details'] ?? '');
        $status = $payload['status'] ?? '';
        
        // Hard bounce indicators
        if (
            str_contains($details, 'user unknown') ||
            str_contains($details, 'no such user') ||
            str_contains($details, 'invalid recipient') ||
            str_contains($details, 'does not exist') ||
            in_array($status, ['5.1.1', '5.1.2', '5.1.3'])
        ) {
            return 'hard';
        }
        
        // Block indicators
        if (
            str_contains($details, 'blocked') ||
            str_contains($details, 'spam') ||
            str_contains($details, 'blacklist')
        ) {
            return 'block';
        }
        
        // Otherwise soft bounce
        return 'soft';
    }
}
