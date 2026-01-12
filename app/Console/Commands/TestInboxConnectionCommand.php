<?php

namespace App\Console\Commands;

use App\Models\DeliveryServer;
use App\Services\Mail\MailerService;
use Illuminate\Console\Command;

class TestInboxConnectionCommand extends Command
{
    protected $signature = 'inbox:test {inbox?}';
    protected $description = 'Test SMTP connection for an inbox';

    public function handle()
    {
        $inboxId = $this->argument('inbox');
        
        if (!$inboxId) {
            // Show all inboxes and let user choose
            $inboxes = DeliveryServer::active()->get(['id', 'name', 'from_email']);
            
            if ($inboxes->isEmpty()) {
                $this->error('No active inboxes found.');
                return Command::FAILURE;
            }
            
            $this->table(['ID', 'Name', 'Email'], $inboxes->map(function ($inbox) {
                return [$inbox->id, $inbox->name, $inbox->from_email];
            }));
            
            $inboxId = $this->ask('Enter inbox ID to test');
        }
        
        $inbox = DeliveryServer::find($inboxId);
        
        if (!$inbox) {
            $this->error('Inbox not found.');
            return Command::FAILURE;
        }
        
        $this->info("Testing connection for: {$inbox->name} ({$inbox->from_email})");
        $this->line('');
        
        $mailerService = new MailerService();
        $result = $mailerService->testConnection($inbox);
        
        if ($result['success']) {
            $this->info('✓ Connection successful!');
            $this->line($result['message']);
            return Command::SUCCESS;
        } else {
            $this->error('✗ Connection failed!');
            $this->error($result['error']);
            return Command::FAILURE;
        }
    }
}
