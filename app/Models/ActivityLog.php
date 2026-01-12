<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ActivityLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    // ====================
    // RELATIONSHIPS
    // ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ====================
    // HELPERS
    // ====================

    /**
     * Log an activity
     */
    public static function log(string $action, string $model, $modelId = null, array $changes = []): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get formatted description
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user?->name ?? 'System';
        $modelName = class_basename($this->model);
        
        return "{$userName} {$this->action} {$modelName}";
    }
}
