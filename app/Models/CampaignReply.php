<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignReply extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'campaign_send_id',
        'message_id',
        'subject',
        'from',
        'body_text',
        'body_html',
        'received_at',
        'status',
        'sentiment',
        'is_lead',
    ];
    
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }
    
    // ====================
    // RELATIONSHIPS
    // ====================
    
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }
    
    public function campaignSend()
    {
        return $this->belongsTo(CampaignSend::class);
    }
}
