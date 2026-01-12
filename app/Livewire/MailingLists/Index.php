<?php

namespace App\Livewire\MailingLists;

use App\Models\MailingList;
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
        $list = MailingList::find($id);
        if ($list) {
            $list->delete();
            session()->flash('success', 'Mailing list deleted successfully.');
        }
    }

    public function render()
    {
        $lists = MailingList::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->withCount('subscribers')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mailing-lists.index', [
            'lists' => $lists
        ]);
    }
}
