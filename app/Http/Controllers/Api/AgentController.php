<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Repositories\Contracts\AgentRepositoryInterface;
use App\Services\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Agent API controller
 * 
 * Handles agent registration, heartbeat, and management via API
 * 
 * @package App\Http\Controllers\Api
 */
class AgentController extends Controller
{
    /**
     * @param AgentRepositoryInterface $agentRepo Agent repository
     * @param AgentService $agentService Agent service
     */
    public function __construct(
        private AgentRepositoryInterface $agentRepo,
        private AgentService $agentService
    ) {
    }

    /**
     * Display a listing of agents
     */
    public function index()
    {
        $data = $this->agentService->getDashboardData();

        return response()->json([
            'success' => true,
            'data' => $data['agents'],
            'summary' => $data['stats'],
        ]);
    }

    /**
     * Register a new agent
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_id' => 'required|string|max:255',
            'hwid' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'os_type' => 'required|string|max:50',
            'os_version' => 'required|string|max:255',
            'cpu_cores' => 'required|integer',
            'total_memory' => 'required|integer',
            'total_disk' => 'required|integer',
            'api_token' => 'required|string',
        ]);

        // Check if agent with same HWID exists (including soft-deleted)
        $existingAgent = $this->agentService->findByHwid($validated['hwid'], true);

        if ($existingAgent) {
            // If agent was soft-deleted, restore it
            if ($existingAgent->trashed()) {
                $existingAgent->restore();
                $existingAgent->update([
                    'hostname' => $validated['hostname'],
                    'ip_address' => $validated['ip_address'],
                    'last_seen_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Agent restored and re-registered successfully',
                    'agent_id' => $existingAgent->id,
                    'api_token' => $validated['api_token'],
                ], 200);
            }

            // Agent exists and is active - update last seen
            $existingAgent->update(['last_seen_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Agent already registered',
                'agent_id' => $existingAgent->id,
                'api_token' => $validated['api_token'],
            ], 200);
        }

        // Create new agent
        $agent = $this->agentService->createAgent([
            'hostname' => $validated['hostname'],
            'ip_address' => $validated['ip_address'],
            'os_type' => $validated['os_type'],
            'os_version' => $validated['os_version'],
            'hwid' => $validated['hwid'],
            'api_token' => $validated['api_token'],
            'cpu_cores' => $validated['cpu_cores'],
            'total_memory_mb' => round($validated['total_memory'] / (1024 * 1024)), // Convert bytes to MB
            'total_disk_gb' => round($validated['total_disk'] / (1024 * 1024 * 1024)), // Convert bytes to GB
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agent registered successfully',
            'agent_id' => $agent->id,
            'api_token' => $validated['api_token'],
        ], 201);
    }

    /**
     * Display the specified agent
     */
    public function show(Agent $agent)
    {
        $agentData = $this->agentService->getAgentDetails($agent->id);

        return response()->json([
            'success' => true,
            'data' => $agentData
        ]);
    }

    /**
     * Update agent heartbeat
     */
    public function heartbeat(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token required'
            ], 401);
        }

        $agent = $this->agentRepo->findByApiToken($token);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        $this->agentService->recordHeartbeat($agent);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
        ]);
    }

    /**
     * Get agents offline for 5+ days
     */
    public function offlineAgents()
    {
        $agents = $this->agentRepo->getOfflineAgents(5);

        return response()->json([
            'success' => true,
            'data' => $agents,
            'count' => $agents->count()
        ]);
    }

    /**
     * Soft delete agent
     */
    public function destroy(Agent $agent)
    {
        $agentId = $agent->agent_id;
        $hostname = $agent->hostname;

        $this->agentService->deleteAgent($agent->id);

        return response()->json([
            'success' => true,
            'message' => "Agent '{$agentId}' ({$hostname}) has been deleted."
        ]);
    }

    /**
     * Restore soft-deleted agent
     */
    public function restore(int $id)
    {
        $agent = Agent::withTrashed()->findOrFail($id);

        if (!$agent->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Agent is not deleted'
            ], 400);
        }

        $agent->restore();

        return response()->json([
            'success' => true,
            'message' => 'Agent restored successfully',
            'data' => $agent
        ]);
    }
}
