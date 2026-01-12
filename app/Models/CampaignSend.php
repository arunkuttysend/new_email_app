<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CampaignSend extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'delivery_server_id',
        'message_id',
        'status',
        'error',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    // Relationships
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function deliveryServer()
    {
        return $this->belongsTo(DeliveryServer::class);
    }
}
