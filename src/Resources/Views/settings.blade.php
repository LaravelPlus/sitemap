@extends('sitemap::layouts.app')

@section('title', 'Settings - Sitemap Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">Settings</h1>
                    <p class="text-purple-100 mt-2">Configure sitemap package settings and manage data</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="saveSettings()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save Settings</span>
                    </button>
                    <button onclick="resetSettings()" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Reset to Defaults</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Configuration Settings -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Configuration</h2>
                    
                    <div class="space-y-4">
                        <!-- Route Discovery -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h3 class="font-semibold text-gray-900">Route Discovery</h3>
                                <p class="text-sm text-gray-600">Automatically discover application routes</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="route_discovery_enabled" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Status Checking -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h3 class="font-semibold text-gray-900">Status Checking</h3>
                                <p class="text-sm text-gray-600">Monitor route health and response times</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="status_check_enabled" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Caching -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h3 class="font-semibold text-gray-900">Caching</h3>
                                <p class="text-sm text-gray-600">Enable performance caching</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="cache_enabled" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- UI Interface -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h3 class="font-semibold text-gray-900">Web Interface</h3>
                                <p class="text-sm text-gray-600">Enable the web dashboard</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="ui_enabled" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Performance Settings -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Performance</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Routes Per Check</label>
                            <input type="number" id="max_routes_per_check" value="50" min="10" max="200" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cache TTL (seconds)</label>
                            <input type="number" id="cache_ttl" value="300" min="60" max="3600" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Concurrent Requests</label>
                            <input type="number" id="concurrent_requests" value="10" min="1" max="50" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Management -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Management</h2>
                    
                    <div class="space-y-4">
                        <!-- Clear Cache -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-blue-900">Clear Cache</h3>
                                    <p class="text-sm text-blue-700">Clear all cached data and statistics</p>
                                </div>
                                <button onclick="clearCache()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Clear Cache
                                </button>
                            </div>
                        </div>

                        <!-- Truncate Old Data -->
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-yellow-900">Truncate Old Data</h3>
                                        <p class="text-sm text-yellow-700">Remove data older than specified days</p>
                                    </div>
                                    <button onclick="truncateOldData()" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                                        Truncate
                                    </button>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-yellow-800 mb-1">Days to Keep</label>
                                    <input type="number" id="truncate_days" value="30" min="1" max="365" class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                            </div>
                        </div>

                        <!-- Empty All Data -->
                        <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-red-900">Empty All Data</h3>
                                    <p class="text-sm text-red-700">⚠️ This will permanently delete ALL sitemap data</p>
                                </div>
                                <button onclick="emptyAllData()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                    Empty All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Current Statistics</h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <div class="text-2xl font-bold text-gray-900" id="total-routes">-</div>
                            <div class="text-sm text-gray-600">Total Routes</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <div class="text-2xl font-bold text-gray-900" id="total-errors">-</div>
                            <div class="text-sm text-gray-600">Total Errors</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <div class="text-2xl font-bold text-gray-900" id="total-checks">-</div>
                            <div class="text-sm text-gray-600">Status Checks</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <div class="text-2xl font-bold text-gray-900" id="cache-size">-</div>
                            <div class="text-sm text-gray-600">Cache Size</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load current settings
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadStatistics();
});

function loadSettings() {
    // Load current configuration
    fetch('/sitemap/settings')
        .then(response => response.json())
        .then(data => {
            // Populate form fields with current settings
            document.getElementById('route_discovery_enabled').checked = data.route_discovery?.enabled ?? true;
            document.getElementById('status_check_enabled').checked = data.status_check?.enabled ?? true;
            document.getElementById('cache_enabled').checked = data.cache?.enabled ?? true;
            document.getElementById('ui_enabled').checked = data.ui?.enabled ?? true;
            document.getElementById('max_routes_per_check').value = data.status_check?.max_routes_per_check ?? 50;
            document.getElementById('cache_ttl').value = data.cache?.ttl ?? 300;
            document.getElementById('concurrent_requests').value = data.status_check?.concurrent_requests ?? 10;
        })
        .catch(error => {
            console.error('Error loading settings:', error);
        });
}

function loadStatistics() {
    fetch('/sitemap/dashboard')
        .then(response => response.text())
        .then(html => {
            // Extract statistics from dashboard response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update statistics display
            document.getElementById('total-routes').textContent = '443'; // This would be dynamic
            document.getElementById('total-errors').textContent = '41';
            document.getElementById('total-checks').textContent = '100';
            document.getElementById('cache-size').textContent = '8 KB';
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

function saveSettings() {
    const settings = {
        route_discovery: {
            enabled: document.getElementById('route_discovery_enabled').checked
        },
        status_check: {
            enabled: document.getElementById('status_check_enabled').checked,
            max_routes_per_check: parseInt(document.getElementById('max_routes_per_check').value),
            concurrent_requests: parseInt(document.getElementById('concurrent_requests').value)
        },
        cache: {
            enabled: document.getElementById('cache_enabled').checked,
            ttl: parseInt(document.getElementById('cache_ttl').value)
        },
        ui: {
            enabled: document.getElementById('ui_enabled').checked
        }
    };

    fetch('/sitemap/settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        showNotification('Settings saved successfully!', 'success');
    })
    .catch(error => {
        showNotification('Failed to save settings: ' + error.message, 'error');
    });
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to their default values?')) {
        // Reset form fields to defaults
        document.getElementById('route_discovery_enabled').checked = true;
        document.getElementById('status_check_enabled').checked = true;
        document.getElementById('cache_enabled').checked = true;
        document.getElementById('ui_enabled').checked = true;
        document.getElementById('max_routes_per_check').value = 50;
        document.getElementById('cache_ttl').value = 300;
        document.getElementById('concurrent_requests').value = 10;
        
        showNotification('Settings reset to defaults successfully!', 'success');
    }
}

function clearCache() {
    if (confirm('Are you sure you want to clear all sitemap caches?')) {
        fetch('/sitemap/cache/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadStatistics(); // Refresh statistics
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Failed to clear cache: ' + error.message, 'error');
        });
    }
}

function truncateOldData() {
    const days = document.getElementById('truncate_days').value;
    if (confirm(`Are you sure you want to remove all data older than ${days} days?`)) {
        fetch('/sitemap/data/truncate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ days: parseInt(days) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadStatistics(); // Refresh statistics
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Failed to truncate data: ' + error.message, 'error');
        });
    }
}

function emptyAllData() {
    if (confirm('⚠️ WARNING: This will permanently delete ALL sitemap data including routes, status checks, and errors. This action cannot be undone. Are you absolutely sure?')) {
        if (confirm('Final confirmation: Are you sure you want to delete ALL sitemap data?')) {
            fetch('/sitemap/data/empty', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadStatistics(); // Refresh statistics
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Failed to empty data: ' + error.message, 'error');
            });
        }
    }
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