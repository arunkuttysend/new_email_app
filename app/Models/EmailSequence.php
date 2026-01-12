<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmailSequence extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'name',
        'enable_threading',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enable_threading' => 'boolean',
        ];
    }

    // ====================
    // RELATIONSHIPS
    // ====================

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function steps()
    {
        return $this->hasMany(SequenceStep::class, 'sequence_id')->orderBy('step_order');
    }

    public function subscriberProgress()
    {
        return $this->hasMany(SequenceSubscriberProgress::class, 'sequence_id');
    }

    public function stepLogs()
    {
        return $this->hasMany(SequenceStepLog::class, 'sequence_id');
    }

    // ====================
    // SCOPES
    // ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ====================
    // HELPERS
    // ====================

    public function getNextStep(int $currentOrder): ?SequenceStep
    {
        return $this->steps()
            ->where('step_order', '>', $currentOrder)
            ->where('status', 'active')
            ->first();
    }

    public function getFirstStep(): ?SequenceStep
    {
        return $this->steps()->first();
    }
}
