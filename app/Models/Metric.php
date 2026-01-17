<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    use HasUuid;
    protected $fillable = [
        'agent_id',
        'metric_type',
        'value',
        'unit',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($metric) {
            $metric->created_at = now();
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
