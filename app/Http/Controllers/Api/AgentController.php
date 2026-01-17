<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display a listing of agents.
     */
    public function index()
    {
        $agents = Agent::with([
            'metrics' => function ($q) {
                $q->where('recorded_at', '>=', now()->subMinutes(5))
                    ->latest('recorded_at');
            }
        ])
            ->withCount('services')
            ->orderBy('status')
            ->orderBy('hostname')
            ->get();

        // Count agents offline for 5+ days
        $offline_count = Agent::offlineForDays(5)->count();

        return response()->json([
            'success' => true,
            'data' => $agents,
            'summary' => [
                'total' => $agents->count(),
                'online' => $agents->where('status', 'online')->count(),
                'offline' => $agents->where('status', 'offline')->count(),
                'error' => $agents->where('status', 'error')->count(),
                'offline_5_days' => $offline_count,
            ]
        ]);
    }

    /**
     * Register a new agent with HWID-based authentication.
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

        // Check if agent already exists (by agent_id or hwid-hostname)
        $agent = Agent::withTrashed()
            ->where('agent_id', $validated['agent_id'])
            ->orWhere(function ($q) use ($validated) {
                $q->where('hwid', $validated['hwid'])
                    ->where('hostname', $validated['hostname']);
            })
            ->first();

        if ($agent) {
            // Agent exists, restore if soft deleted
            if ($agent->trashed()) {
                $agent->restore();
                logger()->info("Restoring soft-deleted agent: {$validated['agent_id']}");
            }

            // Update agent info
            $agent->update([
                'agent_id' => $validated['agent_id'],
                'hwid' => $validated['hwid'],
                'hostname' => $validated['hostname'],
                'ip_address' => $validated['ip_address'],
                'os_type' => $validated['os_type'],
                'os_version' => $validated['os_version'],
                'cpu_cores' => $validated['cpu_cores'],
                'total_memory' => $validated['total_memory'],
                'total_disk' => $validated['total_disk'],
                'api_token' => $validated['api_token'],
                'status' => 'online',
                'last_seen_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agent re-registered successfully',
                'data' => $agent,
            ], 200);
        }

        // Create new agent
        $agent = Agent::create([
            ...$validated,
            'status' => 'online',
            'last_seen_at' => now(),
            'registered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agent registered successfully',
            'data' => $agent,
        ], 201);
    }

    /**
     * Display the specified agent.
     */
    public function show(Agent $agent)
    {
        $agent->load([
            'metrics' => function ($q) {
                $q->where('recorded_at', '>=', now()->subHour())
                    ->orderBy('recorded_at', 'desc');
            },
            'metricSnapshots' => function ($q) {
                $q->where('snapshot_time', '>=', now()->subDay())
                    ->orderBy('snapshot_time', 'desc');
            },
            'services' => function ($q) {
                $q->latest('recorded_at');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $agent
        ]);
    }

    /**
     * Update agent heartbeat / keep-alive.
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

        $agent = Agent::where('api_token', $token)->first();

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        $agent->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
            'data' => [
                'agent_id' => $agent->agent_id,
                'hostname' => $agent->hostname,
                'last_seen_at' => $agent->last_seen_at,
            ]
        ]);
    }

    /**
     * Get agents offline for 5+ days (candidates for deletion).
     */
    public function offlineAgents()
    {
        $agents = Agent::offlineForDays(5)
            ->orderBy('last_seen_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $agents,
            'count' => $agents->count()
        ]);
    }

    /**
     * Soft delete agent (keep metrics history).
     */
    public function destroy(Agent $agent)
    {
        $agentId = $agent->agent_id;
        $hostname = $agent->hostname;

        // Soft delete - metrics history is preserved
        $agent->delete();

        return response()->json([
            'success' => true,
            'message' => "Agent '{$agentId}' ({$hostname}) has been deleted. Metrics history preserved."
        ]);
    }

    /**
     * Restore soft-deleted agent.
     */
    public function restore($id)
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
