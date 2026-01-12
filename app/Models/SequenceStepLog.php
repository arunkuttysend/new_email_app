<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SequenceStepLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'sequence_id',
        'step_id',
        'subscriber_id',
        'message_id',
        'thread_id',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function sequence()
    {
        return $this->belongsTo(EmailSequence::class, 'sequence_id');
    }

    public function step()
    {
        return $this->belongsTo(SequenceStep::class, 'step_id');
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }
}
