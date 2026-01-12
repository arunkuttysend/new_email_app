<?php

namespace App\Console\Commands;

use App\Models\BounceLog;
use App\Models\Subscriber;
use App\Models\DeliveryServer;
use App\Services\Mail\ImapService;
use Illuminate\Console\Command;

class CollectBouncesCommand extends Command
{
    protected $signature = 'bounces:collect {--server= : Specific server ID} {--test : Test mode}';
    protected $description = 'Collect bounce emails from all IMAP servers';

    public function handle()
    {
        $this->info('🔍 Starting bounce collection from IMAP servers...');
        
        // Get all delivery servers with IMAP configured
        $servers = $this->option('server') 
            ? DeliveryServer::where('id', $this->option('server'))->get()
            : DeliveryServer::whereNotNull('credentials->imap_host')->get();
        
        if ($servers->isEmpty()) {
            $this->warn('⚠️  No IMAP servers configured. Add IMAP credentials to your delivery servers.');
            $this->info('💡 Or use webhooks for real-time bounce collection.');
            return Command::SUCCESS;
        }
        
        $this->info("📬 Found {$servers->count()} IMAP server(s) to scan");
        
        $totalProcessed = 0;
        $testMode = $this->option('test');
        
        foreach ($servers as $server) {
            $this->newLine();
            $this->line("📡 Scanning: <fg=cyan>{$server->name}</>");
            
            try {
                $processed = $this->collectFromServer($server, $testMode);
                $totalProcessed += $processed;
                
                if ($processed > 0) {
                    $this->info("  ✅ Found {$processed} bounce(s)");
                } else {
                    $this->line("  ✓ No new bounces");
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("🎉 Total bounces processed: <fg=green>{$totalProcessed}</>");
        
        if ($testMode) {
            $this->warn('⚠️  TEST MODE - No subscribers were updated');
        }
        
        return Command::SUCCESS;
    }
    
    private function collectFromServer(DeliveryServer $server, bool $testMode): int
    {
        $credentials = $server->credentials;
        
        // Check if IMAP is configured
        if (empty($credentials['imap_host']) || empty($credentials['imap_username'])) {
            $this->warn("  ⚠️  IMAP not configured for this server");
            return 0;
        }
        
        // Fetch bounce emails using IMAP
        $bounceEmails = $this->fetchBounceEmails($credentials);
        
        if (empty($bounceEmails)) {
            return 0;
        }
        
        $processed = 0;
        
        foreach ($bounceEmails as $email) {
            $bounceData = $this->parseBounceEmail($email);
            
            if ($bounceData) {
                // Create bounce log
                BounceLog::create([
                    'subscriber_id' => $bounceData['subscriber_id'],
                    'campaign_id' => $bounceData['campaign_id'],
                    'email' => $bounceData['email'],
                    'bounce_type' => $bounceData['type'],
                    'bounce_reason' => $bounceData['reason'],
                    'diagnostic_code' => $bounceData['diagnostic_code'],
                    'smtp_code' => $bounceData['smtp_code'],
                    'raw_message' => $bounceData['raw_message'] ?? null,
                    'bounced_at' => now(),
                ]);
                
                // Update subscriber status
                if (!$testMode && $bounceData['subscriber_id'] && $bounceData['type'] === 'hard') {
                    $subscriber = Subscriber::find($bounceData['subscriber_id']);
                    if ($subscriber) {
                        $subscriber->update(['status' => 'bounced']);
                        $this->line("    → Marked {$bounceData['email']} as bounced");
                    }
                }
                
                $processed++;
            }
        }
        
        return $processed;
    }
    
    private function fetchBounceEmails(array $credentials): array
    {
        /**
         * To implement full IMAP support, install: composer require webklex/php-imap
         * 
         * Example implementation:
         * 
         * $client = new \Webklex\PHPIMAP\ClientManager();
         * $client->connect([
         *     'host' => $credentials['imap_host'],
         *     'port' => $credentials['imap_port'] ?? 993,
         *     'encryption' => $credentials['imap_encryption'] ?? 'ssl',
         *     'username' => $credentials['imap_username'],
         *     'password' => $credentials['imap_password'],
         * ]);
         * 
         * $folder = $client->getFolder($credentials['imap_folder'] ?? 'INBOX');
         * $messages = $folder->messages()->unseen()->get();
         * 
         * return $messages->map(fn($msg) => [
         *     'subject' => $msg->getSubject(),
         *     'body' => $msg->getTextBody(),
         *     'from' => $msg->getFrom(),
         *     'headers' => $msg->getHeaders(),
         * ])->toArray();
         */
        
        // Placeholder - returns empty for now
        // Install webklex/php-imap for full functionality
        return [];
    }
    
    private function parseBounceEmail(array $email): ?array
    {
        $subject = $email['subject'] ?? '';
        $body = $email['body'] ?? '';
        $headers = $email['headers'] ?? '';
        
        // Extract bounced email address
        preg_match('/[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}/', $body, $matches);
        $bouncedEmail = $matches[0] ?? null;
        
        if (!$bouncedEmail) {
            return null;
        }
        
        // Determine bounce type
        $bounceType = 'hard'; // Default
        $lowerBody = strtolower($body);
        
        if (str_contains($lowerBody, 'mailbox full') || str_contains($lowerBody, 'quota exceeded')) {
            $bounceType = 'soft';
        } elseif (str_contains($lowerBody, 'blocked') || str_contains($lowerBody, 'spam')) {
            $bounceType = 'block';
        }
        
        // Find subscriber
        $subscriber = Subscriber::where('email', $bouncedEmail)->first();
        
        // Extract SMTP code
        preg_match('/(\d{3})\s/', $body, $codeMatch);
        $smtpCode = $codeMatch[1] ?? null;
        
        // Try to extract campaign info from headers
        $campaignId = null;
        if (preg_match('/X-Campaign-ID:\s*([a-f0-9\-]+)/i', $headers, $campMatch)) {
            $campaignId = $campMatch[1];
        }
        
        return [
            'subscriber_id' => $subscriber?->id,
            'campaign_id' => $campaignId,
            'email' => $bouncedEmail,
            'type' => $bounceType,
            'reason' => substr($body, 0, 500),
            'diagnostic_code' => null,
            'smtp_code' => $smtpCode,
            'raw_message' => substr($body, 0, 2000),
        ];
    }
}
