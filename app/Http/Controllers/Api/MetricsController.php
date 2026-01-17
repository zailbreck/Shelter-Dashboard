<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Repositories\Contracts\MetricsRepositoryInterface;
use App\Services\MetricsService;
use Illuminate\Http\Request;

/**
 * Metrics API controller
 * 
 * Handles metrics storage and retrieval via API
 * 
 * @package App\Http\Controllers\Api
 */
class MetricsController extends Controller
{
    /**
     * @param MetricsRepositoryInterface $metricsRepo Metrics repository
     * @param MetricsService $metricsService Metrics service
     */
    public function __construct(
        private MetricsRepositoryInterface $metricsRepo,
        private MetricsService $metricsService
    ) {
    }

    /**
     * Store metrics for an agent
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
            'metrics' => 'required|array',
            'metrics.*.metric_type' => 'required|in:cpu,memory,disk,network,io',
            'metrics.*.value' => 'required|numeric|min:0',
            'metrics.*.unit' => 'sometimes|string',
        ]);

        // Pass agent ID as string, not Agent model
        $this->metricsService->storeMetrics($agent->id, $validated['metrics']);

        return response()->json([
            'success' => true,
            'message' => 'Metrics stored successfully',
            'count' => count($validated['metrics'])
        ], 201);
    }

    /**
     * Get realtime metrics for an agent
     */
    public function realtime(string $agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $metrics = $this->metricsService->getRealtimeMetrics($agent);

        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get historical metrics for charts
     */
    public function show(string $agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $history = $this->metricsService->getHistoricalMetrics($agent);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get recent snapshots
     */
    public function snapshots(string $agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $snapshot = $this->metricsRepo->getLatestSnapshot($agent->id);

        return response()->json([
            'success' => true,
            'data' => $snapshot
        ]);
    }
}
