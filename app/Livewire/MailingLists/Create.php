<?php

namespace App\Livewire\MailingLists;

use App\Models\MailingList;
use Livewire\Component;

class Create extends Component
{
    public $name;
    public $display_name;
    public $description;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'display_name' => 'required|min:3|max:255',
        'description' => 'nullable|max:1000',
    ];

    public function store()
    {
        $this->validate();

        MailingList::create([
            'user_id' => auth()->id(), // Assuming user is logged in
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Mailing list created successfully.');

        return redirect()->route('lists.index');
    }

    public function render()
    {
        return view('livewire.mailing-lists.create');
    }
}
