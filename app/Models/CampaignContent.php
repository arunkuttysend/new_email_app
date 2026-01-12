<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignContent extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'html_content',
        'plain_text',
        'template_data',
        'template_type',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
