<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Metric;
use App\Models\MetricSnapshot;
use App\Repositories\Contracts\MetricsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Metrics repository implementation
 * 
 * Handles database operations for Metric and MetricSnapshot models
 * 
 * @package App\Repositories
 */
class MetricsRepository extends BaseRepository implements MetricsRepositoryInterface
{
    /**
     * @param Metric $model Metric model instance
     */
    public function __construct(Metric $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function storeMetrics(string $agentId, array $metrics): bool
    {
        $metricsData = [];
        $now = now();

        foreach ($metrics as $metric) {
            $metricsData[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'agent_id' => $agentId,
                'metric_type' => $metric['metric_type'],
                'value' => $metric['value'],
                'unit' => $metric['unit'] ?? '%',
                'recorded_at' => $now,
                'created_at' => $now,
            ];
        }

        return Metric::insert($metricsData);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecentMetrics(string $agentId, string $type, int $count = 60): Collection
    {
        return $this->model
            ->where('agent_id', $agentId)
            ->where('metric_type', $type)
            ->latest('recorded_at')
            ->limit($count)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestSnapshot(string $agentId): ?array
    {
        $snapshots = MetricSnapshot::where('agent_id', $agentId)
            ->latest('snapshot_time')
            ->get()
            ->groupBy('metric_type');

        if ($snapshots->isEmpty()) {
            return null;
        }

        $result = [];
        foreach (['cpu', 'memory', 'disk', 'network', 'io'] as $type) {
            if ($snapshots->has($type)) {
                $result[$type] = $snapshots[$type]->first()->avg_value;
            } else {
                $result[$type] = 0;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getHourlyMetrics(string $agentId, string $type): Collection
    {
        return $this->model
            ->where('agent_id', $agentId)
            ->where('metric_type', $type)
            ->where('recorded_at', '>=', now()->subHour())
            ->orderBy('recorded_at')
            ->get(['value', 'recorded_at as timestamp']);
    }
}
