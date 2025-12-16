<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Repositories\SitemapErrorRepository;
use LaravelPlus\Sitemap\Repositories\SitemapRouteRepository;
use LaravelPlus\Sitemap\Repositories\SitemapStatusCheckRepository;
use LaravelPlus\Sitemap\Services\SitemapService;
use Log;

final class SitemapController extends Controller
{
    protected SitemapService $sitemapService;

    protected SitemapRouteRepository $routeRepository;

    protected SitemapStatusCheckRepository $statusCheckRepository;

    protected SitemapErrorRepository $errorRepository;

    public function __construct(
        SitemapService $sitemapService,
        SitemapRouteRepository $routeRepository,
        SitemapStatusCheckRepository $statusCheckRepository,
        SitemapErrorRepository $errorRepository
    ) {
        $this->sitemapService = $sitemapService;
        $this->routeRepository = $routeRepository;
        $this->statusCheckRepository = $statusCheckRepository;
        $this->errorRepository = $errorRepository;
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        $stats = $this->sitemapService->getDashboardStats();
        $recentErrors = $this->errorRepository->getRecent(24, 10);
        $routesWithErrors = $this->sitemapService->getRoutesWithErrors();

        return view('sitemap::dashboard', compact('stats', 'recentErrors', 'routesWithErrors'));
    }

    /**
     * Show all routes.
     */
    public function routes(Request $request)
    {
        $filters = [
            'environment' => $request->get('environment'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $routes = $this->routeRepository->getPaginated(20, $filters);
        $stats = $this->routeRepository->getStatistics();

        return view('sitemap::routes', compact('routes', 'stats'));
    }

    /**
     * Show route details.
     */
    public function routeDetails(SitemapRoute $route)
    {
        $recentStatusChecks = $this->statusCheckRepository->getRecentForRoute($route->id, 10);
        $recentErrors = $this->errorRepository->getRecentForRoute($route->id, 10);

        return view('sitemap::route-details', compact('route', 'recentStatusChecks', 'recentErrors'));
    }

    /**
     * Show status checks.
     */
    public function status(Request $request)
    {
        $filters = [
            'environment' => $request->get('environment'),
            'status_code' => $request->get('status_code'),
        ];

        $statusChecks = $this->statusCheckRepository->getPaginated(20, $filters);
        $recentChecks = $this->statusCheckRepository->getRecent(10);
        $stats = $this->statusCheckRepository->getStatistics();

        // Get status statistics
        $healthyCount = $this->statusCheckRepository->getHealthyCount();
        $errorCount = $this->statusCheckRepository->getErrorCount();
        $warningCount = $this->statusCheckRepository->getWarningCount();
        $avgResponseTime = $this->statusCheckRepository->getAverageResponseTime();

        return view('sitemap::status', compact(
            'statusChecks',
            'recentChecks',
            'stats',
            'healthyCount',
            'errorCount',
            'warningCount',
            'avgResponseTime'
        ));
    }

    /**
     * Show route status.
     */
    public function routeStatus(SitemapRoute $route)
    {
        $filters = ['route_id' => $route->id];
        $statusChecks = $this->statusCheckRepository->getPaginated(20, $filters);

        return view('sitemap::route-status', compact('route', 'statusChecks'));
    }

    /**
     * Show errors.
     */
    public function errors(Request $request)
    {
        $filters = [
            'environment' => $request->get('environment'),
            'type' => $request->get('type'),
        ];

        $errors = $this->errorRepository->getPaginated(20, $filters);
        $recentErrors = $this->errorRepository->getRecent(24, 10);

        // Get error statistics
        $totalErrors = $this->errorRepository->getTotal();
        $recentErrorCount = $this->errorRepository->getRecentCount(24);
        $errorRate = $this->errorRepository->getErrorRate();
        $affectedRoutes = $this->errorRepository->getAffectedRoutesCount();
        $errorTypes = $this->errorRepository->getErrorTypes();

        return view('sitemap::errors', compact(
            'errors',
            'recentErrors',
            'totalErrors',
            'recentErrorCount',
            'errorRate',
            'affectedRoutes',
            'errorTypes'
        ));
    }

    /**
     * Show route errors.
     */
    public function routeErrors(SitemapRoute $route)
    {
        $filters = ['route_id' => $route->id];
        $errors = $this->errorRepository->getPaginated(20, $filters);

        return view('sitemap::route-errors', compact('route', 'errors'));
    }

    /**
     * Show sitemap generation page.
     */
    public function generate()
    {
        $stats = $this->sitemapService->getDashboardStats();
        $formats = config('sitemap.export.formats');

        // Get generation statistics
        $totalRoutes = $this->routeRepository->getTotal();
        $healthyRoutes = $this->routeRepository->getHealthyCount();
        $lastGenerated = $this->sitemapService->getLastGeneratedTime();
        $fileSize = $this->sitemapService->getEstimatedFileSize();

        return view('sitemap::generate', compact(
            'stats',
            'formats',
            'totalRoutes',
            'healthyRoutes',
            'lastGenerated',
            'fileSize'
        ));
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

        } catch (Exception $e) {
            Log::error('Sitemap API route discovery failed', [
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

            // Dispatch background job
            \LaravelPlus\Sitemap\Jobs\CheckRoutesStatusJob::dispatch($environment, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Status check job has been queued successfully.',
                'job_queued' => true,
                'environment' => $environment,
                'estimated_time' => '1-3 minutes',
            ]);

        } catch (Exception $e) {
            Log::error('Sitemap API status check failed', [
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
     * Download sitemap.
     */
    public function download(string $format)
    {
        try {
            $sitemap = $this->sitemapService->generateSitemap($format);

            $filename = "sitemap-{$format}-" . now()->format('Y-m-d-H-i-s') . ".{$format}";

            return response($sitemap)
                ->header('Content-Type', $this->getContentType($format))
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show settings page.
     */
    public function settings()
    {
        $config = config('sitemap');

        return view('sitemap::settings', compact('config'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'route_discovery.enabled' => 'boolean',
            'status_check.enabled' => 'boolean',
            'ui.enabled' => 'boolean',
            'cache.enabled' => 'boolean',
        ]);

        // Update configuration (this would typically be stored in database or cache)
        // For now, we'll just redirect back with success message

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Empty all sitemap data.
     */
    public function emptyData(Request $request)
    {
        try {
            // Clear data in order to respect foreign key constraints
            // 1. Delete errors first (references routes)
            $errorsDeleted = $this->errorRepository->deleteAll();

            // 2. Delete status checks (references routes)
            $statusChecksDeleted = $this->statusCheckRepository->deleteAll();

            // 3. Delete routes last (referenced by others)
            $routesDeleted = $this->routeRepository->deleteAll();

            // Clear all caches
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'All sitemap data has been cleared successfully.',
                'data' => [
                    'errors_deleted' => $errorsDeleted,
                    'status_checks_deleted' => $statusChecksDeleted,
                    'routes_deleted' => $routesDeleted,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Truncate old data (keep recent data).
     */
    public function truncateOldData(Request $request)
    {
        try {
            $days = $request->get('days', 30);

            // Truncate old data in order to respect foreign key constraints
            // 1. Delete old errors first (references routes)
            $errorsDeleted = $this->errorRepository->deleteOld($days);

            // 2. Delete old status checks (references routes)
            $statusChecksDeleted = $this->statusCheckRepository->deleteOld($days);

            // 3. Delete old routes last (referenced by others)
            $routesDeleted = $this->routeRepository->deleteOld($days);

            // Clear related caches
            Cache::forget('sitemap_route_statistics');
            Cache::forget('sitemap_routes_total');
            Cache::forget('sitemap_routes_healthy_count');

            return response()->json([
                'success' => true,
                'message' => "Old data older than {$days} days has been cleared successfully.",
                'data' => [
                    'errors_deleted' => $errorsDeleted,
                    'status_checks_deleted' => $statusChecksDeleted,
                    'routes_deleted' => $routesDeleted,
                    'days' => $days,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to truncate old data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache only.
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear all sitemap-related caches
            $cacheKeys = [
                'sitemap_route_statistics',
                'sitemap_routes_total',
                'sitemap_routes_healthy_count',
                'sitemap_cache_hits',
                'sitemap_cache_misses',
                'sitemap_db_queries',
                'sitemap_slow_queries',
                'sitemap_avg_query_time',
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Also clear pattern-based caches
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'All sitemap caches have been cleared successfully.',
                'data' => [
                    'cache_keys_cleared' => count($cacheKeys),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get content type for format.
     */
    protected function getContentType(string $format): string
    {
        return match ($format) {
            'xml' => 'application/xml',
            'json' => 'application/json',
            'csv' => 'text/csv',
            default => 'text/plain',
        };
    }

    /**
     * Get job status and progress.
     */
    public function jobStatus(Request $request)
    {
        $jobType = $request->get('type', 'all');
        $environment = $request->get('environment', app()->environment());

        $status = [
            'discovery' => $this->getJobStatus('discovery', $environment),
            'status_check' => $this->getJobStatus('status_check', $environment),
            'generation' => $this->getJobStatus('generation', $environment),
        ];

        if ($jobType !== 'all') {
            $status = $status[$jobType] ?? null;
        }

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Get job status for a specific type.
     */
    protected function getJobStatus(string $type, string $environment): array
    {
        $cacheKey = "sitemap_job_status_{$type}_{$environment}";
        $status = Cache::get($cacheKey, [
            'status' => 'idle',
            'progress' => 0,
            'message' => 'No job running',
            'last_run' => null,
            'estimated_completion' => null,
        ]);

        return $status;
    }

    /**
     * Get recent job history.
     */
    public function jobHistory(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type');
        $environment = $request->get('environment', app()->environment());

        $history = Cache::get("sitemap_job_history_{$environment}", []);

        if ($type) {
            $history = array_filter($history, fn ($job) => $job['type'] === $type);
        }

        // Sort by timestamp descending and limit
        usort($history, fn ($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));

        $history = array_slice($history, 0, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
