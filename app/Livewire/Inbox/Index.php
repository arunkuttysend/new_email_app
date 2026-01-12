<?php

namespace App\Livewire\Inbox;

use Livewire\Component;

class Index extends Component
{
    public $selectedSubscriberId = null;
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount($subscriber = null)
    {
        $this->selectedSubscriberId = $subscriber;
    }

    public function selectSubscriber($subscriberId)
    {
        $this->selectedSubscriberId = $subscriberId;
    }

    public function render()
    {
        return view('livewire.inbox.index');
    }
}
