<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryServer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'credentials',
        'from_email',
        'from_name',
        'reply_to',
        'settings',
        'quotas',
        'current_usage',
        'warmup_plan_id',
        'status',
    ];

    protected $hidden = [
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'settings' => 'array',
            'quotas' => 'array',
            'current_usage' => 'array',
        ];
    }

    // ====================
    // SERVER TYPES
    // ====================

    const TYPE_SMTP = 'smtp';
    const TYPE_SENDGRID = 'sendgrid';
    const TYPE_MAILGUN = 'mailgun';
    const TYPE_SES = 'ses';
    const TYPE_POSTAL = 'postal';
    const TYPE_POSTMARK = 'postmark';

    public static function getTypes(): array
    {
        return [
            self::TYPE_SMTP => 'SMTP',
            self::TYPE_SENDGRID => 'SendGrid',
            self::TYPE_MAILGUN => 'Mailgun',
            self::TYPE_SES => 'Amazon SES',
            self::TYPE_POSTAL => 'Postal',
            self::TYPE_POSTMARK => 'Postmark',
        ];
    }

    // ====================
    // SCOPES
    // ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ====================
    // HELPERS
    // ====================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getHourlyQuota(): int
    {
        return $this->quotas['hourly'] ?? 0;
    }

    public function getDailyQuota(): int
    {
        return $this->quotas['daily'] ?? 0;
    }

    public function getHourlyUsage(): int
    {
        return $this->current_usage['hourly'] ?? 0;
    }

    public function getDailyUsage(): int
    {
        return $this->current_usage['daily'] ?? 0;
    }

    public function canSend(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $hourlyQuota = $this->getHourlyQuota();
        $dailyQuota = $this->getDailyQuota();

        if ($hourlyQuota > 0 && $this->getHourlyUsage() >= $hourlyQuota) {
            return false;
        }

        if ($dailyQuota > 0 && $this->getDailyUsage() >= $dailyQuota) {
            return false;
        }

        return true;
    }

    public function incrementUsage(): void
    {
        $usage = $this->current_usage ?? [];
        $usage['hourly'] = ($usage['hourly'] ?? 0) + 1;
        $usage['daily'] = ($usage['daily'] ?? 0) + 1;
        $usage['monthly'] = ($usage['monthly'] ?? 0) + 1;
        $usage['last_used_at'] = now()->toISOString();

        $this->update(['current_usage' => $usage]);
    }
}
