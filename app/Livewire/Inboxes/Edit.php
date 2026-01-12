<?php

namespace App\Livewire\Inboxes;

use App\Models\DeliveryServer;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public DeliveryServer $inbox;

    // General
    public $name;
    public $from_name;
    public $from_email;
    public $reply_to;
    public $daily_quota; // 0 = unlimited
    public $hourly_quota; // 0 = unlimited

    // SMTP
    public $smtp_host;
    public $smtp_port;
    public $smtp_username;
    public $smtp_password;
    public $smtp_encryption;

    // IMAP
    public $imap_host;
    public $imap_port;
    public $imap_username;
    public $imap_password;
    public $imap_encryption;

    public function mount(DeliveryServer $inbox)
    {
        $this->inbox = $inbox;

        $this->name = $inbox->name;
        $this->from_name = $inbox->from_name;
        $this->from_email = $inbox->from_email;
        $this->reply_to = $inbox->reply_to;
        
        $quotas = $inbox->quotas ?? [];
        $this->daily_quota = $quotas['daily'] ?? 50;
        $this->hourly_quota = $quotas['hourly'] ?? 5;

        // Decrypt credentials
        // Assuming credentials attribute has an accessor/mutator or cast to array
        // In this app, we are using manual credential array handling for now or simple json
        // But since we cast 'credentials' => 'encrypted:array', getting $inbox->credentials should return array
        
        $creds = $inbox->credentials ?? [];
        
        $this->smtp_host = $creds['host'] ?? '';
        $this->smtp_port = $creds['port'] ?? 587;
        $this->smtp_username = $creds['username'] ?? '';
        $this->smtp_password = $creds['password'] ?? '';
        $this->smtp_encryption = $creds['encryption'] ?? 'tls';

        $imap = $creds['imap'] ?? [];
        $this->imap_host = $imap['host'] ?? '';
        $this->imap_port = $imap['port'] ?? 993;
        $this->imap_username = $imap['username'] ?? '';
        $this->imap_password = $imap['password'] ?? '';
        $this->imap_encryption = $imap['encryption'] ?? 'ssl';
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'from_name' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'daily_quota' => 'required|integer|min:0',
            'hourly_quota' => 'required|integer|min:0',
            
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string|max:255', // Nullable on edit if they don't want to change it? 
            // Actually, for simplicity, let's keep it populated if possible, triggering change only if input.
            // But password inputs are usually empty.
            // Let's assume if empty, we keep old password?
            'smtp_encryption' => 'required|in:tls,ssl,none',

            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string|max:255',
            'imap_password' => 'nullable|string|max:255',
            'imap_encryption' => 'required|in:ssl,tls,none',
        ];
    }

    public function save()
    {
        $this->validate();

        $creds = $this->inbox->credentials;

        // Update basic info
        $this->inbox->name = $this->name;
        $this->inbox->from_name = $this->from_name;
        $this->inbox->from_email = $this->from_email;
        $this->inbox->reply_to = $this->reply_to;
        $this->inbox->quotas = [
            'daily' => (int) $this->daily_quota,
            'hourly' => (int) $this->hourly_quota,
        ];

        // Prepare credentials update
        // If password fields are filled, update them. logic:
        $currentSmtpPass = $creds['password'] ?? '';
        $newSmtpPass = !empty($this->smtp_password) ? $this->smtp_password : $currentSmtpPass;

        $currentImapPass = $creds['imap']['password'] ?? '';
        $newImapPass = !empty($this->imap_password) ? $this->imap_password : $currentImapPass;

        $newCreds = [
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'username' => $this->smtp_username,
            'password' => $newSmtpPass,
            'encryption' => $this->smtp_encryption,
            'imap' => [
                'host' => $this->imap_host,
                'port' => $this->imap_port,
                'username' => $this->imap_username,
                'password' => $newImapPass,
                'encryption' => $this->imap_encryption,
            ]
        ];

        $this->inbox->credentials = $newCreds;
        $this->inbox->save();

        session()->flash('success', 'Inbox updated successfully.');
        return redirect()->route('inboxes.index');
    }

    public function render()
    {
        return view('livewire.inboxes.edit');
    }
}
