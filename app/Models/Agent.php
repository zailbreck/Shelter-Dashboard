<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hwid',
        'agent_id',
        'hostname',
        'ip_address',
        'api_token',
        'os_type',
        'os_version',
        'cpu_cores',
        'total_memory',
        'total_disk',
        'status',
        'last_seen_at',
        'registered_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'registered_at' => 'datetime',
        'total_memory' => 'integer',
        'total_disk' => 'integer',
        'cpu_cores' => 'integer',
    ];

    protected $hidden = [
        'api_token',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    public function metricSnapshots(): HasMany
    {
        return $this->hasMany(MetricSnapshot::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online' &&
            $this->last_seen_at?->diffInSeconds(now()) < 60;
    }

    public function isOfflineForDays(int $days = 5): bool
    {
        if (!$this->last_seen_at) {
            return true;
        }

        return $this->last_seen_at->diffInDays(now()) >= $days;
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online')
            ->where('last_seen_at', '>=', now()->subMinute());
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline')
            ->orWhere('last_seen_at', '<', now()->subMinute());
    }

    public function scopeOfflineForDays($query, int $days = 5)
    {
        return $query->where(function ($q) use ($days) {
            $q->where('last_seen_at', '<', now()->subDays($days))
                ->orWhereNull('last_seen_at');
        });
    }

    /**
     * Soft delete agent but keep metrics history
     */
    public function softDeleteAgent()
    {
        // Soft delete the agent
        $this->delete();

        // Note: metrics, snapshots, and services are kept
        // They have foreign key constraints but we don't cascade delete
        return true;
    }
}
