<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Repositories\Contracts\AgentRepositoryInterface;
use App\Services\AgentService;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|string',
            'hwid' => 'required|string|max:16',
            'hostname' => 'required|string',
            'ip_address' => 'required|ip',
            'os_type' => 'required|string',
            'os_version' => 'required|string',
            'cpu_cores' => 'required|integer|min:1',
            'total_memory' => 'required|integer|min:1',
            'total_disk' => 'required|integer|min:1',
            'api_token' => 'required|string',
        ]);

        $agent = $this->agentService->registerAgent($validated);

        return response()->json([
            'success' => true,
            'message' => 'Agent registered successfully',
            'data' => $agent,
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
