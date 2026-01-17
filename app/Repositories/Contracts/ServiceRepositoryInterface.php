<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Service repository interface
 * 
 * Defines data access methods for Service model
 * 
 * @package App\Repositories\Contracts
 */
interface ServiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all services for an agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @return Collection
     */
    public function getByAgent(string $agentId): Collection;

    /**
     * Get top services by resource usage
     * 
     * @param string $agentId Agent ID (UUID)
     * @param int $limit Number of services
     * @return Collection
     */
    public function getTopByResource(string $agentId, int $limit = 10): Collection;

    /**
     * Bulk insert/update services for an agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @param array $services Array of service data
     * @return bool
     */
    public function bulkUpsert(string $agentId, array $services): bool;

    /**
     * Delete all services for an agent
     * 
     * @param string $agentId Agent ID (UUID)
     * @return bool
     */
    public function deleteByAgent(string $agentId): bool;
}
