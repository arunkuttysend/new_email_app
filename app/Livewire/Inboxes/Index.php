<?php

namespace App\Livewire\Inboxes;

use App\Models\DeliveryServer;
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
        $server = DeliveryServer::find($id);
        if ($server) {
            $server->delete();
            session()->flash('success', 'Inbox deleted successfully.');
        }
    }

    public function render()
    {
        $inboxes = DeliveryServer::query()
            ->where('type', 'smtp') // Inboxes are primarily SMTP type for now
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('from_email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.inboxes.index', [
            'inboxes' => $inboxes,
        ]);
    }
}
