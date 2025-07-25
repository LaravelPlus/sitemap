@extends('sitemap::layouts.app')

@section('title', 'Route Errors - Sitemap Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div>
                    <h1 class="text-3xl lg:text-4xl font-bold">Route Errors</h1>
                    <p class="text-red-100 mt-2">Monitor and analyze route errors and issues</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <button onclick="checkAllStatus()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-4 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center justify-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Check All Status</span>
                    </button>
                    <button onclick="clearErrors()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-4 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center justify-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>Clear Errors</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 lg:p-3 rounded-xl bg-red-50">
                        <svg class="h-5 w-5 lg:h-6 lg:w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs lg:text-sm font-medium text-gray-500">Total Errors</span>
                </div>
                <div class="space-y-1">
                    <p class="text-2xl lg:text-3xl font-bold text-red-600">{{ $totalErrors ?? 0 }}</p>
                    <p class="text-xs lg:text-sm text-gray-600">All time errors</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 lg:p-3 rounded-xl bg-orange-50">
                        <svg class="h-5 w-5 lg:h-6 lg:w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <span class="text-xs lg:text-sm font-medium text-gray-500">Recent Errors</span>
                </div>
                <div class="space-y-1">
                    <p class="text-2xl lg:text-3xl font-bold text-orange-600">{{ $recentErrors ?? 0 }}</p>
                    <p class="text-xs lg:text-sm text-gray-600">Last 24 hours</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 lg:p-3 rounded-xl bg-yellow-50">
                        <svg class="h-5 w-5 lg:h-6 lg:w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs lg:text-sm font-medium text-gray-500">Error Rate</span>
                </div>
                <div class="space-y-1">
                    <p class="text-2xl lg:text-3xl font-bold text-yellow-600">{{ $errorRate ?? 0 }}%</p>
                    <p class="text-xs lg:text-sm text-gray-600">Percentage of errors</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 lg:p-3 rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 lg:h-6 lg:w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="text-xs lg:text-sm font-medium text-gray-500">Affected Routes</span>
                </div>
                <div class="space-y-1">
                    <p class="text-2xl lg:text-3xl font-bold text-blue-600">{{ $affectedRoutes ?? 0 }}</p>
                    <p class="text-xs lg:text-sm text-gray-600">Routes with errors</p>
                </div>
            </div>
        </div>

        <!-- Error Types and Recent Errors -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-900">Error Types</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">Breakdown of error categories</p>
                </div>
                <div class="p-4 lg:p-6">
                    @if(isset($errorTypes) && count($errorTypes) > 0)
                        <div class="space-y-3 lg:space-y-4">
                            @foreach($errorTypes as $type => $count)
                            <div class="flex items-center justify-between p-3 lg:p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center space-x-3 min-w-0 flex-1">
                                    <div class="w-2 h-2 lg:w-3 lg:h-3 bg-red-500 rounded-full flex-shrink-0"></div>
                                    <span class="font-medium text-gray-900 text-sm lg:text-base truncate">{{ ucfirst($type) }}</span>
                                </div>
                                <span class="text-xs lg:text-sm font-medium text-gray-600 ml-2">{{ $count }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 lg:py-8">
                            <div class="mx-auto h-10 w-10 lg:h-12 lg:w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No error types</h3>
                            <p class="mt-1 text-xs lg:text-sm text-gray-500">No errors have been recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-900">Recent Errors</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">Latest error occurrences</p>
                </div>
                <div class="p-4 lg:p-6">
                    @if(isset($recentErrors) && $recentErrors->count() > 0)
                        <div class="space-y-3 lg:space-y-4">
                            @foreach($recentErrors as $error)
                            <div class="p-3 lg:p-4 bg-red-50 rounded-xl border border-red-200">
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-red-900 text-sm lg:text-base break-words">
                                                {{ $error->route->uri ?? 'Unknown Route' }}
                                            </p>
                                        </div>
                                        <div class="text-right ml-2 flex-shrink-0">
                                            <p class="text-xs text-red-600">
                                                {{ $error->occurred_at ? $error->occurred_at->diffForHumans() : 'Unknown' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="relative">
                                            <p class="text-xs lg:text-sm text-red-700 break-words line-clamp-2 cursor-pointer" 
                                               onclick="toggleErrorDetails(this, '{{ $error->id }}')"
                                               title="Click to expand/collapse">
                                                {{ $error->error_message ?? 'Unknown error' }}
                                            </p>
                                            <div id="error-details-{{ $error->id }}" class="hidden mt-2 p-2 bg-red-100 rounded text-xs text-red-800 break-words">
                                                <strong>Full Error:</strong><br>
                                                {{ $error->error_message ?? 'Unknown error' }}
                                                @if($error->stack_trace)
                                                    <br><br><strong>Stack Trace:</strong><br>
                                                    <div class="font-mono text-xs max-h-32 overflow-y-auto">
                                                        {{ $error->stack_trace }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-red-600">
                                            {{ $error->error_type ?? 'Unknown type' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 lg:py-8">
                            <div class="mx-auto h-10 w-10 lg:h-12 lg:w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No recent errors</h3>
                            <p class="mt-1 text-xs lg:text-sm text-gray-500">No errors have been recorded recently.</p>
                        </div>
                    @endif
                </div>
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
        <div class="bg-blue-500 text-white px-6 py-4 lg:px-8 lg:py-6 rounded-2xl shadow-2xl max-w-sm lg:max-w-md mx-4">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-6 w-6 lg:h-8 lg:w-8 border-b-2 border-white mr-3"></div>
                    <h3 class="text-base lg:text-lg font-semibold">Processing Status Check</h3>
                </div>
                <div class="space-y-3">
                    <div class="text-xs lg:text-sm opacity-90">Please wait while we process your request</div>
                    <div class="bg-blue-600 rounded-full h-2 overflow-hidden">
                        <div id="progress-bar" class="bg-white h-2 transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <div id="progress-text" class="text-xs lg:text-sm font-medium">Initializing...</div>
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
    const maxProgressSteps = 100;
    
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

function clearErrors() {
    if (confirm('Are you sure you want to clear all error records? This action cannot be undone.')) {
        fetch('{{ route("sitemap.api.cleanup") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Error records cleared successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Network error occurred', 'error');
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 lg:px-6 lg:py-4 rounded-xl shadow-2xl text-white font-medium transition-all duration-300 transform translate-x-full text-sm lg:text-base max-w-sm lg:max-w-md`;
    
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

function toggleErrorDetails(element, errorId) {
    const detailsDiv = document.getElementById(`error-details-${errorId}`);
    if (detailsDiv) {
        const isHidden = detailsDiv.classList.contains('hidden');
        detailsDiv.classList.toggle('hidden');
        
        // Update the cursor and title
        if (isHidden) {
            element.classList.add('text-red-800', 'font-medium');
            element.title = 'Click to collapse';
        } else {
            element.classList.remove('text-red-800', 'font-medium');
            element.title = 'Click to expand';
        }
    }
}
</script>
@endsection 