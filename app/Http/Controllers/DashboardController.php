<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $agents = Agent::withCount(['services'])
            ->orderBy('status')
            ->orderBy('hostname')
            ->get();

        // Get agents offline for 5+ days
        $offline_agents = Agent::offlineForDays(5)->get();

        return view('dashboard.index', compact('agents', 'offline_agents'));
    }

    public function show(Agent $agent)
    {
        $agent->load([
            'services' => function ($q) {
                $q->latest('recorded_at')->limit(50);
            }
        ]);

        return view('dashboard.show', compact('agent'));
    }

    public function destroy(Agent $agent)
    {
        $agent_id = $agent->agent_id;
        $hostname = $agent->hostname;

        // Soft delete
        $agent->delete();

        return redirect()->route('dashboard')
            ->with('success', "Agent {$agent_id} ({$hostname}) has been deleted. Metrics history preserved.");
    }
}
