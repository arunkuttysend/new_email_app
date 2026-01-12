<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignClick extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'campaign_link_id',
        'ip_address',
        'user_agent',
        'geo_data',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'geo_data' => 'array',
            'clicked_at' => 'datetime',
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

    public function link()
    {
        return $this->belongsTo(CampaignLink::class, 'campaign_link_id');
    }
}
