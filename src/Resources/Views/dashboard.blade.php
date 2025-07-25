@extends('sitemap::layouts.app')

@section('title', 'Sitemap Dashboard')

@section('content')
<div class="p-8 space-y-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">Sitemap Dashboard</h1>
                <p class="text-primary-100 text-lg">Monitor and manage your application routes with real-time insights</p>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="discoverRoutes()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Discover Routes</span>
                </button>
                <button onclick="checkStatus()" class="bg-white text-primary-700 px-6 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center space-x-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Check Status</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Routes -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-blue-50 group-hover:bg-blue-100 transition-colors">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Total</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-gray-900">{{ $stats['routes']['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Discovered routes</p>
            </div>
        </div>

        <!-- Healthy Routes -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-green-50 group-hover:bg-green-100 transition-colors">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Healthy</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-green-600">{{ $stats['routes']['healthy'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Working properly</p>
            </div>
        </div>

        <!-- Routes with Errors -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-red-50 group-hover:bg-red-100 transition-colors">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Errors</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-red-600">{{ $stats['routes']['with_errors'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Need attention</p>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-primary-50 group-hover:bg-primary-100 transition-colors">
                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Success</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-primary-600">{{ $stats['routes']['success_rate'] ?? 0 }}%</p>
                <p class="text-sm text-gray-600">Overall health</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Quick Actions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Quick Actions</h3>
                    <p class="text-sm text-gray-600 mt-1">Common tasks to manage your sitemap</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button onclick="discoverRoutes()" class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left hover:shadow-lg hover:border-primary-200 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-xl bg-primary-50 group-hover:bg-primary-100 transition-colors">
                                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">Discover Routes</h4>
                                    <p class="text-sm text-gray-600 mt-1">Find all available routes</p>
                                </div>
                            </div>
                        </button>

                        <button onclick="checkStatus()" class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left hover:shadow-lg hover:border-green-200 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-xl bg-green-50 group-hover:bg-green-100 transition-colors">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">Check Status</h4>
                                    <p class="text-sm text-gray-600 mt-1">Verify route health</p>
                                </div>
                            </div>
                        </button>

                        <button onclick="generateSitemap()" class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left hover:shadow-lg hover:border-purple-200 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-xl bg-purple-50 group-hover:bg-purple-100 transition-colors">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">Generate Sitemap</h4>
                                    <p class="text-sm text-gray-600 mt-1">Create XML sitemap</p>
                                </div>
                            </div>
                        </button>

                        <a href="{{ route('sitemap.settings') }}" class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left hover:shadow-lg hover:border-gray-300 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-xl bg-gray-50 group-hover:bg-gray-100 transition-colors">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">Settings</h4>
                                    <p class="text-sm text-gray-600 mt-1">Configure preferences</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            @if(isset($stats['performance']))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                    <h3 class="text-xl font-semibold text-blue-900">Performance Metrics</h3>
                    <p class="text-sm text-blue-700 mt-1">System optimization and performance data</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['performance']['execution_time_ms'] }}ms</div>
                            <div class="text-sm text-gray-600">Dashboard Load Time</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['performance']['cache_hits'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Cache Hits</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $stats['performance']['cache_misses'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Cache Misses</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed Statistics -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Detailed Statistics</h3>
                    <p class="text-sm text-gray-600 mt-1">Comprehensive breakdown of route status</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['routes']['total'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total Routes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['routes']['healthy'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Healthy Routes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $stats['routes']['with_errors'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Routes with Errors</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $stats['routes']['unknown'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Unknown Status</div>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-primary-600">{{ $stats['routes']['success_rate'] ?? 0 }}%</div>
                                <div class="text-sm text-gray-600">Success Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-blue-600">{{ $stats['routes']['recently_checked'] ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Recently Checked</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-600">{{ $stats['routes']['avg_response_time'] ?? 0 }}ms</div>
                                <div class="text-sm text-gray-600">Avg Response Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">System Status</h3>
                    <p class="text-sm text-gray-600 mt-1">Current system information</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">Environment</span>
                            <span class="badge badge-success">{{ app()->environment() }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">Application</span>
                            <span class="text-sm font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">Last Check</span>
                            <span class="text-sm font-semibold text-gray-900">{{ now()->format('M j, g:i A') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-green-50">
                            <span class="text-sm font-medium text-gray-700">Status</span>
                            <span class="badge badge-success">Operational</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Errors -->
        @if($recentErrors->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-red-50 to-red-100 px-6 py-4 border-b border-red-200">
                <h3 class="text-xl font-semibold text-red-900">Recent Errors</h3>
                <p class="text-sm text-red-700 mt-1">Latest issues that need attention</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentErrors->take(5) as $error)
                    <div class="flex items-start space-x-4 p-4 rounded-xl hover:bg-red-50 transition-colors border border-red-100">
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <a href="{{ route('sitemap.route.details', $error->route) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">
                                    {{ $error->route->uri }}
                                </a>
                                <span class="badge badge-error text-xs">{{ $error->error_type }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $error->truncated_message }}</p>
                            <p class="text-xs text-gray-500">{{ $error->occurred_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Routes with Errors -->
        @if($routesWithErrors->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                <h3 class="text-xl font-semibold text-orange-900">Routes with Errors</h3>
                <p class="text-sm text-orange-700 mt-1">Routes that need immediate attention</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($routesWithErrors->take(5) as $route)
                    <div class="flex items-center justify-between p-4 rounded-xl hover:bg-orange-50 transition-colors border border-orange-100">
                        <div class="flex-1">
                            <a href="{{ route('sitemap.route.details', $route) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $route->uri }}
                            </a>
                            <p class="text-xs text-gray-500 mt-1">{{ $route->last_checked_at?->diffForHumans() ?? 'Never checked' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="badge badge-error">{{ $route->error_count }} errors</span>
                            <span class="badge badge-info">{{ $route->last_status_code ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function discoverRoutes() {
    showLoading();
    fetch('{{ route("sitemap.discover") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Routes discovered successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showNotification('Network error occurred', 'error');
    });
}

function checkStatus() {
    showLoading();
    fetch('{{ route("sitemap.check-status") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Status check completed!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showNotification('Network error occurred', 'error');
    });
}

function generateSitemap() {
    window.location.href = '{{ route("sitemap.generate") }}';
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endsection 