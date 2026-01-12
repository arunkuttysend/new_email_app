<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SequenceSubscriberProgress extends Model
{
    use HasUuids;

    protected $table = 'sequence_subscriber_progress';

    protected $fillable = [
        'sequence_id',
        'subscriber_id',
        'current_step_id',
        'current_step_order',
        'next_send_at',
        'status',
        'stop_reason',
    ];

    protected function casts(): array
    {
        return [
            'next_send_at' => 'datetime',
            'current_step_order' => 'integer',
        ];
    }

    public function sequence()
    {
        return $this->belongsTo(EmailSequence::class, 'sequence_id');
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(SequenceStep::class, 'current_step_id');
    }
}
