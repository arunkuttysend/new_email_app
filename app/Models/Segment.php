<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasUuids;

    protected $fillable = [
        'mailing_list_id',
        'name',
        'match_type',
        'conditions',
        'subscribers_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
        ];
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
