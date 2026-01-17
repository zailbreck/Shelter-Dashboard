<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Metric;
use App\Models\MetricSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Store metrics data (batch).
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
            'metrics.*.value' => 'required|numeric',
            'metrics.*.unit' => 'required|string|max:20',
            'metrics.*.recorded_at' => 'sometimes|date',
        ]);

        $metricsData = [];
        $now = now();

        foreach ($validated['metrics'] as $metric) {
            $metricsData[] = [
                'agent_id' => $agent->id,
                'metric_type' => $metric['metric_type'],
                'value' => $metric['value'],
                'unit' => $metric['unit'],
                'recorded_at' => $metric['recorded_at'] ?? $now,
                'created_at' => $now,
            ];
        }

        Metric::insert($metricsData);

        // Update agent last seen
        $agent->update([
            'status' => 'online',
            'last_seen_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metrics stored successfully',
            'count' => count($metricsData)
        ], 201);
    }

    /**
     * Get metrics for a specific agent.
     */
    public function show(Request $request, $agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $hours = $request->input('hours', 1); // Default last 1 hour
        $metricType = $request->input('type', null); // Filter by type

        $query = Metric::where('agent_id', $agent->id)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'desc');

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }

        $metrics = $query->get();

        // Group by metric type for easier consumption
        $groupedMetrics = $metrics->groupBy('metric_type')->map(function ($items) {
            return [
                'current' => $items->first()?->value,
                'unit' => $items->first()?->unit,
                'avg' => round($items->avg('value'), 2),
                'min' => round($items->min('value'), 2),
                'max' => round($items->max('value'), 2),
                'data_points' => $items->count(),
                'history' => $items->map(function ($item) {
                    return [
                        'value' => $item->value,
                        'timestamp' => $item->recorded_at->toIso8601String(),
                    ];
                })->values()
            ];
        });

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'hostname' => $agent->hostname,
                'status' => $agent->status,
            ],
            'time_range' => [
                'from' => now()->subHours($hours)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'hours' => $hours,
            ],
            'metrics' => $groupedMetrics
        ]);
    }

    /**
     * Get metric snapshots (aggregated data).
     */
    public function snapshots(Request $request, $agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $period = $request->input('period', '1hour'); // 1min, 5min, 1hour, 1day
        $hours = $request->input('hours', 24);
        $metricType = $request->input('type', null);

        $query = MetricSnapshot::where('agent_id', $agent->id)
            ->where('snapshot_period', $period)
            ->where('snapshot_time', '>=', now()->subHours($hours))
            ->orderBy('snapshot_time', 'desc');

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }

        $snapshots = $query->get();

        // Group by metric type
        $groupedSnapshots = $snapshots->groupBy('metric_type');

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'hostname' => $agent->hostname,
            ],
            'period' => $period,
            'snapshots' => $groupedSnapshots
        ]);
    }

    /**
     * Get real-time latest metrics for all metric types.
     */
    public function realtime($agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $latestMetrics = Metric::where('agent_id', $agent->id)
            ->where('recorded_at', '>=', now()->subMinutes(5))
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('metric_type')
            ->map(function ($items) {
                $latest = $items->first();
                return [
                    'value' => $latest->value,
                    'unit' => $latest->unit,
                    'recorded_at' => $latest->recorded_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'agent_id' => $agent->id,
            'hostname' => $agent->hostname,
            'status' => $agent->status,
            'timestamp' => now()->toIso8601String(),
            'metrics' => $latestMetrics,
        ]);
    }
}
