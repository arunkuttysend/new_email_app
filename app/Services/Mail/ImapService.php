<?php

namespace App\Services\Mail;

use App\Models\DeliveryServer;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

class ImapService
{
    protected $client = null;
    protected $folder = null;
    
    /**
     * Connect to inbox via IMAP
     */
    public function connect(DeliveryServer $inbox): bool
    {
        try {
            $credentials = $inbox->credentials;
            
            // Get IMAP credentials or fallback to SMTP
            $imapConfig = $credentials['imap'] ?? [
                'host' => $credentials['host'],
                'port' => 993,
                'encryption' => 'ssl',
                'validate_cert' => true,
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'protocol' => 'imap',
            ];
            
            // Use ClientManager to create client
            $cm = new ClientManager();
            $this->client = $cm->make([
                'host' => $imapConfig['host'],
                'port' => $imapConfig['port'],
                'encryption' => $imapConfig['encryption'],
                'validate_cert' => $imapConfig['validate_cert'] ?? true,
                'username' => $imapConfig['username'],
                'password' => $imapConfig['password'],
                'protocol' => $imapConfig['protocol'] ?? 'imap',
            ]);
            
            $this->client->connect();
            
            // Open INBOX folder
            $this->folder = $this->client->getFolder('INBOX');
            
            return true;
        } catch (\Exception $e) {
            \Log::error('IMAP connection failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get unread emails from the last 24 hours
     */
    public function getUnreadEmails(int $hours = 24, int $limit = 100): array
    {
        if (!$this->folder) {
            return [];
        }
        
        try {
            $since = now()->subHours($hours);
            
            $messages = $this->folder
                ->query()
                ->unseen()
                ->since($since)
                ->limit($limit)
                ->get();
            
            return $messages->toArray();
        } catch (\Exception $e) {
            \Log::error('IMAP fetch emails failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark email as read
     */
    public function markAsRead($message): bool
    {
        try {
            $message->setFlag('Seen');
            return true;
        } catch (\Exception $e) {
            \Log::error('IMAP mark as read failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disconnect from IMAP server
     */
    public function disconnect(): void
    {
        if ($this->client) {
            try {
                $this->client->disconnect();
            } catch (\Exception $e) {
                \Log::error('IMAP disconnect failed: ' . $e->getMessage());
            }
        }
    }
}
