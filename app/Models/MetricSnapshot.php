<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricSnapshot extends Model
{
    use HasUuid;
    protected $fillable = [
        'agent_id',
        'metric_type',
        'avg_value',
        'min_value',
        'max_value',
        'low_value',
        'high_value',
        'snapshot_period',
        'snapshot_time',
    ];

    protected $casts = [
        'snapshot_time' => 'datetime',
        'avg_value' => 'decimal:2',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'low_value' => 'decimal:2',
        'high_value' => 'decimal:2',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($snapshot) {
            $snapshot->created_at = now();
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
