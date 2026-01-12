<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'hash',
        'url',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'clicks_count' => 'integer',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function clicks()
    {
        return $this->hasMany(CampaignClick::class);
    }
}
