<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Service API controller
 * 
 * Handles service/process data via API
 * 
 * @package App\Http\Controllers\Api
 */
class ServiceController extends Controller
{
    /**
     * @param ServiceRepositoryInterface $serviceRepo Service repository
     */
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo
    ) {
    }

    /**
     * Store services data (batch)
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
            'services.*.cpu_percent' => 'nullable|numeric',
            'services.*.memory_percent' => 'nullable|numeric',
            'services.*.memory_mb' => 'nullable|numeric',
            'services.*.disk_read_mb' => 'nullable|numeric',
            'services.*.disk_write_mb' => 'nullable|numeric',
            'services.*.user' => 'nullable|string',
            'services.*.command' => 'nullable|string',
        ]);

        $this->serviceRepo->bulkUpsert($agent->id, $validated['services']);

        return response()->json([
            'success' => true,
            'message' => 'Services updated successfully',
            'count' => count($validated['services'])
        ], 201);
    }

    /**
     * Get services for a specific agent
     */
    public function index(string $agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $services = $this->serviceRepo->getByAgent($agent->id);

        return response()->json([
            'success' => true,
            'data' => $services,
            'count' => $services->count()
        ]);
    }

    /**
     * Get top services by resource usage
     */
    public function top(string $agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $services = $this->serviceRepo->getTopByResource($agent->id, 10);

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
}
