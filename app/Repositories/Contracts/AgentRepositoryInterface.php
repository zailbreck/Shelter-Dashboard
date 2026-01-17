<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Collection;

/**
 * Agent repository interface
 * 
 * Defines data access methods for Agent model
 * 
 * @package App\Repositories\Contracts
 */
interface AgentRepositoryInterface extends RepositoryInterface
{
    /**
     * Find agent by API token
     * 
     * @param string $token API token
     * @return Agent|null
     */
    public function findByApiToken(string $token): ?Agent;

    /**
     * Get all agents with service count
     * 
     * @return Collection
     */
    public function withServiceCount(): Collection;

    /**
     * Get online agents
     * 
     * @return Collection
     */
    public function getOnlineAgents(): Collection;

    /**
     * Get agents offline for specified days
     * 
     * @param int $days Number of days
     * @return Collection
     */
    public function getOfflineAgents(int $days = 5): Collection;

    /**
     * Get agent with services relationship
     * 
     * @param string $id Agent ID (UUID)
     * @return Agent|null
     */
    public function withServices(string $id): ?Agent;

    /**
     * Update agent heartbeat
     * 
     * @param string $id Agent ID (UUID)
     * @param string $status Agent status
     * @return bool
     */
    public function updateHeartbeat(string $id, string $status): bool;
}
