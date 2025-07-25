<?php

namespace LaravelPlus\Sitemap\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Http\Requests\UpdatePriorityRequest;
use LaravelPlus\Sitemap\Http\Requests\UpdateChangeFreqRequest;
use LaravelPlus\Sitemap\Services\ThresholdService;

class SitemapApiController extends Controller
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        $stats = $this->sitemapService->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get routes with pagination and filtering.
     */
    public function routes(Request $request)
    {
        $query = SitemapRoute::query();
        
        if ($request->has('environment')) {
            $query->forEnvironment($request->environment);
        }
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'errors':
                    $query->withErrors();
                    break;
                case 'healthy':
                    $query->get()->filter->isHealthy();
                    break;
            }
        }

        if ($request->has('search')) {
            $query->where('uri', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
        }

        $routes = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $routes,
        ]);
    }

    /**
     * Get specific route details.
     */
    public function route(SitemapRoute $route)
    {
        $route->load(['statusChecks' => function ($query) {
            $query->orderBy('checked_at', 'desc')->limit(10);
        }, 'errors' => function ($query) {
            $query->orderBy('occurred_at', 'desc')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $route,
        ]);
    }

    /**
     * Update route priority.
     */
    public function updatePriority(UpdatePriorityRequest $request, SitemapRoute $route)
    {
        $success = $this->sitemapService->updateRoutePriority($route->id, $request->priority);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Route priority updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update route priority',
        ], 400);
    }

    /**
     * Update route change frequency.
     */
    public function updateChangeFreq(UpdateChangeFreqRequest $request, SitemapRoute $route)
    {
        $success = $this->sitemapService->updateRouteChangeFreq($route->id, $request->changefreq);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Route change frequency updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update route change frequency',
        ], 400);
    }

    /**
     * Toggle route active status.
     */
    public function toggleRoute(SitemapRoute $route)
    {
        $success = $this->sitemapService->toggleRouteStatus($route->id);
        
        if ($success) {
            $route->refresh();
            return response()->json([
                'success' => true,
                'message' => 'Route status toggled successfully',
                'data' => [
                    'is_active' => $route->is_active,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to toggle route status',
        ], 400);
    }

    /**
     * Discover routes via API.
     */
    public function discover(Request $request)
    {
        try {
            $environment = $request->get('environment', app()->environment());
            $userId = $request->user()?->id;

            // Dispatch background job
            \LaravelPlus\Sitemap\Jobs\DiscoverRoutesJob::dispatch($environment, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Route discovery job has been queued successfully.',
                'job_queued' => true,
                'environment' => $environment,
                'estimated_time' => '2-5 minutes',
            ]);

        } catch (\Exception $e) {
            \Log::error('Sitemap API route discovery failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue route discovery job: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status of all routes via API.
     */
    public function checkStatus(Request $request)
    {
        try {
            $environment = $request->get('environment', app()->environment());
            $userId = $request->user()?->id;
            $batchSize = $request->get('batch_size', 50);
            $checkAll = $request->get('check_all', false);

            // Dispatch background job
            \LaravelPlus\Sitemap\Jobs\CheckRoutesStatusJob::dispatch($environment, $userId, $batchSize, $checkAll);

            return response()->json([
                'success' => true,
                'message' => 'Status check job has been queued successfully.',
                'job_queued' => true,
                'environment' => $environment,
                'batch_size' => $batchSize,
                'check_all' => $checkAll,
                'estimated_time' => '1-3 minutes',
            ]);

        } catch (\Exception $e) {
            \Log::error('Sitemap API status check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue status check job: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status of a specific route.
     */
    public function checkRouteStatus(Request $request)
    {
        $request->validate([
            'route_id' => 'required|integer|exists:sitemap_routes,id',
        ]);

        $route = SitemapRoute::find($request->route_id);
        
        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        try {
            $result = $this->sitemapService->getStatusCheck()->checkRoutes(collect([$route]));
            
            return response()->json([
                'success' => true,
                'message' => 'Route status checked successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check route status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test a specific route.
     */
    public function testRoute(Request $request)
    {
        $request->validate([
            'uri' => 'required|string',
        ]);

        try {
            $result = $this->sitemapService->getStatusCheck()->testRoute($request->uri);
            
            return response()->json([
                'success' => true,
                'message' => 'Route test completed',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test route: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate sitemap via API.
     */
    public function generateSitemap(Request $request)
    {
        try {
            $format = $request->get('format', 'xml');
            $environment = $request->get('environment', app()->environment());
            $userId = $request->user()?->id;
            $saveToDisk = $request->get('save_to_disk', true);

            // Validate format
            $validFormats = ['xml', 'json', 'csv'];
            if (!in_array($format, $validFormats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid format. Allowed formats: ' . implode(', ', $validFormats),
                ], 400);
            }

            // Dispatch background job
            \LaravelPlus\Sitemap\Jobs\GenerateSitemapJob::dispatch($format, $environment, $userId, $saveToDisk);

            return response()->json([
                'success' => true,
                'message' => 'Sitemap generation job has been queued successfully.',
                'job_queued' => true,
                'format' => $format,
                'environment' => $environment,
                'save_to_disk' => $saveToDisk,
                'estimated_time' => '30 seconds - 2 minutes',
            ]);

        } catch (\Exception $e) {
            \Log::error('Sitemap API generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue sitemap generation job: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cleanup old data.
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30);
        
        $result = $this->sitemapService->cleanupOldData($days);
        
        return response()->json([
            'success' => true,
            'message' => 'Cleanup completed successfully',
            'data' => $result,
        ]);
    }

    /**
     * Get threshold alerts.
     */
    public function thresholdAlerts(Request $request)
    {
        try {
            $routes = SitemapRoute::active()->get();
            
            if ($routes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No routes found for threshold monitoring.',
                ], 404);
            }

            $thresholdService = app(ThresholdService::class);
            $alerts = $thresholdService->checkBulkThresholds($routes);
            
            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'total_alerts' => count($alerts),
                'routes_monitored' => $routes->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Sitemap API thresholdAlerts error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save settings.
     */
    public function saveSettings(Request $request)
    {
        try {
            $settings = $request->validate([
                'environment' => 'string|in:production,development,testing',
                'exclude_patterns' => 'array',
                'include_hidden' => 'boolean',
                'include_api' => 'boolean',
                'timeout' => 'integer|min:1|max:60',
                'concurrent_requests' => 'integer|min:1|max:20',
                'max_routes_per_check' => 'integer|min:10|max:500',
                'bulk_check_enabled' => 'boolean',
                'thresholds_enabled' => 'boolean',
                'warning_threshold' => 'integer|min:100|max:10000',
                'critical_threshold' => 'integer|min:500|max:15000',
                'alert_threshold' => 'integer|min:1000|max:30000',
                'log_notifications' => 'boolean',
                'email_notifications' => 'boolean',
                'slack_notifications' => 'boolean',
                'webhook_notifications' => 'boolean',
                'notification_recipients' => 'array',
            ]);

            // Update configuration
            config([
                'sitemap.route_discovery.exclude_patterns' => $settings['exclude_patterns'] ?? [],
                'sitemap.route_discovery.include_hidden' => $settings['include_hidden'] ?? false,
                'sitemap.route_discovery.include_api' => $settings['include_api'] ?? false,
                'sitemap.status_check.timeout' => $settings['timeout'] ?? 10,
                'sitemap.status_check.concurrent_requests' => $settings['concurrent_requests'] ?? 3,
                'sitemap.status_check.max_routes_per_check' => $settings['max_routes_per_check'] ?? 50,
                'sitemap.status_check.bulk_check_enabled' => $settings['bulk_check_enabled'] ?? false,
                'sitemap.thresholds.enabled' => $settings['thresholds_enabled'] ?? true,
                'sitemap.thresholds.response_time.warning' => $settings['warning_threshold'] ?? 1000,
                'sitemap.thresholds.response_time.critical' => $settings['critical_threshold'] ?? 2000,
                'sitemap.thresholds.response_time.alert' => $settings['alert_threshold'] ?? 5000,
                'sitemap.thresholds.notifications.channels.log' => $settings['log_notifications'] ?? true,
                'sitemap.thresholds.notifications.channels.email' => $settings['email_notifications'] ?? false,
                'sitemap.thresholds.notifications.channels.slack' => $settings['slack_notifications'] ?? false,
                'sitemap.thresholds.notifications.channels.webhook' => $settings['webhook_notifications'] ?? false,
                'sitemap.thresholds.notifications.recipients' => $settings['notification_recipients'] ?? [],
            ]);

            // Clear config cache
            \Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Sitemap API saveSettings error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to defaults.
     */
    public function resetSettings(Request $request)
    {
        try {
            // Reset to default configuration
            config([
                'sitemap.route_discovery.exclude_patterns' => [
                    '_ignition/*',
                    'admin/*',
                    'api/*',
                    'telescope/*',
                    'horizon/*',
                    'debugbar/*',
                    'sanctum/*',
                    'broadcasting/*',
                    'oauth/*',
                    'passport/*',
                ],
                'sitemap.route_discovery.include_hidden' => false,
                'sitemap.route_discovery.include_api' => false,
                'sitemap.status_check.timeout' => 10,
                'sitemap.status_check.concurrent_requests' => 3,
                'sitemap.status_check.max_routes_per_check' => 50,
                'sitemap.status_check.bulk_check_enabled' => false,
                'sitemap.thresholds.enabled' => true,
                'sitemap.thresholds.response_time.warning' => 1000,
                'sitemap.thresholds.response_time.critical' => 2000,
                'sitemap.thresholds.response_time.alert' => 5000,
                'sitemap.thresholds.notifications.channels.log' => true,
                'sitemap.thresholds.notifications.channels.email' => false,
                'sitemap.thresholds.notifications.channels.slack' => false,
                'sitemap.thresholds.notifications.channels.webhook' => false,
                'sitemap.thresholds.notifications.recipients' => [],
            ]);

            // Clear config cache
            \Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Sitemap API resetSettings error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
} 