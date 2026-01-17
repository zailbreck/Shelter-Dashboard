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
     * @param string $agentId Agent ID
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
     * @param string $agentId Agent ID
     * @return bool Success status
     */
    public function deleteAgent(string $agentId): bool
    {
        return $this->agentRepo->delete($agentId);
    }

    /**
     * Find agent by HWID (including soft-deleted)
     * 
     * @param string $hwid Hardware ID
     * @param bool $withTrashed Include soft-deleted agents
     * @return Agent|null Agent model
     */
    public function findByHwid(string $hwid, bool $withTrashed = false): ?Agent
    {
        $query = Agent::where('hwid', $hwid);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Create new agent
     * 
     * @param array $data Agent data
     * @return Agent Created agent
     */
    public function createAgent(array $data): Agent
    {
        return $this->agentRepo->create([
            'hostname' => $data['hostname'],
            'ip_address' => $data['ip_address'],
            'os_type' => $data['os_type'],
            'os_version' => $data['os_version'],
            'hwid' => $data['hwid'],
            'api_token' => $data['api_token'] ?? null,
            'cpu_cores' => $data['cpu_cores'] ?? null,
            'total_memory_mb' => $data['total_memory_mb'] ?? null,
            'total_disk_gb' => $data['total_disk_gb'] ?? null,
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
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
