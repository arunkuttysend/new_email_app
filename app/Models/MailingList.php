<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailingList extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'mailing_lists';

    protected $fillable = [
        'user_id',
        'name',
        'display_name',
        'description',
        'visibility',
        'opt_in',
        'opt_out',
        'welcome_email',
        'require_approval',
        'defaults',
        'company_info',
        'notifications',
        'subscribers_count',
        'status',
    ];

    protected static function booted()
    {
        static::created(function ($mailingList) {
            // Create default fields
            $defaults = [
                [
                    'label' => 'First Name',
                    'tag' => 'first_name',
                    'type' => 'text',
                    'sort_order' => 1,
                ],
                [
                    'label' => 'Last Name',
                    'tag' => 'last_name',
                    'type' => 'text',
                    'sort_order' => 2,
                ],
                [
                    'label' => 'Company',
                    'tag' => 'company',
                    'type' => 'text',
                    'sort_order' => 3,
                ],
            ];

            foreach ($defaults as $field) {
                $mailingList->fields()->create($field);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'defaults' => 'array',
            'company_info' => 'array',
            'notifications' => 'array',
            'welcome_email' => 'boolean',
            'require_approval' => 'boolean',
        ];
    }

    // ====================
    // RELATIONSHIPS
    // ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(ListField::class, 'mailing_list_id')->orderBy('sort_order');
    }

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'mailing_list_id');
    }

    public function activeSubscribers()
    {
        return $this->subscribers()->where('status', 'subscribed');
    }

    public function segments()
    {
        return $this->hasMany(Segment::class, 'mailing_list_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'mailing_list_id');
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

    public function incrementSubscribersCount(): void
    {
        $this->increment('subscribers_count');
    }

    public function decrementSubscribersCount(): void
    {
        if ($this->subscribers_count > 0) {
            $this->decrement('subscribers_count');
        }
    }
}
