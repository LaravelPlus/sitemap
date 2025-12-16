<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Repositories;

use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LaravelPlus\Sitemap\Models\SitemapError;
use LaravelPlus\Sitemap\Models\SitemapRoute;

final class SitemapErrorRepository
{
    /**
     * Get errors with pagination.
     */
    public function getPaginated(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = SitemapError::with('route');

        if (isset($filters['environment'])) {
            $query->forEnvironment($filters['environment']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        return $query->orderBy('occurred_at', 'desc')->paginate($perPage);
    }

    /**
     * Get recent errors.
     */
    public function getRecent(int $hours = 24, int $limit = 10): Collection
    {
        return SitemapError::recent($hours)
            ->with('route')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent errors for a route.
     */
    public function getRecentForRoute(int $routeId, int $limit = 10): Collection
    {
        return SitemapError::where('route_id', $routeId)
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new error.
     */
    public function create(array $data): SitemapError
    {
        return SitemapError::create($data);
    }

    /**
     * Clean up old errors.
     */
    public function cleanupOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);

        return SitemapError::where('occurred_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get total error count.
     */
    public function getTotal(): int
    {
        return SitemapError::count();
    }

    /**
     * Get recent error count.
     */
    public function getRecentCount(int $hours = 24): int
    {
        return SitemapError::where('created_at', '>=', now()->subHours($hours))->count();
    }

    /**
     * Get error rate percentage.
     */
    public function getErrorRate(): float
    {
        $totalRoutes = SitemapRoute::count();
        $totalErrors = $this->getTotal();

        if ($totalRoutes === 0) {
            return 0.0;
        }

        return round(($totalErrors / $totalRoutes) * 100, 2);
    }

    /**
     * Get count of routes with errors.
     */
    public function getAffectedRoutesCount(): int
    {
        return SitemapError::distinct('route_id')->count();
    }

    /**
     * Get error types.
     */
    public function getErrorTypes(): array
    {
        return SitemapError::select('type')
            ->distinct()
            ->pluck('type')
            ->toArray();
    }

    /**
     * Delete all errors.
     */
    public function deleteAll(): int
    {
        $count = SitemapError::count();

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            // Delete in order to respect foreign key constraints
            SitemapError::query()->delete();
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        return $count;
    }

    /**
     * Delete old errors.
     */
    public function deleteOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);

        $count = SitemapError::where('occurred_at', '<', $cutoffDate)->count();
        SitemapError::where('occurred_at', '<', $cutoffDate)->delete();

        return $count;
    }
}
