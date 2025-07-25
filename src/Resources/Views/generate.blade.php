@extends('sitemap::layouts.app')

@section('title', 'Generate Sitemap - Sitemap Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">Generate Sitemap</h1>
                    <p class="text-green-100 mt-2">Create and export sitemaps in multiple formats</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="generateSitemap()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Generate Sitemap</span>
                    </button>
                    <button onclick="discoverRoutes()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Discover Routes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generation Options -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Format Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Export Format</h3>
                    <p class="text-sm text-gray-600 mt-1">Choose the format for your sitemap</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <label class="flex items-center p-4 bg-blue-50 rounded-xl border border-blue-200 cursor-pointer">
                            <input type="radio" name="format" value="xml" checked class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">XML Sitemap</span>
                                </div>
                                <p class="text-sm text-gray-600">Standard XML format for search engines</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <input type="radio" name="format" value="json" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">JSON Sitemap</span>
                                </div>
                                <p class="text-sm text-gray-600">JSON format for API integration</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <input type="radio" name="format" value="csv" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">CSV Sitemap</span>
                                </div>
                                <p class="text-sm text-gray-600">CSV format for spreadsheet analysis</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Generation Options</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure sitemap generation settings</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="includeLastmod" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">Include last modified date</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" id="includeChangefreq" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">Include change frequency</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" id="includePriority" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">Include priority</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" id="onlyHealthy" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">Only include healthy routes</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-50">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Total Routes</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-blue-600">{{ $totalRoutes ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Available for sitemap</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-green-50">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Healthy Routes</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-green-600">{{ $healthyRoutes ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Ready for inclusion</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-yellow-50">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Last Generated</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-yellow-600">{{ $lastGenerated ?? 'Never' }}</p>
                    <p class="text-sm text-gray-600">Previous sitemap</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-purple-50">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">File Size</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-purple-600">{{ $fileSize ?? '0 KB' }}</p>
                    <p class="text-sm text-gray-600">Estimated size</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateSitemap() {
    const format = document.querySelector('input[name="format"]:checked').value;
    const includeLastmod = document.getElementById('includeLastmod').checked;
    const includeChangefreq = document.getElementById('includeChangefreq').checked;
    const includePriority = document.getElementById('includePriority').checked;
    const onlyHealthy = document.getElementById('onlyHealthy').checked;
    
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('generate-progress');
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
    progressDiv.id = 'generate-progress';
    progressDiv.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
    progressDiv.innerHTML = `
        <div class="bg-green-500 text-white px-8 py-6 rounded-2xl shadow-2xl max-w-md mx-4">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mr-3"></div>
                    <h3 class="text-lg font-semibold">Generating Sitemap</h3>
                </div>
                <div class="space-y-3">
                    <div class="text-sm opacity-90">Please wait while we generate your sitemap</div>
                    <div class="bg-green-600 rounded-full h-2 overflow-hidden">
                        <div id="generate-progress-bar" class="bg-white h-2 transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <div id="generate-progress-text" class="text-sm font-medium">Initializing...</div>
                    <div id="generate-progress-details" class="text-xs opacity-75">Preparing sitemap generation</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(progressDiv);
    
    // Update progress function
    function updateGenerateProgress(current, total, message, details) {
        const progressBar = document.getElementById('generate-progress-bar');
        const progressText = document.getElementById('generate-progress-text');
        const progressDetails = document.getElementById('generate-progress-details');
        
        if (progressBar && progressText && progressDetails) {
            const safeCurrent = Math.min(current, total);
            const percentage = total > 0 ? Math.round((safeCurrent / total) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${safeCurrent}/${total}`;
            progressDetails.textContent = message || 'Generating sitemap...';
        }
    }
    
    // Simulate progress updates with proper limits
    let progressCounter = 0;
    const maxProgressSteps = 30;
    
    const progressInterval = setInterval(() => {
        progressCounter++;
        
        if (progressCounter > maxProgressSteps) {
            clearInterval(progressInterval);
            return;
        }
        
        const messages = [
            'Collecting routes...',
            'Filtering routes...',
            'Processing metadata...',
            'Generating content...',
            'Formatting output...',
            'Finalizing sitemap...'
        ];
        const currentMessage = messages[Math.min(Math.floor(progressCounter / (maxProgressSteps / messages.length)), messages.length - 1)];
        updateGenerateProgress(progressCounter, maxProgressSteps, currentMessage, `Step ${progressCounter} of ${maxProgressSteps}`);
    }, 150);
    
    fetch('{{ route("sitemap.api.generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            format: format,
            include_lastmod: includeLastmod,
            include_changefreq: includeChangefreq,
            include_priority: includePriority,
            only_healthy: onlyHealthy,
        }),
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
        const progressDiv = document.getElementById('generate-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        if (data.success) {
            showNotification(`Sitemap generated successfully! ${data.routes_count || 0} routes included.`, 'success');
            
            // Download the file if URL is provided
            if (data.download_url) {
                const link = document.createElement('a');
                link.href = data.download_url;
                link.download = `sitemap.${format}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
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
        const progressDiv = document.getElementById('generate-progress');
        if (progressDiv) {
            progressDiv.remove();
        }
        
        showNotification('Network error occurred', 'error');
    });
}

function discoverRoutes() {
    // Remove any existing progress modals first
    const existingProgress = document.getElementById('discovery-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    // Disable all action buttons
    const buttons = document.querySelectorAll('button[onclick]');
    buttons.forEach(button => {
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    });
    
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
    const maxDiscoverySteps = 50;
    
    const discoveryInterval = setInterval(() => {
        discoveryCounter++;
        
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
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
        
        // Re-enable all action buttons
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
        // Remove the progress modal
        const progressDiv = document.getElementById('discovery-progress');
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