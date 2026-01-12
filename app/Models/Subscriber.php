<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasUuids;

    protected $fillable = [
        'mailing_list_id',
        'email',
        'ip_address',
        'source',
        'status',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    // ====================
    // RELATIONSHIPS
    // ====================

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(SubscriberFieldValue::class);
    }

    public function campaignOpens()
    {
        return $this->hasMany(CampaignOpen::class);
    }

    public function campaignClicks()
    {
        return $this->hasMany(CampaignClick::class);
    }

    public function campaignReplies()
    {
        return $this->hasMany(CampaignReply::class);
    }

    public function sequenceProgress()
    {
        return $this->hasMany(SequenceSubscriberProgress::class);
    }

    // ====================
    // SCOPES
    // ====================

    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['subscribed', 'unconfirmed']);
    }

    // ====================
    // HELPERS
    // ====================

    public function isSubscribed(): bool
    {
        return $this->status === 'subscribed';
    }

    public function getFieldValue(string $tag): ?string
    {
        $value = $this->fieldValues()
            ->whereHas('listField', fn($q) => $q->where('tag', $tag))
            ->first();

        return $value?->value;
    }

    public function subscribe(): void
    {
        $this->update([
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);
        $this->mailingList->incrementSubscribersCount();
    }

    public function unsubscribe(): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
        $this->mailingList->decrementSubscribersCount();
    }
}
