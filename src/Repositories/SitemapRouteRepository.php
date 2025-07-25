<?php

namespace LaravelPlus\Sitemap\Repositories;

use LaravelPlus\Sitemap\Models\SitemapRoute;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapRouteRepository
{
    /**
     * Get all routes with pagination and caching.
     */
    public function getPaginated(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = 'sitemap_routes_paginated_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, 300, function () use ($perPage, $filters) {
            $query = SitemapRoute::query();
            
            // Apply filters
            if (isset($filters['environment'])) {
                $query->forEnvironment($filters['environment']);
            }
            
            if (isset($filters['status'])) {
                switch ($filters['status']) {
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
                        $query->where('is_healthy', true);
                        break;
                }
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('uri', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('name', 'like', '%' . $filters['search'] . '%');
                });
            }

            return $query->orderBy('uri')->paginate($perPage);
        });
    }

    /**
     * Get routes for a specific environment with caching.
     */
    public function getForEnvironment(string $environment): Collection
    {
        $cacheKey = "sitemap_routes_environment_{$environment}";
        
        return Cache::remember($cacheKey, 600, function () use ($environment) {
            return SitemapRoute::forEnvironment($environment)
                ->active()
                ->with(['statusChecks' => function ($query) {
                    $query->latest()->limit(5);
                }])
                ->get();
        });
    }

    /**
     * Get routes with errors with caching.
     */
    public function getWithErrors(?string $environment = null): Collection
    {
        $cacheKey = 'sitemap_routes_with_errors' . ($environment ? "_$environment" : '');
        
        return Cache::remember($cacheKey, 300, function () use ($environment) {
            $query = SitemapRoute::withErrors()
                ->with(['statusChecks' => function ($query) {
                    $query->latest()->limit(3);
                }]);
            
            if ($environment) {
                $query->forEnvironment($environment);
            }

            return $query->get();
        });
    }

    /**
     * Get healthy routes with caching.
     */
    public function getHealthy(?string $environment = null): Collection
    {
        $cacheKey = 'sitemap_routes_healthy' . ($environment ? "_$environment" : '');
        
        return Cache::remember($cacheKey, 600, function () use ($environment) {
            $query = SitemapRoute::active()
                ->where('is_healthy', true)
                ->with(['statusChecks' => function ($query) {
                    $query->latest()->limit(3);
                }]);
            
            if ($environment) {
                $query->forEnvironment($environment);
            }

            return $query->get();
        });
    }

    /**
     * Get route statistics with caching.
     */
    public function getStatistics(?string $environment = null): array
    {
        $cacheKey = 'sitemap_route_statistics' . ($environment ? "_$environment" : '');
        
        return Cache::remember($cacheKey, 300, function () use ($environment) {
            $query = SitemapRoute::query();
            
            if ($environment) {
                $query->forEnvironment($environment);
            }

            $total = $query->count();
            $active = $query->clone()->active()->count();
            $withErrors = $query->clone()->withErrors()->count();
            
            // Calculate healthy routes correctly (total - with errors)
            $healthy = $total - $withErrors;
            
            $inactive = $query->clone()->where('is_active', false)->count();
            
            // Get average response time
            $avgResponseTime = $query->clone()
                ->whereNotNull('last_response_time')
                ->avg('last_response_time') ?? 0;
            
            // Get recently checked routes
            $recentlyChecked = $query->clone()
                ->where('last_checked_at', '>=', now()->subHours(24))
                ->count();

            // Get unknown status (not checked recently)
            $unknown = $total - $recentlyChecked;

            // Calculate success rate
            $successRate = $total > 0 ? round((($total - $withErrors) / $total) * 100, 2) : 0;

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'healthy' => $healthy,
                'with_errors' => $withErrors,
                'unknown' => $unknown,
                'recently_checked' => $recentlyChecked,
                'success_rate' => $successRate,
                'avg_response_time' => round($avgResponseTime, 3),
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get total routes count with caching.
     */
    public function getTotal(): int
    {
        return Cache::remember('sitemap_routes_total', 600, function () {
            return SitemapRoute::count();
        });
    }

    /**
     * Get healthy routes count with caching.
     */
    public function getHealthyCount(): int
    {
        return Cache::remember('sitemap_routes_healthy_count', 300, function () {
            return SitemapRoute::where('is_healthy', true)->count();
        });
    }

    /**
     * Find route by ID with caching.
     */
    public function findById(int $id): ?SitemapRoute
    {
        $cacheKey = "sitemap_route_{$id}";
        
        return Cache::remember($cacheKey, 600, function () use ($id) {
            return SitemapRoute::with(['statusChecks' => function ($query) {
                $query->latest()->limit(10);
            }, 'errors' => function ($query) {
                $query->latest()->limit(10);
            }])->find($id);
        });
    }

    /**
     * Update route priority with cache invalidation.
     */
    public function updatePriority(int $routeId, float $priority): bool
    {
        $route = $this->findById($routeId);
        
        if (!$route) {
            return false;
        }

        $success = $route->update(['priority' => max(0.0, min(1.0, $priority))]);
        
        if ($success) {
            $this->clearRouteCache($routeId);
            $this->clearStatisticsCache();
        }
        
        return $success;
    }

    /**
     * Update route change frequency with cache invalidation.
     */
    public function updateChangeFreq(int $routeId, string $changefreq): bool
    {
        $validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
        
        if (!in_array($changefreq, $validFreqs)) {
            return false;
        }

        $route = $this->findById($routeId);
        
        if (!$route) {
            return false;
        }

        $success = $route->update(['changefreq' => $changefreq]);
        
        if ($success) {
            $this->clearRouteCache($routeId);
            $this->clearStatisticsCache();
        }
        
        return $success;
    }

    /**
     * Toggle route active status with cache invalidation.
     */
    public function toggleStatus(int $routeId): bool
    {
        $route = $this->findById($routeId);
        
        if (!$route) {
            return false;
        }

        $success = $route->update(['is_active' => !$route->is_active]);
        
        if ($success) {
            $this->clearRouteCache($routeId);
            $this->clearStatisticsCache();
        }
        
        return $success;
    }

    /**
     * Store routes from discovery with optimized batch operations.
     */
    public function storeFromDiscovery(Collection $routes): int
    {
        $stored = 0;
        $environment = app()->environment();
        
        // Use batch operations for better performance
        $routes->chunk(100)->each(function ($chunk) use (&$stored, $environment) {
            $data = $chunk->map(function ($routeData) use ($environment) {
                $routeData['environment'] = $environment;
                return $routeData;
            })->toArray();
            
            foreach ($data as $routeData) {
                SitemapRoute::updateOrCreate(
                    ['uri' => $routeData['uri'], 'environment' => $environment],
                    $routeData
                );
                $stored++;
            }
        });
        
        // Clear cache after bulk operation
        $this->clearStatisticsCache();
        
        return $stored;
    }

    /**
     * Clear cache for specific route.
     */
    protected function clearRouteCache(int $routeId): void
    {
        Cache::forget("sitemap_route_{$routeId}");
    }

    /**
     * Clear statistics cache.
     */
    protected function clearStatisticsCache(): void
    {
        Cache::forget('sitemap_route_statistics');
        Cache::forget('sitemap_route_statistics_local');
        Cache::forget('sitemap_routes_total');
        Cache::forget('sitemap_routes_healthy_count');
        Cache::forget('sitemap_routes_with_errors');
        Cache::forget('sitemap_routes_healthy');
    }

    /**
     * Get routes for bulk status checking.
     */
    public function getForBulkCheck(int $limit = 50): Collection
    {
        return SitemapRoute::active()
            ->where(function ($query) {
                $query->whereNull('last_checked_at')
                      ->orWhere('last_checked_at', '<=', now()->subMinutes(30));
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Delete all routes.
     */
    public function deleteAll(): int
    {
        $count = SitemapRoute::count();
        
        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        try {
            // Delete in order to respect foreign key constraints
            SitemapRoute::query()->delete();
        } finally {
            // Re-enable foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
        
        // Clear all route-related caches
        $this->clearStatisticsCache();
        Cache::forget('sitemap_routes_total');
        Cache::forget('sitemap_routes_healthy_count');
        
        return $count;
    }

    /**
     * Delete old routes.
     */
    public function deleteOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        $count = SitemapRoute::where('created_at', '<', $cutoffDate)->count();
        SitemapRoute::where('created_at', '<', $cutoffDate)->delete();
        
        // Clear related caches
        $this->clearStatisticsCache();
        
        return $count;
    }
} 