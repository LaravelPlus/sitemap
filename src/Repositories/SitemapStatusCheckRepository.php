<?php

namespace LaravelPlus\Sitemap\Repositories;

use LaravelPlus\Sitemap\Models\SitemapStatusCheck;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SitemapStatusCheckRepository
{
    /**
     * Get status checks with pagination.
     */
    public function getPaginated(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = SitemapStatusCheck::with('route');
        
        if (isset($filters['environment'])) {
            $query->forEnvironment($filters['environment']);
        }
        
        if (isset($filters['status_code'])) {
            $query->withStatusCode($filters['status_code']);
        }
        
        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        return $query->orderBy('checked_at', 'desc')->paginate($perPage);
    }

    /**
     * Get recent status checks for a route.
     */
    public function getRecentForRoute(int $routeId, int $limit = 10): Collection
    {
        return SitemapStatusCheck::where('route_id', $routeId)
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent status checks.
     */
    public function getRecent(int $hours = 24, int $limit = 10): Collection
    {
        return SitemapStatusCheck::where('checked_at', '>=', now()->subHours($hours))
            ->with('route')
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get status check statistics.
     */
    public function getStatistics(?string $environment = null): array
    {
        $query = SitemapStatusCheck::query();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        $total = $query->count();
        $successful = $query->clone()->where('status_code', '>=', 200)->where('status_code', '<', 300)->count();
        $failed = $query->clone()->where('status_code', '>=', 400)->count();
        $timeout = $query->clone()->where('status_code', 0)->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'timeout' => $timeout,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Create a new status check.
     */
    public function create(array $data): SitemapStatusCheck
    {
        return SitemapStatusCheck::create($data);
    }

    /**
     * Clean up old status checks.
     */
    public function cleanupOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        return SitemapStatusCheck::where('checked_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get healthy status checks count.
     */
    public function getHealthyCount(): int
    {
        return SitemapStatusCheck::where('status_code', '>=', 200)
            ->where('status_code', '<', 300)
            ->count();
    }

    /**
     * Get error status checks count.
     */
    public function getErrorCount(): int
    {
        return SitemapStatusCheck::where('status_code', '>=', 400)
            ->count();
    }

    /**
     * Get warning status checks count (4xx status codes).
     */
    public function getWarningCount(): int
    {
        return SitemapStatusCheck::whereBetween('status_code', [400, 499])->count();
    }

    /**
     * Get average response time.
     */
    public function getAverageResponseTime(): float
    {
        $avg = SitemapStatusCheck::whereNotNull('response_time')
            ->where('response_time', '>', 0)
            ->avg('response_time');
        
        return round($avg ?? 0, 2);
    }

    /**
     * Delete all status checks.
     */
    public function deleteAll(): int
    {
        $count = SitemapStatusCheck::count();
        
        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        try {
            // Delete in order to respect foreign key constraints
            SitemapStatusCheck::query()->delete();
        } finally {
            // Re-enable foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
        
        return $count;
    }

    /**
     * Delete old status checks.
     */
    public function deleteOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        $count = SitemapStatusCheck::where('checked_at', '<', $cutoffDate)->count();
        SitemapStatusCheck::where('checked_at', '<', $cutoffDate)->delete();
        
        return $count;
    }
} 