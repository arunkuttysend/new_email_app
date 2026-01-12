<?php

namespace App\Livewire\Subscribers;

use App\Models\MailingList;
use App\Models\Subscriber;
use Livewire\Component;

class Create extends Component
{
    public $email;
    public $mailing_list_id;
    public $status = 'confirmed';

    public $fields = [];
    public $fieldValues = [];

    protected function rules()
    {
        $rules = [
            'email' => 'required|email|max:255',
            'mailing_list_id' => 'required|exists:mailing_lists,id',
            'status' => 'required|in:confirmed,unconfirmed,unsubscribed',
        ];

        foreach ($this->fields as $field) {
            if ($field->required) {
                $rules['fieldValues.' . $field->tag] = 'required';
            }
        }

        return $rules;
    }

    public function updatedMailingListId($value)
    {
        if ($value) {
            $list = MailingList::find($value);
            if ($list) {
                $this->fields = $list->fields;
            } else {
                $this->fields = [];
            }
        } else {
            $this->fields = [];
        }
        $this->fieldValues = []; // Reset values when list changes
    }

    public function store()
    {
        $this->validate();

        // Check if subscriber already exists in this list
        $exists = Subscriber::where('mailing_list_id', $this->mailing_list_id)
            ->where('email', $this->email)
            ->exists();

        if ($exists) {
            $this->addError('email', 'This email is already subscribed to the selected list.');
            return;
        }

        $subscriber = Subscriber::create([
            'mailing_list_id' => $this->mailing_list_id,
            'email' => $this->email,
            'status' => $this->status,
            'source' => 'manual',
            'subscribed_at' => ($this->status === 'confirmed') ? now() : null,
        ]);

        foreach ($this->fields as $field) {
            if (isset($this->fieldValues[$field->tag]) && $this->fieldValues[$field->tag] !== '') {
                $subscriber->fieldValues()->create([
                    'list_field_id' => $field->id,
                    'value' => $this->fieldValues[$field->tag],
                ]);
            }
        }

        session()->flash('success', 'Subscriber added successfully.');

        return redirect()->route('subscribers.index');
    }

    public function render()
    {
        $mailingLists = MailingList::pluck('name', 'id');

        return view('livewire.subscribers.create', [
            'mailingLists' => $mailingLists,
        ]);
    }
}
