<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentService;
use App\Models\Agent;
use Illuminate\Http\Request;

/**
 * Dashboard controller
 * 
 * Handles main dashboard and agent detail pages
 * 
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * @param AgentService $agentService Agent service
     */
    public function __construct(
        private AgentService $agentService
    ) {
    }

    /**
     * Show dashboard with all agents
     */
    public function index()
    {
        $data = $this->agentService->getDashboardData();

        return view('dashboard.index', $data);
    }

    /**
     * Show agent details
     */
    public function show(Agent $agent)
    {
        $agentData = $this->agentService->getAgentDetails($agent->id);

        return view('dashboard.show', ['agent' => $agentData]);
    }

    /**
     * Delete agent
     */
    public function destroy(Agent $agent)
    {
        $agentId = $agent->agent_id;
        $hostname = $agent->hostname;

        $this->agentService->deleteAgent($agent->id);

        return redirect()->route('dashboard')
            ->with('success', "Agent {$agentId} ({$hostname}) has been deleted.");
    }
}
