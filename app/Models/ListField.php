<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ListField extends Model
{
    use HasUuids;

    protected $fillable = [
        'mailing_list_id',
        'label',
        'tag',
        'type',
        'options',
        'default_value',
        'help_text',
        'required',
        'visibility',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
        ];
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class);
    }

    public function values()
    {
        return $this->hasMany(SubscriberFieldValue::class);
    }
}
