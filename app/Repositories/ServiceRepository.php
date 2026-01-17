<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service repository implementation
 * 
 * Handles database operations for Service model
 * 
 * @package App\Repositories
 */
class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    /**
     * @param Service $model Service model instance
     */
    public function __construct(Service $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getByAgent(string $agentId): Collection
    {
        return $this->model
            ->where('agent_id', $agentId)
            ->orderBy('cpu_percent', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getTopByResource(string $agentId, int $limit = 10): Collection
    {
        return $this->model
            ->where('agent_id', $agentId)
            ->where('status', 'running')
            ->orderByRaw('(cpu_percent * 0.6) + (memory_percent * 0.4) DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkUpsert(string $agentId, array $services): bool
    {
        // Delete old services first
        $this->deleteByAgent($agentId);

        $servicesData = [];
        $now = now();

        foreach ($services as $service) {
            $servicesData[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'agent_id' => $agentId,
                'name' => $service['name'],
                'pid' => $service['pid'],
                'status' => 'running',
                'cpu_percent' => $service['cpu_percent'] ?? 0,
                'memory_percent' => $service['memory_percent'] ?? 0,
                'memory_mb' => $service['memory_mb'] ?? 0,
                'disk_read_mb' => $service['disk_read_mb'] ?? 0,
                'disk_write_mb' => $service['disk_write_mb'] ?? 0,
                'user' => $service['user'] ?? 'unknown',
                'command' => $service['command'] ?? null,
                'recorded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return Service::insert($servicesData);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByAgent(string $agentId): bool
    {
        return (bool) $this->model
            ->where('agent_id', $agentId)
            ->delete();
    }
}
