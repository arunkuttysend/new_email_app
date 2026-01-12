<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $campaign = Campaign::find($id);
        if ($campaign) {
            $campaign->delete();
            session()->flash('success', 'Campaign deleted successfully.');
        }
    }

    public function render()
    {
        $campaigns = Campaign::query()
            ->with(['mailingList']) // Eager load relationships
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.campaigns.index', [
            'campaigns' => $campaigns,
        ]);
    }
}
