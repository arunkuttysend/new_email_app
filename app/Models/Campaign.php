<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'mailing_list_id',
        'segment_id',
        'name',
        'type',
        'from_name',
        'from_email',
        'reply_to',
        'subject',
        'preheader',
        'scheduled_at',
        'started_at',
        'finished_at',
        'options',
        'stats',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'stats' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    // ====================
    // STATUS CONSTANTS
    // ====================

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENDING = 'sending';
    const STATUS_PAUSED = 'paused';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    // ====================
    // RELATIONSHIPS
    // ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function content()
    {
        return $this->hasOne(CampaignContent::class);
    }

    public function sequence()
    {
        return $this->hasOne(EmailSequence::class);
    }

    public function sends()
    {
        return $this->hasMany(CampaignSend::class);
    }

    public function opens()
    {
        return $this->hasMany(CampaignOpen::class);
    }

    public function clicks()
    {
        return $this->hasMany(CampaignClick::class);
    }

    public function bounces()
    {
        return $this->hasMany(CampaignBounce::class);
    }

    public function links()
    {
        return $this->hasMany(CampaignLink::class);
    }

    public function replies()
    {
        return $this->hasMany(CampaignReply::class);
    }

    // ====================
    // SCOPES
    // ====================

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeSending($query)
    {
        return $query->where('status', self::STATUS_SENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    // ====================
    // HELPERS
    // ====================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    public function canSend(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PAUSED]);
    }

    public function getOpenRate(): float
    {
        $sent = $this->stats['sent'] ?? 0;
        $opens = $this->stats['unique_opens'] ?? 0;
        return $sent > 0 ? round(($opens / $sent) * 100, 2) : 0;
    }

    public function getClickRate(): float
    {
        $sent = $this->stats['sent'] ?? 0;
        $clicks = $this->stats['unique_clicks'] ?? 0;
        return $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0;
    }
    
    /**
     * Calculate and return campaign statistics
     */
    public function getStats(): array
    {
        // 1. Check for Sequence Logic
        if ($this->sequence) {
            $logs = $this->sequence->stepLogs;
            
            // Sends
            // For sequences, "sent" logs imply a successful send.
            $sent = $logs->where('status', 'sent')->count();
            $failed = $logs->where('status', 'failed')->count();
            // Total is tricky for sequences. Often "Total Enrolled" * "Steps".
            // For consistent dashboarding, let's use Sent + Failed + Active Subscribers (approx pending).
            $pending = $this->sequence->subscriberProgress()->where('status', 'active')->count();
            $total = $sent + $failed + $pending; 

            // Opens & Clicks
            $uniqueOpens = $logs->whereNotNull('opened_at')->unique('subscriber_id')->count();
            $uniqueClicks = $logs->whereNotNull('clicked_at')->unique('subscriber_id')->count();
            
            $totalOpens = $logs->whereNotNull('opened_at')->count();
            $totalClicks = $logs->whereNotNull('clicked_at')->count();
            
            // Calculate rates
            $openRate = $sent > 0 ? round(($uniqueOpens / $sent) * 100, 2) : 0;
            $clickRate = $sent > 0 ? round(($uniqueClicks / $sent) * 100, 2) : 0;
            $ctr = $uniqueOpens > 0 ? round(($uniqueClicks / $uniqueOpens) * 100, 2) : 0;

            return [
                'total' => $total,
                'sent' => $sent,
                'failed' => $failed,
                'pending' => $pending,
                'unique_opens' => $uniqueOpens,
                'unique_clicks' => $uniqueClicks,
                'total_opens' => $totalOpens,
                'total_clicks' => $totalClicks,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'ctr' => $ctr,
            ];
        }

        // 2. Default Campaign Logic (No Sequence)
        $sends = $this->sends()->count();
        $sent = $this->sends()->where('status', 'sent')->count();
        $failed = $this->sends()->where('status', 'failed')->count();
        $pending = $this->sends()->where('status', 'pending')->count();
        
        // Unique opens and clicks
        $uniqueOpens = $this->opens()->distinct('subscriber_id')->count('subscriber_id');
        $uniqueClicks = $this->clicks()->distinct('subscriber_id')->count('subscriber_id');
        
        // Total opens and clicks
        $totalOpens = $this->opens()->count();
        $totalClicks = $this->clicks()->count();
        
        // Calculate rates
        $openRate = $sent > 0 ? round(($uniqueOpens / $sent) * 100, 2) : 0;
        $clickRate = $sent > 0 ? round(($uniqueClicks / $sent) * 100, 2) : 0;
        $ctr = $uniqueOpens > 0 ? round(($uniqueClicks / $uniqueOpens) * 100, 2) : 0;
        
        return [
            'total' => $sends,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'unique_opens' => $uniqueOpens,
            'unique_clicks' => $uniqueClicks,
            'total_opens' => $totalOpens,
            'total_clicks' => $totalClicks,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'ctr' => $ctr,
        ];
    }
}
