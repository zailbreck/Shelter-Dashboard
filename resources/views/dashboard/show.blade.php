@extends('layouts.app')

@section('title', $agent->hostname . ' - ShelterAgent')

@section('content')
    <div x-data="agentDetail({{ $agent->id }})" x-init="init()">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-primary hover:text-blue-600 text-sm mb-2 inline-block">
                        ← Back to Dashboard
                    </a>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $agent->hostname }}</h1>
                        <span class="px-3 py-1 text-sm font-medium rounded-full 
                            @if($agent->status === 'online') bg-green-100 text-green-800
                            @elseif($agent->status === 'offline') bg-gray-100 text-gray-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst($agent->status) }}
                        </span>
                    </div>
                    <p class="text-gray-500 mt-1">{{ $agent->ip_address }} • {{ $agent->os_type }} {{ $agent->os_version }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Last seen</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $agent->last_seen_at?->diffForHumans() ?? 'Never' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">CPU Cores</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $agent->cpu_cores }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total RAM</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($agent->total_memory / 1024 / 1024 / 1024, 1) }} GB</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Disk</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($agent->total_disk / 1024 / 1024 / 1024, 0) }} GB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Metrics -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Real-time Metrics</h2>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4" x-show="metrics">
                <!-- CPU -->
                <div
                    class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                    <div class="text-sm font-medium text-purple-700 mb-2">CPU</div>
                    <div class="text-3xl font-bold text-purple-900" x-text="metrics?.cpu?.value || '-'"></div>
                    <div class="text-xs text-purple-600 mt-1" x-text="metrics?.cpu?.unit || ''"></div>
                </div>

                <!-- Memory -->
                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                    <div class="text-sm font-medium text-blue-700 mb-2">Memory</div>
                    <div class="text-3xl font-bold text-blue-900" x-text="metrics?.memory?.value || '-'"></div>
                    <div class="text-xs text-blue-600 mt-1" x-text="metrics?.memory?.unit || ''"></div>
                </div>

                <!-- Disk -->
                <div
                    class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                    <div class="text-sm font-medium text-green-700 mb-2">Disk</div>
                    <div class="text-3xl font-bold text-green-900" x-text="metrics?.disk?.value || '-'"></div>
                    <div class="text-xs text-green-600 mt-1" x-text="metrics?.disk?.unit || ''"></div>
                </div>

                <!-- Network -->
                <div
                    class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border border-yellow-200">
                    <div class="text-sm font-medium text-yellow-700 mb-2">Network</div>
                    <div class="text-3xl font-bold text-yellow-900" x-text="metrics?.network?.value || '-'"></div>
                    <div class="text-xs text-yellow-600 mt-1" x-text="metrics?.network?.unit || ''"></div>
                </div>

                <!-- I/O -->
                <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200">
                    <div class="text-sm font-medium text-red-700 mb-2">I/O</div>
                    <div class="text-3xl font-bold text-red-900" x-text="metrics?.io?.value || '-'"></div>
                    <div class="text-xs text-red-600 mt-1" x-text="metrics?.io?.unit || ''"></div>
                </div>
            </div>

            <div x-show="!metrics" class="text-center py-8 text-gray-500">
                Loading metrics...
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- CPU Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">CPU Usage (1 Hour)</h3>
                <canvas id="cpuChart" height="200"></canvas>
            </div>

            <!-- Memory Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Memory Usage (1 Hour)</h3>
                <canvas id="memoryChart" height="200"></canvas>
            </div>

            <!-- Disk Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Disk Usage (1 Hour)</h3>
                <canvas id="diskChart" height="200"></canvas>
            </div>

            <!-- Network Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Traffic (1 Hour)</h3>
                <canvas id="networkChart" height="200"></canvas>
            </div>
        </div>

        <!-- Services List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Running Services (Top 50)</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU %
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Memory MB</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($agent->services as $service)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $service->pid }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($service->status === 'running') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $service->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $service->cpu_percent }}%</span>
                                        <div class="ml-2 w-20 bg-gray-200 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full"
                                                style="width: {{ min($service->cpu_percent, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($service->memory_mb, 0) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $service->user }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No services data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function agentDetail(agentId) {
            return {
                agentId: agentId,
                metrics: null,
                charts: {},

                async init() {
                    await this.loadRealtimeMetrics();
                    await this.loadHistoricalMetrics();

                    // Auto-refresh every 10 seconds
                    setInterval(() => {
                        this.loadRealtimeMetrics();
                        this.loadHistoricalMetrics();
                    }, 10000);
                },

                async loadRealtimeMetrics() {
                    try {
                        const response = await fetch(`/api/metrics/${this.agentId}/realtime`);
                        const data = await response.json();
                        if (data.success) {
                            this.metrics = data.metrics;
                        }
                    } catch (error) {
                        console.error('Error loading realtime metrics:', error);
                    }
                },

                async loadHistoricalMetrics() {
                    try {
                        const response = await fetch(`/api/metrics/${this.agentId}?hours=1`);
                        const data = await response.json();

                        if (data.success) {
                            this.updateCharts(data.metrics);
                        }
                    } catch (error) {
                        console.error('Error loading historical metrics:', error);
                    }
                },

                updateCharts(metricsData) {
                    // CPU Chart
                    this.updateChart('cpuChart', 'CPU Usage (%)', metricsData.cpu?.history || [], 'rgb(147, 51, 234)');

                    // Memory Chart
                    this.updateChart('memoryChart', 'Memory Usage (%)', metricsData.memory?.history || [], 'rgb(59, 130, 246)');

                    // Disk Chart
                    this.updateChart('diskChart', 'Disk Usage (%)', metricsData.disk?.history || [], 'rgb(16, 185, 129)');

                    // Network Chart
                    this.updateChart('networkChart', 'Network (Mbps)', metricsData.network?.history || [], 'rgb(245, 158, 11)');
                },

                updateChart(chartId, label, history, color) {
                    const ctx = document.getElementById(chartId);
                    if (!ctx) return;

                    // Destroy existing chart
                    if (this.charts[chartId]) {
                        this.charts[chartId].destroy();
                    }

                    // Prepare data
                    const labels = history.map(h => new Date(h.timestamp).toLocaleTimeString());
                    const values = history.map(h => h.value);

                    // Create new chart
                    this.charts[chartId] = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels.reverse(),
                            datasets: [{
                                label: label,
                                data: values.reverse(),
                                borderColor: color,
                                backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 10
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
@endsection