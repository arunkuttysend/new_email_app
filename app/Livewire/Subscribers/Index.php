<?php

namespace App\Livewire\Subscribers;

use App\Models\MailingList;
use App\Models\Subscriber;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $listFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'listFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Check for list_id query param manually if not automatically bound (Livewire 3 binds automatically usually, but let's be safe for initial load)
        if (request()->has('list_id')) {
            $this->listFilter = request('list_id');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingListFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        try {
            $subscriber = Subscriber::find($id);
            if ($subscriber) {
                // Determine if we should force delete or soft delete
                // Since model doesn't use SoftDeletes yet, this is a hard delete.
                $subscriber->delete();
                session()->flash('success', 'Subscriber deleted successfully.');
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Subscriber delete failed: ' . $e->getMessage());
            
            // Show user friendly error
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                session()->flash('error', 'Cannot delete subscriber due to associated data (replies/history).');
            } else {
                session()->flash('error', 'Error deleting subscriber: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $mailingLists = MailingList::pluck('name', 'id');

        $subscribers = Subscriber::query()
            ->with(['mailingList', 'fieldValues.listField'])
            ->when($this->search, function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->listFilter, function ($query) {
                $query->where('mailing_list_id', $this->listFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.subscribers.index', [
            'subscribers' => $subscribers,
            'mailingLists' => $mailingLists,
        ]);
    }
}
