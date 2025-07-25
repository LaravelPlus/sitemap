@extends('sitemap::layouts.app')

@section('title', 'Route Details - Sitemap Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">Route Details</h1>
                    <p class="text-indigo-100 mt-2">{{ $route->uri ?? 'Route Information' }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('sitemap.routes') }}" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Back to Routes</span>
                    </a>
                    <button onclick="testRoute()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Test Route</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Information -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Route Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Route Information</h3>
                    <p class="text-sm text-gray-600 mt-1">Basic route details and configuration</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">URI</span>
                            <span class="text-sm text-gray-600">{{ $route->uri ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">Name</span>
                            <span class="text-sm text-gray-600">{{ $route->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">Controller</span>
                            <span class="text-sm text-gray-600">{{ $route->controller ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">Methods</span>
                            <span class="text-sm text-gray-600">{{ is_array($route->methods) ? implode(', ', $route->methods) : $route->methods ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">Environment</span>
                            <span class="text-sm text-gray-600">{{ $route->environment ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="font-medium text-gray-900">Status</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $route->is_healthy ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $route->is_healthy ? 'Healthy' : 'Unhealthy' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Statistics -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Route Statistics</h3>
                    <p class="text-sm text-gray-600 mt-1">Performance and health metrics</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <div class="text-2xl font-bold text-blue-600">{{ $route->last_status_code ?? 'N/A' }}</div>
                            <div class="text-sm text-blue-700">Last Status</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <div class="text-2xl font-bold text-green-600">{{ $route->last_response_time ?? 'N/A' }}ms</div>
                            <div class="text-sm text-green-700">Response Time</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-xl">
                            <div class="text-2xl font-bold text-yellow-600">{{ $route->error_count ?? 0 }}</div>
                            <div class="text-sm text-yellow-700">Error Count</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <div class="text-2xl font-bold text-purple-600">{{ $route->priority ?? 0.5 }}</div>
                            <div class="text-sm text-purple-700">Priority</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Status Checks -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Recent Status Checks</h3>
                <p class="text-sm text-gray-600 mt-1">Latest health monitoring results</p>
            </div>
            <div class="p-6">
                @if(isset($recentStatusChecks) && $recentStatusChecks->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentStatusChecks as $check)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($check->status_code >= 200 && $check->status_code < 300)
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    @elseif($check->status_code >= 400 && $check->status_code < 500)
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                    @else
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Status: {{ $check->status_code ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-600">{{ $check->response_time ?? 0 }}ms • {{ $check->checked_at ? $check->checked_at->diffForHumans() : 'Unknown' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $check->environment ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No status checks</h3>
                        <p class="mt-1 text-sm text-gray-500">No status checks have been performed for this route yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Errors -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Recent Errors</h3>
                <p class="text-sm text-gray-600 mt-1">Latest error occurrences for this route</p>
            </div>
            <div class="p-6">
                @if(isset($recentErrors) && $recentErrors->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentErrors as $error)
                        <div class="p-4 bg-red-50 rounded-xl border border-red-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-red-900">{{ $error->error_type ?? 'Unknown Error' }}</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $error->error_message ?? 'No error message available' }}</p>
                                    <p class="text-xs text-red-600 mt-2">{{ $error->occurred_at ? $error->occurred_at->diffForHumans() : 'Unknown time' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-red-600">{{ $error->environment ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No errors</h3>
                        <p class="mt-1 text-sm text-gray-500">No errors have been recorded for this route.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function testRoute() {
    const routeId = {{ $route->id ?? 0 }};
    
    // Show loading state
    const testButton = document.querySelector('button[onclick="testRoute()"]');
    const originalText = testButton.innerHTML;
    testButton.innerHTML = `
        <svg class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <span>Testing...</span>
    `;
    testButton.disabled = true;

    fetch('{{ route("sitemap.api.test-route") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            route_id: routeId,
        }),
    })
    .then(response => response.json())
    .then(data => {
        // Restore button state
        testButton.innerHTML = originalText;
        testButton.disabled = false;

        if (data.success) {
            showNotification(`Route test completed! Status: ${data.status_code}, Response time: ${data.response_time}ms`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        // Restore button state
        testButton.innerHTML = originalText;
        testButton.disabled = false;
        showNotification('Network error occurred', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-2xl text-white font-medium transition-all duration-300 transform translate-x-full`;
    
    if (type === 'success') {
        notification.classList.add('bg-green-500');
    } else if (type === 'error') {
        notification.classList.add('bg-red-500');
    } else if (type === 'warning') {
        notification.classList.add('bg-yellow-500');
    } else {
        notification.classList.add('bg-blue-500');
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
</script>
@endsection 