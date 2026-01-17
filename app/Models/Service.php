<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'agent_id',
        'name',
        'pid',
        'status',
        'cpu_percent',
        'memory_percent',
        'memory_mb',
        'disk_read_mb',
        'disk_write_mb',
        'user',
        'command',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'pid' => 'integer',
        'cpu_percent' => 'decimal:2',
        'memory_percent' => 'decimal:2',
        'memory_mb' => 'integer',
        'disk_read_mb' => 'decimal:2',
        'disk_write_mb' => 'decimal:2',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('recorded_at', 'desc');
    }
}
