<?php

namespace App\Livewire\MailingLists;

use App\Models\MailingList;
use Livewire\Component;

class Edit extends Component
{
    public MailingList $mailingList;

    public $name;
    public $display_name;
    public $description;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'display_name' => 'required|min:3|max:255',
        'description' => 'nullable|max:1000',
    ];

    public function mount(MailingList $mailingList)
    {
        $this->mailingList = $mailingList;
        $this->name = $mailingList->name;
        $this->display_name = $mailingList->display_name;
        $this->description = $mailingList->description;
    }

    public function update()
    {
        $this->validate();

        $this->mailingList->update([
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Mailing list updated successfully.');

        return redirect()->route('lists.index');
    }

    public function render()
    {
        return view('livewire.mailing-lists.edit');
    }
}
