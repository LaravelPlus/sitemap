<?php

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Collection;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Repositories\SitemapRouteRepository;
use LaravelPlus\Sitemap\Repositories\SitemapStatusCheckRepository;
use LaravelPlus\Sitemap\Repositories\SitemapErrorRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected RouteDiscoveryService $routeDiscovery;
    protected StatusCheckService $statusCheck;
    protected SitemapRouteRepository $routeRepository;
    protected SitemapStatusCheckRepository $statusCheckRepository;
    protected SitemapErrorRepository $errorRepository;

    public function __construct(
        RouteDiscoveryService $routeDiscovery,
        StatusCheckService $statusCheck,
        SitemapRouteRepository $routeRepository,
        SitemapStatusCheckRepository $statusCheckRepository,
        SitemapErrorRepository $errorRepository
    ) {
        $this->routeDiscovery = $routeDiscovery;
        $this->statusCheck = $statusCheck;
        $this->routeRepository = $routeRepository;
        $this->statusCheckRepository = $statusCheckRepository;
        $this->errorRepository = $errorRepository;
    }

    /**
     * Get route discovery service.
     */
    public function getRouteDiscovery(): RouteDiscoveryService
    {
        return $this->routeDiscovery;
    }

    /**
     * Get status check service.
     */
    public function getStatusCheck(): StatusCheckService
    {
        return $this->statusCheck;
    }

    /**
     * Discover and store routes for the current environment.
     */
    public function discoverAndStoreRoutes(): array
    {
        $environment = app()->environment();
        $config = config('sitemap.environments.' . $environment, [
            'enabled' => true,
            'cache_duration' => 3600,
            'check_frequency' => 3600,
            'notify_on_error' => false,
        ]);

        if (!isset($config['enabled']) || !$config['enabled']) {
            return [
                'success' => false,
                'message' => "Sitemap discovery is disabled for environment: {$environment}",
                'environment' => $environment,
            ];
        }

        try {
            $routes = $this->routeDiscovery->discoverRoutes();
            $stored = $this->routeRepository->storeFromDiscovery($routes);

            return [
                'success' => true,
                'routes_discovered' => $routes->count(),
                'routes_stored' => $stored,
                'environment' => $environment,
            ];
        } catch (\Exception $e) {
            Log::error('Sitemap route discovery failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'environment' => $environment,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'environment' => $environment,
            ];
        }
    }

    /**
     * Check status of all routes for the current environment.
     */
    public function checkAllRoutesStatus(): array
    {
        $environment = app()->environment();
        $config = config('sitemap.environments.' . $environment, [
            'enabled' => true,
            'cache_duration' => 3600,
            'check_frequency' => 3600,
            'notify_on_error' => false,
        ]);

        if (!isset($config['enabled']) || !$config['enabled']) {
            return [
                'success' => false,
                'message' => "Sitemap status checking is disabled for environment: {$environment}",
            ];
        }

        try {
            $results = $this->statusCheck->checkAllRoutes($environment);

            // Cache results for the configured duration
            $cacheKey = "sitemap_status_{$environment}";
            Cache::put($cacheKey, $results, $config['cache_duration']);

            return [
                'success' => true,
                'results' => $results,
                'environment' => $environment,
            ];
        } catch (\Exception $e) {
            Log::error('Sitemap status check failed', [
                'error' => $e->getMessage(),
                'environment' => $environment,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'environment' => $environment,
            ];
        }
    }

    /**
     * Generate sitemap XML for the current environment.
     */
    public function generateSitemap(string $format = 'xml'): string
    {
        $environment = app()->environment();
        $routes = $this->routeRepository->getForEnvironment($environment);
        
        return match($format) {
            'xml' => $this->generateXmlSitemap($routes),
            'json' => $this->generateJsonSitemap($routes),
            'csv' => $this->generateCsvSitemap($routes),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    /**
     * Generate XML sitemap.
     */
    protected function generateXmlSitemap(Collection $routes): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($routes as $route) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($route->full_url) . '</loc>' . "\n";
            
            if (config('sitemap.export.include_lastmod') && $route->last_checked_at) {
                $xml .= '    <lastmod>' . $route->last_checked_at->toISOString() . '</lastmod>' . "\n";
            }
            
            if (config('sitemap.export.include_changefreq')) {
                $xml .= '    <changefreq>' . $route->changefreq . '</changefreq>' . "\n";
            }
            
            if (config('sitemap.export.include_priority')) {
                $xml .= '    <priority>' . $route->priority . '</priority>' . "\n";
            }
            
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate JSON sitemap.
     */
    protected function generateJsonSitemap(Collection $routes): string
    {
        $data = [
            'sitemap' => [
                'generated_at' => now()->toISOString(),
                'environment' => app()->environment(),
                'total_urls' => $routes->count(),
                'urls' => $routes->map(function ($route) {
                    return [
                        'url' => $route->full_url,
                        'uri' => $route->uri,
                        'name' => $route->name,
                        'priority' => $route->priority,
                        'changefreq' => $route->changefreq,
                        'last_checked' => $route->last_checked_at?->toISOString(),
                        'status_code' => $route->last_status_code,
                        'is_healthy' => $route->isHealthy(),
                    ];
                })->toArray(),
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Generate CSV sitemap.
     */
    protected function generateCsvSitemap(Collection $routes): string
    {
        $csv = "URL,URI,Name,Priority,ChangeFreq,LastChecked,StatusCode,IsHealthy\n";

        foreach ($routes as $route) {
            $csv .= sprintf(
                '"%s","%s","%s",%s,%s,"%s",%s,%s' . "\n",
                $route->full_url,
                $route->uri,
                $route->name ?? '',
                $route->priority,
                $route->changefreq,
                $route->last_checked_at?->toISOString() ?? '',
                $route->last_status_code ?? '',
                $route->isHealthy() ? 'true' : 'false'
            );
        }

        return $csv;
    }

    /**
     * Get dashboard statistics with performance monitoring.
     */
    public function getDashboardStats(): array
    {
        $startTime = microtime(true);
        $environment = app()->environment();
        
        $routeStats = $this->routeRepository->getStatistics($environment);
        $statusStats = $this->statusCheckRepository->getStatistics($environment);
        
        $recentErrors = $this->errorRepository->getRecent(24, 10);

        $executionTime = microtime(true) - $startTime;
        
        // Log performance metrics
        Log::info('Sitemap dashboard stats generated', [
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'environment' => $environment,
            'routes_total' => $routeStats['total'] ?? 0,
        ]);

        return [
            'environment' => $environment,
            'routes' => $routeStats,
            'status_checks' => $statusStats,
            'recent_errors' => $recentErrors,
            'last_updated' => Cache::get("sitemap_status_{$environment}_updated_at"),
            'performance' => [
                'execution_time_ms' => round($executionTime * 1000, 2),
                'cache_hits' => Cache::get('sitemap_cache_hits', 0),
                'cache_misses' => Cache::get('sitemap_cache_misses', 0),
            ],
        ];
    }

    /**
     * Get performance metrics for monitoring.
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'cache' => [
                'hits' => Cache::get('sitemap_cache_hits', 0),
                'misses' => Cache::get('sitemap_cache_misses', 0),
                'hit_rate' => $this->calculateCacheHitRate(),
            ],
            'database' => [
                'total_queries' => Cache::get('sitemap_db_queries', 0),
                'slow_queries' => Cache::get('sitemap_slow_queries', 0),
                'avg_query_time' => Cache::get('sitemap_avg_query_time', 0),
            ],
            'routes' => [
                'discovery_time' => Cache::get('sitemap_discovery_time', 0),
                'status_check_time' => Cache::get('sitemap_status_check_time', 0),
                'last_optimization' => Cache::get('sitemap_last_optimization', now()->toISOString()),
            ],
        ];
    }

    /**
     * Calculate cache hit rate.
     */
    protected function calculateCacheHitRate(): float
    {
        $hits = Cache::get('sitemap_cache_hits', 0);
        $misses = Cache::get('sitemap_cache_misses', 0);
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Record cache hit.
     */
    public function recordCacheHit(): void
    {
        Cache::increment('sitemap_cache_hits');
    }

    /**
     * Record cache miss.
     */
    public function recordCacheMiss(): void
    {
        Cache::increment('sitemap_cache_misses');
    }

    /**
     * Record database query performance.
     */
    public function recordQueryPerformance(float $executionTime): void
    {
        Cache::increment('sitemap_db_queries');
        
        if ($executionTime > 100) { // Queries taking more than 100ms
            Cache::increment('sitemap_slow_queries');
        }
        
        // Update average query time
        $currentAvg = Cache::get('sitemap_avg_query_time', 0);
        $totalQueries = Cache::get('sitemap_db_queries', 1);
        $newAvg = (($currentAvg * ($totalQueries - 1)) + $executionTime) / $totalQueries;
        Cache::put('sitemap_avg_query_time', $newAvg, 3600);
    }

    /**
     * Get routes with errors for monitoring.
     */
    public function getRoutesWithErrors(?string $environment = null): Collection
    {
        return $this->routeRepository->getWithErrors($environment);
    }

    /**
     * Get healthy routes.
     */
    public function getHealthyRoutes(?string $environment = null): Collection
    {
        return $this->routeRepository->getHealthy($environment);
    }

    /**
     * Update route priority.
     */
    public function updateRoutePriority(int $routeId, float $priority): bool
    {
        return $this->routeRepository->updatePriority($routeId, $priority);
    }

    /**
     * Update route change frequency.
     */
    public function updateRouteChangeFreq(int $routeId, string $changefreq): bool
    {
        return $this->routeRepository->updateChangeFreq($routeId, $changefreq);
    }

    /**
     * Toggle route active status.
     */
    public function toggleRouteStatus(int $routeId): bool
    {
        return $this->routeRepository->toggleStatus($routeId);
    }

    /**
     * Clear old status checks and errors.
     */
    public function cleanupOldData(int $days = 30): array
    {
        $deletedStatusChecks = $this->statusCheckRepository->cleanupOld($days);
        $deletedErrors = $this->errorRepository->cleanupOld($days);
        
        return [
            'status_checks_deleted' => $deletedStatusChecks,
            'errors_deleted' => $deletedErrors,
            'cutoff_date' => now()->subDays($days)->toISOString(),
        ];
    }

    /**
     * Get last generated time.
     */
    public function getLastGeneratedTime(): ?string
    {
        // This would typically check a cache or database record
        // For now, return a placeholder
        return null;
    }

    /**
     * Get estimated file size.
     */
    public function getEstimatedFileSize(): string
    {
        $totalRoutes = $this->routeRepository->getTotal();
        $estimatedSize = $totalRoutes * 200; // Rough estimate: 200 bytes per route
        
        if ($estimatedSize < 1024) {
            return $estimatedSize . ' B';
        } elseif ($estimatedSize < 1024 * 1024) {
            return round($estimatedSize / 1024, 1) . ' KB';
        } else {
            return round($estimatedSize / (1024 * 1024), 1) . ' MB';
        }
    }
} 