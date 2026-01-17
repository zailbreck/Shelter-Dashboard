<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Collection;

/**
 * Metrics repository interface
 * 
 * Defines data access methods for Metric and MetricSnapshot models
 * 
 * @package App\Repositories\Contracts
 */
interface MetricsRepositoryInterface extends RepositoryInterface
{
    /**
     * Store multiple metrics for an agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @param array $metrics Array of metrics data
     * @return bool
     */
    public function storeMetrics(string $agentId, array $metrics): bool;

    /**
     * Get recent metrics by type
     * 
     * @param string $agentId Agent ID (UUID)
     * @param string $type Metric type (cpu, memory, disk, etc.)
     * @param int $count Number of records
     * @return Collection
     */
    public function getRecentMetrics(string $agentId, string $type, int $count = 60): Collection;

    /**
     * Get latest snapshot for agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @return array|null
     */
    public function getLatestSnapshot(string $agentId): ?array;

    /**
     * Get hourly metrics for charts
     * 
     * @param string $agentId Agent ID (UUID)
     * @param string $type Metric type
     * @return Collection
     */
    public function getHourlyMetrics(string $agentId, string $type): Collection;
}
