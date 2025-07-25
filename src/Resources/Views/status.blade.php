@extends('sitemap::layouts.app')

@section('title', 'Status Checks - Sitemap Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">Status Checks</h1>
                    <p class="text-blue-100 mt-2">Monitor and analyze route health status</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="checkAllStatus()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Check All Status</span>
                    </button>
                    <button onclick="checkThresholdAlerts()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span>Check Alerts</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                    <p class="text-3xl font-bold text-green-600">{{ $healthyCount ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Routes working properly</p>
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
                    <p class="text-3xl font-bold text-red-600">{{ $errorCount ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Routes with issues</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-yellow-50">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Warnings</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-yellow-600">{{ $warningCount ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Routes with warnings</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-50">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Avg Response</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-blue-600">{{ $avgResponseTime ?? 0 }}ms</p>
                    <p class="text-sm text-gray-600">Average response time</p>
                </div>
            </div>
        </div>

        <!-- Recent Status Checks -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Recent Status Checks</h3>
                <p class="text-sm text-gray-600 mt-1">Latest route health monitoring results</p>
            </div>
            <div class="p-6">
                @if(isset($recentChecks) && $recentChecks->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentChecks as $check)
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
                                    <p class="font-medium text-gray-900">{{ $check->route->uri ?? 'Unknown Route' }}</p>
                                    <p class="text-sm text-gray-600">Status: {{ $check->status_code ?? 'Unknown' }} • {{ $check->response_time ?? 0 }}ms</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $check->created_at ? $check->created_at->diffForHumans() : 'Unknown' }}</p>
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
                        <p class="mt-1 text-sm text-gray-500">Run a status check to see results here.</p>
                        <div class="mt-6">
                            <button onclick="checkAllStatus()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Run Status Check
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function checkAllStatus() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('status-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    // Disable all action buttons
    const buttons = document.querySelectorAll('button[onclick]');
    buttons.forEach(button => {
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    });
    
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
            const safeCurrent = Math.min(current, total);
            const percentage = total > 0 ? Math.round((safeCurrent / total) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${safeCurrent}/${total}`;
            progressDetails.textContent = message || 'Processing routes...';
        }
    }
    
    // Simulate progress updates with proper limits
    let progressCounter = 0;
    const maxProgressSteps = 100; // Fixed steps for status check
    
    const progressInterval = setInterval(() => {
        progressCounter++;
        
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
        // Remove the progress modal
        const progressDiv = document.getElementById('status-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        showNotification('Network error occurred', 'error');
    });
}

function checkThresholdAlerts() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('threshold-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    // Disable all action buttons
    const buttons = document.querySelectorAll('button[onclick]');
    buttons.forEach(button => {
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    });
    
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
    const maxThresholdSteps = 25;
    
    const thresholdInterval = setInterval(() => {
        thresholdCounter++;
        
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
        // Remove the progress modal
        const progressDiv = document.getElementById('threshold-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        if (data.success) {
            if (data.total_alerts > 0) {
                showNotification(`⚠️ Found ${data.total_alerts} threshold alerts! Check logs for details.`, 'warning');
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
        // Remove the progress modal
        const progressDiv = document.getElementById('threshold-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
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