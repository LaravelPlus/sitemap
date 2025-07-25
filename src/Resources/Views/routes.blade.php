@extends('sitemap::layouts.app')

@section('title', 'Routes Management')

@section('content')
<div class="p-8 space-y-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">Routes Management</h1>
                <p class="text-blue-100 text-lg">Discover, monitor, and manage all your application routes</p>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="discoverRoutes()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Discover Routes</span>
                </button>
                @if(config('sitemap.status_check.bulk_check_enabled', false))
                <button onclick="checkAllStatus()" class="bg-white text-blue-700 px-6 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center space-x-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Check All Status</span>
                </button>
                @endif
                @if(config('sitemap.thresholds.enabled', true))
                <button onclick="checkThresholdAlerts()" class="bg-white text-orange-700 px-6 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center space-x-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span>Check Alerts</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">Filters & Search</h3>
            <p class="text-sm text-gray-600 mt-1">Find and filter routes by various criteria</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Routes</label>
                    <input type="text" id="searchInput" placeholder="Search by URI or name..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
                        <option value="">All Status</option>
                        <option value="healthy">Healthy</option>
                        <option value="error">With Errors</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                    <select id="environmentFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
                        <option value="">All Environments</option>
                        <option value="production">Production</option>
                        <option value="development">Development</option>
                        <option value="testing">Testing</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortBy" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
                        <option value="uri">URI (A-Z)</option>
                        <option value="uri_desc">URI (Z-A)</option>
                        <option value="last_checked">Last Checked</option>
                        <option value="error_count">Error Count</option>
                        <option value="status_code">Status Code</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-blue-50">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Total</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Routes found</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-green-50">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Healthy</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-green-600">{{ $stats['healthy'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Working properly</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-red-50">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Errors</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-red-600">{{ $stats['with_errors'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Need attention</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gray-50">
                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Unknown</span>
            </div>
            <div class="space-y-1">
                <p class="text-3xl font-bold text-gray-600">{{ $stats['unknown'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Not checked</p>
            </div>
        </div>
    </div>

    <!-- Threshold Alerts -->
    @if(config('sitemap.thresholds.enabled', true))
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">Threshold Alerts</h3>
            <p class="text-sm text-gray-600 mt-1">Response time and error rate monitoring</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 rounded-xl bg-yellow-50 border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-600">{{ config('sitemap.thresholds.response_time.warning', 1000) }}ms</div>
                    <div class="text-sm text-yellow-700">Warning Threshold</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-orange-50 border border-orange-200">
                    <div class="text-2xl font-bold text-orange-600">{{ config('sitemap.thresholds.response_time.critical', 2000) }}ms</div>
                    <div class="text-sm text-orange-700">Critical Threshold</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-red-50 border border-red-200">
                    <div class="text-2xl font-bold text-red-600">{{ config('sitemap.thresholds.response_time.alert', 5000) }}ms</div>
                    <div class="text-sm text-red-700">Alert Threshold</div>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <p><strong>Monitoring:</strong> 
                    @if(config('sitemap.thresholds.monitoring.all_routes', true))
                        All routes
                    @else
                        Specific routes and prefixes
                    @endif
                </p>
                <p><strong>Notifications:</strong> 
                    @if(config('sitemap.thresholds.notifications.enabled', true))
                        Enabled
                    @else
                        Disabled
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Routes Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">All Routes</h3>
            <p class="text-sm text-gray-600 mt-1">Manage and monitor your application routes</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Checked</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Time</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Errors</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($routes as $route)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-primary-100 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $route->uri }}</div>
                                    <div class="text-sm text-gray-500">{{ $route->name ?? 'No name' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($route->status === 'healthy')
                                <span class="badge badge-success">Healthy</span>
                            @elseif($route->status === 'error')
                                <span class="badge badge-error">Error</span>
                            @else
                                <span class="badge badge-warning">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $route->last_checked_at?->diffForHumans() ?? 'Never' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($route->response_time)
                                {{ number_format($route->response_time, 2) }}ms
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($route->error_count > 0)
                                <span class="text-red-600 font-medium">{{ $route->error_count }}</span>
                            @else
                                <span class="text-green-600">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="checkRouteStatus('{{ $route->id }}')" class="text-primary-600 hover:text-primary-900 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                <a href="{{ route('sitemap.route.details', $route) }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <button onclick="testRoute('{{ $route->uri }}')" class="text-green-600 hover:text-green-900 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No routes found</h3>
                                <p class="text-gray-500 mb-4">Start by discovering routes to see them here</p>
                                <button onclick="discoverRoutes()" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Discover Routes
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($routes->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $routes->firstItem() ?? 0 }} to {{ $routes->lastItem() ?? 0 }} of {{ $routes->total() }} results
                </div>
                <div class="flex items-center space-x-2">
                    @if($routes->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-l-lg cursor-not-allowed">Previous</span>
                    @else
                        <a href="{{ $routes->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 hover:text-gray-700 transition-all duration-200">Previous</a>
                    @endif

                    @foreach($routes->getUrlRange(1, $routes->lastPage()) as $page => $url)
                        @if($page == $routes->currentPage())
                            <span class="px-3 py-2 text-sm font-medium bg-primary-600 text-white border border-primary-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 hover:text-gray-700 transition-all duration-200">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($routes->hasMorePages())
                        <a href="{{ $routes->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 hover:text-gray-700 transition-all duration-200">Next</a>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-r-lg cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function discoverRoutes() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('discovery-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    showLoading();
    
    // Create single centered progress indicator for discovery
    const progressDiv = document.createElement('div');
    progressDiv.id = 'discovery-progress';
    progressDiv.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
    progressDiv.innerHTML = `
        <div class="bg-green-500 text-white px-8 py-6 rounded-2xl shadow-2xl max-w-md mx-4">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mr-3"></div>
                    <h3 class="text-lg font-semibold">Discovering Routes</h3>
                </div>
                <div class="space-y-3">
                    <div class="text-sm opacity-90">Please wait while we scan your application</div>
                    <div class="bg-green-600 rounded-full h-2 overflow-hidden">
                        <div id="discovery-progress-bar" class="bg-white h-2 transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <div id="discovery-progress-text" class="text-sm font-medium">Initializing...</div>
                    <div id="discovery-progress-details" class="text-xs opacity-75">Scanning application routes</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(progressDiv);
    
    // Update discovery progress function
    function updateDiscoveryProgress(current, total, message, details) {
        const progressBar = document.getElementById('discovery-progress-bar');
        const progressText = document.getElementById('discovery-progress-text');
        const progressDetails = document.getElementById('discovery-progress-details');
        
        if (progressBar && progressText && progressDetails) {
            const safeCurrent = Math.min(current, total);
            const percentage = total > 0 ? Math.round((safeCurrent / total) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${safeCurrent}/${total}`;
            progressDetails.textContent = message || 'Scanning routes...';
        }
    }
    
    // Simulate discovery progress with proper limits
    let discoveryCounter = 0;
    const maxDiscoverySteps = 50; // Fixed discovery steps
    
    const discoveryInterval = setInterval(() => {
        discoveryCounter++;
        
        // Ensure we don't exceed the total
        if (discoveryCounter > maxDiscoverySteps) {
            clearInterval(discoveryInterval);
            return;
        }
        
        const messages = [
            'Scanning application...',
            'Analyzing route files...',
            'Processing route definitions...',
            'Filtering routes...',
            'Validating routes...',
            'Storing route data...',
            'Finalizing discovery...'
        ];
        const currentMessage = messages[Math.min(Math.floor(discoveryCounter / (maxDiscoverySteps / messages.length)), messages.length - 1)];
        updateDiscoveryProgress(discoveryCounter, maxDiscoverySteps, currentMessage, `Step ${discoveryCounter} of ${maxDiscoverySteps}`);
    }, 150);
    
    fetch('{{ route("sitemap.api.discover") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(discoveryInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('discovery-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        if (data.success) {
            const discoveredRoutes = data.routes?.length || 0;
            const totalRoutes = data.total || 0;
            showNotification(`Route discovery completed! Found ${discoveredRoutes} new routes (${totalRoutes} total)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        clearInterval(discoveryInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('discovery-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        showNotification('Network error occurred', 'error');
    });
}

function checkAllStatus() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('status-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    showLoading();
    
    // Create single centered progress indicator
    const progressDiv = document.createElement('div');
    progressDiv.id = 'status-progress';
    progressDiv.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
    progressDiv.innerHTML = `
        <div class="bg-blue-500 text-white px-8 py-6 rounded-2xl shadow-2xl max-w-md mx-4">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mr-3"></div>
                    <h3 class="text-lg font-semibold">Processing Status Check</h3>
                </div>
                <div class="space-y-3">
                    <div class="text-sm opacity-90">Please wait while we process your request</div>
                    <div class="bg-blue-600 rounded-full h-2 overflow-hidden">
                        <div id="progress-bar" class="bg-white h-2 transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <div id="progress-text" class="text-sm font-medium">Initializing...</div>
                    <div id="progress-details" class="text-xs opacity-75">Preparing to check routes</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(progressDiv);
    
    // Update progress function
    function updateProgress(current, total, message, details) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressDetails = document.getElementById('progress-details');
        
        if (progressBar && progressText && progressDetails) {
            // Ensure current doesn't exceed total
            const safeCurrent = Math.min(current, total);
            const percentage = total > 0 ? Math.round((safeCurrent / total) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${safeCurrent}/${total}`;
            progressDetails.textContent = message || 'Processing routes...';
        }
    }
    
    // Simulate progress updates with proper limits
    let progressCounter = 0;
    const actualRouteCount = {{ $routes->total() }}; // Use actual route count from the page
    const maxProgressSteps = Math.min(actualRouteCount, 100); // Cap at 100 steps max
    
    const progressInterval = setInterval(() => {
        progressCounter++;
        
        // Ensure we don't exceed the total
        if (progressCounter > maxProgressSteps) {
            clearInterval(progressInterval);
            return;
        }
        
        const messages = [
            'Discovering routes...',
            'Preparing batch processing...',
            'Checking route status...',
            'Processing responses...',
            'Analyzing results...',
            'Updating database...',
            'Finalizing check...'
        ];
        const currentMessage = messages[Math.min(Math.floor(progressCounter / (maxProgressSteps / messages.length)), messages.length - 1)];
        updateProgress(progressCounter, maxProgressSteps, currentMessage, `Batch ${Math.ceil(progressCounter / 3)} of ${Math.ceil(maxProgressSteps / 3)}`);
    }, 200);
    
    fetch('{{ route("sitemap.api.check-status") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('status-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        if (data.success) {
            const totalRoutes = data.results?.total || 0;
            const successfulRoutes = data.results?.successful || 0;
            const failedRoutes = data.results?.failed || 0;
            const errorRoutes = data.results?.errors || 0;
            
            showNotification(`Status check completed! Checked ${totalRoutes} routes (${successfulRoutes} successful, ${failedRoutes} failed, ${errorRoutes} errors)`, 'success');
            
            // Show threshold alerts if any
            if (data.results?.threshold_alerts && data.results.threshold_alerts.length > 0) {
                const alertCount = data.results.threshold_alerts.length;
                showNotification(`⚠️ Found ${alertCount} threshold alert(s) - check logs for details`, 'warning');
            }
            
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('status-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        showNotification('Network error occurred', 'error');
    });
}

function checkRouteStatus(routeId) {
    showLoading();
    fetch(`{{ route('sitemap.api.check-route-status') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ route_id: routeId }),
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Route status checked!', 'success');
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

function testRoute(uri) {
    showLoading();
    fetch(`{{ route('sitemap.api.test-route') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ uri: uri }),
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification(`Route test completed! Status: ${data.status_code}`, 'success');
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showNotification('Network error occurred', 'error');
    });
}

function checkThresholdAlerts() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('threshold-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    showLoading();
    
    // Create single centered progress indicator for threshold alerts
    const progressDiv = document.createElement('div');
    progressDiv.id = 'threshold-progress';
    progressDiv.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
    progressDiv.innerHTML = `
        <div class="bg-orange-500 text-white px-8 py-6 rounded-2xl shadow-2xl max-w-md mx-4">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mr-3"></div>
                    <h3 class="text-lg font-semibold">Checking Threshold Alerts</h3>
                </div>
                <div class="space-y-3">
                    <div class="text-sm opacity-90">Please wait while we analyze route performance</div>
                    <div class="bg-orange-600 rounded-full h-2 overflow-hidden">
                        <div id="threshold-progress-bar" class="bg-white h-2 transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <div id="threshold-progress-text" class="text-sm font-medium">Initializing...</div>
                    <div id="threshold-progress-details" class="text-xs opacity-75">Analyzing route thresholds</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(progressDiv);
    
    // Update threshold progress function
    function updateThresholdProgress(current, total, message, details) {
        const progressBar = document.getElementById('threshold-progress-bar');
        const progressText = document.getElementById('threshold-progress-text');
        const progressDetails = document.getElementById('threshold-progress-details');
        
        if (progressBar && progressText && progressDetails) {
            const safeCurrent = Math.min(current, total);
            const percentage = total > 0 ? Math.round((safeCurrent / total) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${safeCurrent}/${total}`;
            progressDetails.textContent = message || 'Analyzing thresholds...';
        }
    }
    
    // Simulate threshold analysis progress with proper limits
    let thresholdCounter = 0;
    const maxThresholdSteps = 25; // Fixed threshold analysis steps
    
    const thresholdInterval = setInterval(() => {
        thresholdCounter++;
        
        // Ensure we don't exceed the total
        if (thresholdCounter > maxThresholdSteps) {
            clearInterval(thresholdInterval);
            return;
        }
        
        const messages = [
            'Loading route data...',
            'Analyzing response times...',
            'Checking error rates...',
            'Evaluating thresholds...',
            'Processing alerts...',
            'Generating report...',
            'Finalizing analysis...'
        ];
        const currentMessage = messages[Math.min(Math.floor(thresholdCounter / (maxThresholdSteps / messages.length)), messages.length - 1)];
        updateThresholdProgress(thresholdCounter, maxThresholdSteps, currentMessage, `Step ${thresholdCounter} of ${maxThresholdSteps}`);
    }, 120);
    
    fetch('{{ route("sitemap.api.threshold-alerts") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(thresholdInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('threshold-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        if (data.success) {
            if (data.total_alerts > 0) {
                showNotification(`⚠️ Found ${data.total_alerts} threshold alerts! Check logs for details.`, 'warning');
                // You could display alerts in a modal or dedicated section
                console.log('Threshold alerts:', data.alerts);
            } else {
                showNotification('✅ No threshold alerts found. All routes are within acceptable limits.', 'success');
            }
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        clearInterval(thresholdInterval);
        hideLoading();
        
        // Remove the progress modal
        const progressDiv = document.getElementById('threshold-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        showNotification('Network error occurred', 'error');
    });
}

// Filter functionality
document.getElementById('searchInput').addEventListener('input', function() {
    filterRoutes();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterRoutes();
});

document.getElementById('environmentFilter').addEventListener('change', function() {
    filterRoutes();
});

document.getElementById('sortBy').addEventListener('change', function() {
    filterRoutes();
});

function filterRoutes() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const environmentFilter = document.getElementById('environmentFilter').value;
    const sortBy = document.getElementById('sortBy').value;
    
    // This would typically make an AJAX request to filter the routes
    // For now, we'll just show a notification
    showNotification('Filter functionality will be implemented', 'info');
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
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

function showLoading() {
    // Disable all action buttons
    const buttons = document.querySelectorAll('button[onclick]');
    buttons.forEach(button => {
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    });
}

function hideLoading() {
    // Re-enable all action buttons
    const buttons = document.querySelectorAll('button[onclick]');
    buttons.forEach(button => {
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}
</script>
@endsection 