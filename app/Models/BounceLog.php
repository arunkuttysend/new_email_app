<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BounceLog extends Model
{
    use HasUuids;

    const TYPE_HARD = 'hard';
    const TYPE_SOFT = 'soft';
    const TYPE_BLOCK = 'block';

    protected $fillable = [
        'subscriber_id',
        'campaign_id',
        'email',
        'bounce_type',
        'bounce_reason',
        'diagnostic_code',
        'smtp_code',
        'raw_message',
        'bounced_at',
    ];

    protected function casts(): array
    {
        return [
            'bounced_at' => 'datetime',
        ];
    }

    // Relationships
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Helpers
    public function isHardBounce(): bool
    {
        return $this->bounce_type === self::TYPE_HARD;
    }

    public function isSoftBounce(): bool
    {
        return $this->bounce_type === self::TYPE_SOFT;
    }

    /**
     * Get bounce type badge color
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->bounce_type) {
            self::TYPE_HARD => 'danger',
            self::TYPE_SOFT => 'warning',
            self::TYPE_BLOCK => 'dark',
            default => 'secondary',
        };
    }
}
