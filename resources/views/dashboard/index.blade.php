@extends('layouts.app')

@section('title', 'Dashboard - ShelterAgent')

@section('content')
    <div x-data="dashboard()" x-init="init()">
        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
            <p class="text-gray-500 mt-1">Monitor all your servers in real-time</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Agents -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Agents</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $agents->count() }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Online -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Online</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $agents->where('status', 'online')->count() }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Offline -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Offline</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $agents->where('status', 'offline')->count() }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Offline 5+ Days -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Offline 5+ Days</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $offline_agents->count() }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offline Agents Warning -->
        @if($offline_agents->count() > 0)
            <div class="mb-6 bg-orange-50 border border-orange-200 rounded-lg p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-orange-800">Agents offline for 5+ days</h3>
                        <div class="mt-2 text-sm text-orange-700">
                            <p>{{ $offline_agents->count() }} agent(s) have been offline for more than 5 days. You can delete
                                them to clean up the dashboard.</p>
                        </div>
                        <div class="mt-4">
                            <button @click="showOfflineModal = true"
                                class="text-sm font-medium text-orange-800 hover:text-orange-900 underline">
                                View and manage offline agents →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Agents Grid -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Agents</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($agents as $agent)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Status Indicator -->
                                <div class="flex-shrink-0">
                                    @if($agent->status === 'online')
                                        <span class="flex h-3 w-3 relative">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                        </span>
                                    @elseif($agent->status === 'offline')
                                        <span class="inline-flex h-3 w-3 rounded-full bg-gray-400"></span>
                                    @else
                                        <span class="inline-flex h-3 w-3 rounded-full bg-yellow-500"></span>
                                    @endif
                                </div>

                                <!-- Agent Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $agent->hostname }}</h3>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    @if($agent->status === 'online') bg-green-100 text-green-800
                                                    @elseif($agent->status === 'offline') bg-gray-100 text-gray-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                            {{ ucfirst($agent->status) }}
                                        </span>
                                        @if($agent->isOfflineForDays(5))
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                                Offline {{ $agent->last_seen_at ? $agent->last_seen_at->diffInDays(now()) : '∞' }}
                                                days
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                        <span
                                            class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $agent->agent_id }}</span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                            {{ $agent->ip_address }}
                                        </span>
                                        <span>{{ $agent->os_type }}</span>
                                        <span>{{ $agent->cpu_cores }} cores</span>
                                        <span>{{ number_format($agent->total_memory / 1024 / 1024 / 1024, 1) }} GB RAM</span>
                                    </div>
                                    @if($agent->last_seen_at)
                                        <p class="mt-1 text-xs text-gray-400">
                                            Last seen: {{ $agent->last_seen_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('agent.show', $agent) }}"
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No agents found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by registering your first agent.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Offline Agents Modal -->
        <div x-show="showOfflineModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showOfflineModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showOfflineModal = false"></div>

                <div x-show="showOfflineModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Agents Offline for 5+ Days</h3>

                        <div class="space-y-3">
                            @foreach($offline_agents as $offline_agent)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $offline_agent->hostname }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">{{ $offline_agent->agent_id }}</p>
                                            <p class="text-sm text-gray-500">Last seen:
                                                {{ $offline_agent->last_seen_at?->diffForHumans() ?? 'Never' }}
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('agent.delete', $offline_agent) }}"
                                            onsubmit="return confirm('Delete agent {{ $offline_agent->agent_id }}? Metrics history will be preserved.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                                                Delete Agent
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showOfflineModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function dashboard() {
            return {
                showOfflineModal: false,

                init() {
                    console.log('Dashboard initialized');
                    // Auto-refresh removed to prevent page growth bug
                    // Users can manually refresh the page when needed
                }
            }
        }
    </script>
@endsection