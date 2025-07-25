<?php

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Collection;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected RouteDiscoveryService $routeDiscovery;
    protected StatusCheckService $statusCheck;

    public function __construct(
        RouteDiscoveryService $routeDiscovery,
        StatusCheckService $statusCheck
    ) {
        $this->routeDiscovery = $routeDiscovery;
        $this->statusCheck = $statusCheck;
    }

    /**
     * Discover and store routes for the current environment.
     */
    public function discoverAndStoreRoutes(): array
    {
        $environment = app()->environment();
        $config = config('sitemap.environments.' . $environment);

        if (!$config['enabled']) {
            return [
                'success' => false,
                'message' => "Sitemap discovery is disabled for environment: {$environment}",
            ];
        }

        try {
            $routes = $this->routeDiscovery->discoverRoutes();
            $stored = $this->routeDiscovery->storeRoutes($routes);

            return [
                'success' => true,
                'routes_discovered' => $routes->count(),
                'routes_stored' => $stored,
                'environment' => $environment,
            ];
        } catch (\Exception $e) {
            Log::error('Sitemap route discovery failed', [
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
     * Check status of all routes for the current environment.
     */
    public function checkAllRoutesStatus(): array
    {
        $environment = app()->environment();
        $config = config('sitemap.environments.' . $environment);

        if (!$config['enabled']) {
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
        $routes = $this->routeDiscovery->getRoutesForEnvironment($environment);
        
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
     * Get dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        $environment = app()->environment();
        
        $routeStats = $this->routeDiscovery->getStatistics($environment);
        $statusStats = $this->statusCheck->getStatistics($environment);
        
        $recentErrors = SitemapRoute::withErrors()
            ->forEnvironment($environment)
            ->with(['errors' => function ($query) {
                $query->recent(24)->limit(5);
            }])
            ->limit(10)
            ->get();

        return [
            'environment' => $environment,
            'routes' => $routeStats,
            'status_checks' => $statusStats,
            'recent_errors' => $recentErrors,
            'last_updated' => Cache::get("sitemap_status_{$environment}_updated_at"),
        ];
    }

    /**
     * Get routes with errors for monitoring.
     */
    public function getRoutesWithErrors(string $environment = null): Collection
    {
        return $this->routeDiscovery->getRoutesWithErrors($environment);
    }

    /**
     * Get healthy routes.
     */
    public function getHealthyRoutes(string $environment = null): Collection
    {
        $query = SitemapRoute::active();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        return $query->get()->filter->isHealthy();
    }

    /**
     * Update route priority.
     */
    public function updateRoutePriority(int $routeId, float $priority): bool
    {
        $route = SitemapRoute::find($routeId);
        
        if (!$route) {
            return false;
        }

        $route->update(['priority' => max(0.0, min(1.0, $priority))]);
        
        return true;
    }

    /**
     * Update route change frequency.
     */
    public function updateRouteChangeFreq(int $routeId, string $changefreq): bool
    {
        $validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
        
        if (!in_array($changefreq, $validFreqs)) {
            return false;
        }

        $route = SitemapRoute::find($routeId);
        
        if (!$route) {
            return false;
        }

        $route->update(['changefreq' => $changefreq]);
        
        return true;
    }

    /**
     * Toggle route active status.
     */
    public function toggleRouteStatus(int $routeId): bool
    {
        $route = SitemapRoute::find($routeId);
        
        if (!$route) {
            return false;
        }

        $route->update(['is_active' => !$route->is_active]);
        
        return true;
    }

    /**
     * Clear old status checks and errors.
     */
    public function cleanupOldData(int $days = 30): array
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedStatusChecks = \LaravelPlus\Sitemap\Models\SitemapStatusCheck::where('checked_at', '<', $cutoffDate)->delete();
        $deletedErrors = \LaravelPlus\Sitemap\Models\SitemapError::where('occurred_at', '<', $cutoffDate)->delete();
        
        return [
            'status_checks_deleted' => $deletedStatusChecks,
            'errors_deleted' => $deletedErrors,
            'cutoff_date' => $cutoffDate->toISOString(),
        ];
    }
} 