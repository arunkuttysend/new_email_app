<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SequenceStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'sequence_id',
        'step_order',
        'name',
        'wait_value',
        'wait_unit',
        'condition_type',
        'condition_operator',
        'condition_link_id',
        'subject',
        'html_content',
        'plain_text',
        'template_data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
        ];
    }

    // ====================
    // CONDITION TYPES
    // ====================

    const CONDITION_OPENED = 'opened';
    const CONDITION_CLICKED = 'clicked';
    const CONDITION_REPLIED = 'replied';
    const CONDITION_BOUNCED = 'bounced';

    // ====================
    // RELATIONSHIPS
    // ====================

    public function sequence()
    {
        return $this->belongsTo(EmailSequence::class, 'sequence_id');
    }

    public function logs()
    {
        return $this->hasMany(SequenceStepLog::class, 'step_id');
    }

    // ====================
    // HELPERS
    // ====================

    public function getWaitSeconds(): int
    {
        return match ($this->wait_unit) {
            'minutes' => $this->wait_value * 60,
            'hours' => $this->wait_value * 3600,
            'days' => $this->wait_value * 86400,
            default => $this->wait_value * 86400,
        };
    }

    public function shouldSend(SequenceStepLog $previousLog): bool
    {
        if (!$this->condition_type) {
            return true;
        }

        $conditionMet = match ($this->condition_type) {
            self::CONDITION_OPENED => $previousLog->opened_at !== null,
            self::CONDITION_CLICKED => $previousLog->clicked_at !== null,
            self::CONDITION_REPLIED => $previousLog->replied_at !== null,
            self::CONDITION_BOUNCED => $previousLog->status === 'bounced',
            default => true,
        };

        return $this->condition_operator === 'not' ? !$conditionMet : $conditionMet;
    }
}
