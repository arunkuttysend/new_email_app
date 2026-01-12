<?php

namespace App\Console\Commands;

use App\Models\DeliveryServer;
use App\Services\Mail\ImapService;
use App\Services\Mail\ReplyDetectionService;
use Illuminate\Console\Command;

class CheckRepliesCommand extends Command
{
    protected $signature = 'replies:check';
    protected $description = 'Check inboxes for replies and auto-stop sequences';

    public function handle()
    {
        $this->info('Checking for replies...');
        
        // Get all active inboxes with IMAP credentials
        $inboxes = DeliveryServer::active()->get();
        
        if ($inboxes->isEmpty()) {
            $this->warn('No active inboxes found.');
            return Command::SUCCESS;
        }
        
        $totalReplies = 0;
        $totalSequencesStopped = 0;
        
        foreach ($inboxes as $inbox) {
            $this->line("\nChecking: {$inbox->name} ({$inbox->from_email})");
            
            // Check if inbox has IMAP credentials
            if (!isset($inbox->credentials['imap']) && !isset($inbox->credentials['host'])) {
                $this->warn('  No IMAP credentials configured, skipping.');
                continue;
            }
            
            $imapService = new ImapService();
            $replyService = new ReplyDetectionService();
            
            // Connect to inbox
            if (!$imapService->connect($inbox)) {
                $this->error('  Failed to connect to IMAP');
                continue;
            }
            
            // Fetch unread emails from last 24 hours
            $emails = $imapService->getUnreadEmails(hours: 24, limit: 100);
            $this->line("  Found " . count($emails) . " unread email(s)");
            
            $repliesFound = 0;
            
            foreach ($emails as $email) {
                // Process email and detect if it's a reply
                $result = $replyService->processEmail($email, $inbox->id);
                
                if ($result) {
                    $repliesFound++;
                    $totalReplies++;
                    
                    $this->info("  ✓ Reply detected from: {$result['subscriber']->email}");
                    
                    // Mark as read
                    $imapService->markAsRead($email);
                } else {
                    // Not a reply to our campaign, skip
                    // Optionally mark as read or leave unread
                }
            }
            
            $this->info("  Processed {$repliesFound} reply/replies");
            
            // Disconnect
            $imapService->disconnect();
        }
        
        $this->line('');
        $this->info("Summary: {$totalReplies} total replies detected");
        
        return Command::SUCCESS;
    }
}
