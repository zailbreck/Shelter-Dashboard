<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Store services data (batch).
     */
    public function store(Request $request)
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

        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.name' => 'required|string',
            'services.*.pid' => 'required|integer',
            'services.*.status' => 'required|in:running,stopped',
            'services.*.cpu_percent' => 'sometimes|numeric',
            'services.*.memory_percent' => 'sometimes|numeric',
            'services.*.memory_mb' => 'sometimes|integer',
            'services.*.disk_read_mb' => 'sometimes|numeric',
            'services.*.disk_write_mb' => 'sometimes|numeric',
            'services.*.user' => 'sometimes|string',
            'services.*.command' => 'sometimes|string',
        ]);

        // Delete old services for this agent to avoid duplicates
        Service::where('agent_id', $agent->id)->delete();

        $servicesData = [];
        $now = now();

        foreach ($validated['services'] as $service) {
            $servicesData[] = [
                'agent_id' => $agent->id,
                'name' => $service['name'],
                'pid' => $service['pid'],
                'status' => $service['status'],
                'cpu_percent' => $service['cpu_percent'] ?? 0,
                'memory_percent' => $service['memory_percent'] ?? 0,
                'memory_mb' => $service['memory_mb'] ?? 0,
                'disk_read_mb' => $service['disk_read_mb'] ?? 0,
                'disk_write_mb' => $service['disk_write_mb'] ?? 0,
                'user' => $service['user'] ?? 'unknown',
                'command' => $service['command'] ?? null,
                'recorded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Service::insert($servicesData);

        return response()->json([
            'success' => true,
            'message' => 'Services updated successfully',
            'count' => count($servicesData)
        ], 201);
    }

    /**
     * Get services for a specific agent.
     */
    public function index(Request $request, $agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $query = Service::where('agent_id', $agent->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sort options
        $sortBy = $request->input('sort_by', 'name'); // name, cpu_percent, memory_percent, memory_mb
        $sortOrder = $request->input('sort_order', 'asc');

        $query->orderBy($sortBy, $sortOrder);

        $services = $query->get();

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'hostname' => $agent->hostname,
            ],
            'summary' => [
                'total' => $services->count(),
                'running' => $services->where('status', 'running')->count(),
                'stopped' => $services->where('status', 'stopped')->count(),
                'total_cpu_usage' => round($services->sum('cpu_percent'), 2),
                'total_memory_mb' => $services->sum('memory_mb'),
            ],
            'services' => $services
        ]);
    }

    /**
     * Get top services by resource usage.
     */
    public function top($agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $topCpu = Service::where('agent_id', $agent->id)
            ->where('status', 'running')
            ->orderBy('cpu_percent', 'desc')
            ->limit(10)
            ->get();

        $topMemory = Service::where('agent_id', $agent->id)
            ->where('status', 'running')
            ->orderBy('memory_mb', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'hostname' => $agent->hostname,
            ],
            'top_cpu' => $topCpu,
            'top_memory' => $topMemory,
        ]);
    }
}
