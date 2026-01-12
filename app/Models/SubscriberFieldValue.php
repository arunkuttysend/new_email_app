<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriberFieldValue extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscriber_id',
        'list_field_id',
        'value',
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function listField()
    {
        return $this->belongsTo(ListField::class);
    }
}
