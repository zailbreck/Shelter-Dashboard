<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Repositories\Contracts\AgentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Agent service
 * 
 * Handles agent management business logic
 * 
 * @package App\Services
 */
class AgentService
{
    /**
     * @param AgentRepositoryInterface $agentRepo Agent repository
     */
    public function __construct(
        private AgentRepositoryInterface $agentRepo
    ) {
    }

    /**
     * Get dashboard data with agents and statistics
     * 
     * @return array Dashboard data
     */
    public function getDashboardData(): array
    {
        $agents = $this->agentRepo->withServiceCount();
        $offlineAgents = $this->agentRepo->getOfflineAgents(5);

        return [
            'agents' => $agents,
            'offline_agents' => $offlineAgents,
            'stats' => $this->calculateStats($agents),
        ];
    }

    /**
     * Get agent details with services
     * 
     * @param int $agentId Agent ID
     * @return Agent|null Agent with relationships
     */
    public function getAgentDetails(string $agentId): ?Agent
    {
        return $this->agentRepo->withServices($agentId);
    }

    /**
     * Register new agent
     * 
     * @param array $data Agent data
     * @return Agent Created agent
     */
    public function registerAgent(array $data): Agent
    {
        // Check if agent already exists
        $existing = $this->agentRepo->findByApiToken($data['api_token']);

        if ($existing) {
            // Update existing agent
            $this->agentRepo->update($existing->id, $data);
            return $existing->fresh();
        }

        // Create new agent
        return $this->agentRepo->create($data);
    }

    /**
     * Record agent heartbeat
     * 
     * @param Agent $agent Agent model
     * @return bool Success status
     */
    public function recordHeartbeat(Agent $agent): bool
    {
        return $this->agentRepo->updateHeartbeat($agent->id, 'online');
    }

    /**
     * Delete agent
     * 
     * @param int $agentId Agent ID
     * @return bool Success status
     */
    public function deleteAgent(string $agentId): bool
    {
        return $this->agentRepo->delete($agentId);
    }

    /**
     * Calculate agent statistics
     * 
     * @param Collection $agents Agent collection
     * @return array Statistics
     */
    private function calculateStats(Collection $agents): array
    {
        return [
            'total' => $agents->count(),
            'online' => $agents->where('status', 'online')->count(),
            'offline' => $agents->where('status', 'offline')->count(),
        ];
    }
}
