<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\ServiceController;

// Agent Management
Route::prefix('agent')->group(function () {
    Route::post('/register', [AgentController::class, 'store']);
    Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
});

// Agents List (for dashboard)
Route::apiResource('agents', AgentController::class);

// Additional agent endpoints
Route::get('/agents/offline/candidates', [AgentController::class, 'offlineAgents']);
Route::post('/agents/{id}/restore', [AgentController::class, 'restore']);

// Metrics
Route::prefix('metrics')->group(function () {
    Route::post('/', [MetricsController::class, 'store']);
    Route::get('/{agentId}', [MetricsController::class, 'show']);
    Route::get('/{agentId}/snapshots', [MetricsController::class, 'snapshots']);
    Route::get('/{agentId}/realtime', [MetricsController::class, 'realtime']);
});

// Services
Route::prefix('services')->group(function () {
    Route::post('/', [ServiceController::class, 'store']);
    Route::get('/{agentId}', [ServiceController::class, 'index']);
    Route::get('/{agentId}/top', [ServiceController::class, 'top']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});
