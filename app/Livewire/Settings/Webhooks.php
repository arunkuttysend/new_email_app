<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Webhooks extends Component
{
    public $webhooks = [];
    
    public function mount()
    {
        $baseUrl = config('app.url');
        
        $this->webhooks = [
            [
                'name' => 'Postal Bounce Webhook',
                'url' => $baseUrl . '/api/webhooks/bounces/postal',
                'method' => 'POST',
                'description' => 'Configure this URL in your Postal server webhook settings',
                'provider' => 'Postal',
                'icon' => 'fa-mail-bulk',
            ],
            [
                'name' => 'Generic Bounce Webhook',
                'url' => $baseUrl . '/api/webhooks/bounces/generic',
                'method' => 'POST',
                'description' => 'Use for SendGrid, Mailgun, or custom bounce notifications',
                'provider' => 'Generic',
                'icon' => 'fa-plug',
            ],
        ];
    }
    
    public function copyToClipboard($url)
    {
        $this->dispatch('copy-to-clipboard', url: $url);
        session()->flash('success', 'Webhook URL copied to clipboard!');
    }
    
    public function render()
    {
        return view('livewire.settings.webhooks');
    }
}
