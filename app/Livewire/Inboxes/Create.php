<?php

namespace App\Livewire\Inboxes;

use App\Models\DeliveryServer;
use Livewire\Component;

class Create extends Component
{
    // General
    public $name;
    public $from_name;
    public $from_email;
    public $reply_to;
    
    // SMTP Credentials
    public $smtp_host;
    public $smtp_port = 587;
    public $smtp_username;
    public $smtp_password;
    public $smtp_encryption = 'tls';

    // IMAP Credentials
    public $imap_host;
    public $imap_port = 993;
    public $imap_username;
    public $imap_password;
    public $imap_encryption = 'ssl';

    // Quotas
    public $daily_quota = 50;
    public $hourly_quota = 20;

    protected $rules = [
        'name' => 'required|string|max:255',
        'from_name' => 'required|string|max:255',
        'from_email' => 'required|email|max:255',
        'reply_to' => 'nullable|email|max:255',
        
        'smtp_host' => 'required|string',
        'smtp_port' => 'required|numeric',
        'smtp_username' => 'required|string',
        'smtp_password' => 'required|string',
        'smtp_encryption' => 'required|in:tls,ssl,none',

        'imap_host' => 'required|string',
        'imap_port' => 'required|numeric',
        'imap_username' => 'required|string',
        'imap_password' => 'required|string',
        'imap_encryption' => 'required|in:ssl,tls,none',

        'daily_quota' => 'nullable|numeric|min:0',
    ];

    public function store()
    {
        $this->validate();

        $credentials = [
            'username' => $this->smtp_username,
            'password' => $this->smtp_password,
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'encryption' => $this->smtp_encryption,
            
            // Store IMAP in same credential blob
            'imap' => [
                'username' => $this->imap_username,
                'password' => $this->imap_password,
                'host' => $this->imap_host,
                'port' => $this->imap_port,
                'encryption' => $this->imap_encryption,
            ]
        ];

        DeliveryServer::create([
            'name' => $this->name,
            'type' => 'smtp', // All inboxes are SMTP type
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'reply_to' => $this->reply_to,
            'credentials' => $credentials,
            'quotas' => [
                'daily' => (int)$this->daily_quota,
                'hourly' => (int)$this->hourly_quota,
            ],
            'status' => 'active', // Active by default for now
        ]);

        session()->flash('success', 'Inbox added successfully.');
        return redirect()->route('inboxes.index');
    }

    public function render()
    {
        return view('livewire.inboxes.create');
    }
}
