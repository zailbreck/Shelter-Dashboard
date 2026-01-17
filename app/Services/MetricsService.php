<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Repositories\Contracts\MetricsRepositoryInterface;

/**
 * Metrics service
 * 
 * Handles metrics storage and retrieval business logic
 * 
 * @package App\Services
 */
class MetricsService
{
    /**
     * @param MetricsRepositoryInterface $metricsRepo Metrics repository
     */
    public function __construct(
        private MetricsRepositoryInterface $metricsRepo
    ) {
    }

    /**
     * Store metrics for an agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @param array $metrics Metrics data
     * @return bool Success status
     */
    public function storeMetrics(string $agentId, array $metrics): bool
    {
        return $this->metricsRepo->storeMetrics($agentId, $metrics);
    }

    /**
     * Get realtime metrics snapshot
     * 
     * @param Agent $agent Agent model
     * @return array|null Latest metrics
     */
    public function getRealtimeMetrics(Agent $agent): ?array
    {
        return $this->metricsRepo->getLatestSnapshot($agent->id);
    }

    /**
     * Get historical metrics for charts
     * 
     * @param Agent $agent Agent model
     * @return array Metrics by type
     */
    public function getHistoricalMetrics(Agent $agent): array
    {
        return [
            'cpu' => $this->formatChartData(
                $this->metricsRepo->getHourlyMetrics($agent->id, 'cpu')
            ),
            'memory' => $this->formatChartData(
                $this->metricsRepo->getHourlyMetrics($agent->id, 'memory')
            ),
            'disk' => $this->formatChartData(
                $this->metricsRepo->getHourlyMetrics($agent->id, 'disk')
            ),
            'network' => $this->formatChartData(
                $this->metricsRepo->getHourlyMetrics($agent->id, 'network')
            ),
        ];
    }

    /**
     * Format metrics for chart display
     * 
     * @param \Illuminate\Database\Eloquent\Collection $metrics Metrics collection
     * @return array Formatted data
     */
    private function formatChartData($metrics): array
    {
        return $metrics->map(function ($metric) {
            return [
                'value' => $metric->value,
                'timestamp' => $metric->timestamp,
            ];
        })->toArray();
    }
}
