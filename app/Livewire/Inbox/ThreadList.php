<?php

namespace App\Livewire\Inbox;

use Livewire\Component;

class ThreadList extends Component
{
    public $selectedSubscriberId;
    public $filter = 'all'; // all, unread, leads, interested, not_interested

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $query = \App\Models\Subscriber::whereHas('campaignReplies', function ($q) {
            // Base filter: must have replied
            if ($this->filter === 'unread') {
                $q->where('status', 'unread');
            } elseif ($this->filter === 'leads') {
                $q->where('is_lead', true);
            } elseif (in_array($this->filter, ['interested', 'not_interested', 'ooo'])) {
                $q->where('sentiment', $this->filter);
            }
        });

        // Get subscribers who have replied
        $threads = $query
            ->withCount(['campaignReplies as unread_count' => function($query) {
                $query->where('status', 'unread');
            }])
            ->with(['campaignReplies' => function($query) {
                $query->latest('received_at')->limit(1);
            }])
            ->withMax('campaignReplies', 'received_at')
            ->orderByDesc('campaign_replies_max_received_at')
            ->paginate(20);

        return view('livewire.inbox.thread-list', [
            'threads' => $threads
        ]);
    }

    public function selectThread($subscriberId)
    {
        $this->dispatch('subscriberSelected', $subscriberId);
        // Also update parent for URL change
        $this->js("window.history.pushState({}, '', '/inbox/' + '{$subscriberId}')");
        // We can't easily call parent method, but we can redirect or use events
    }
}
