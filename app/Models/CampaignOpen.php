<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignOpen extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'ip_address',
        'user_agent',
        'geo_data',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'geo_data' => 'array',
            'opened_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }
}
