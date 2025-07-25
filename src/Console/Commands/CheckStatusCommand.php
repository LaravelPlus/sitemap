<?php

namespace LaravelPlus\Sitemap\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Models\SitemapRoute;

class CheckStatusCommand extends Command
{
    protected $signature = 'sitemap:check-status 
                            {--environment= : Environment to check routes for}
                            {--route= : Check specific route by ID}
                            {--force : Force status check even if disabled}';

    protected $description = 'Check status of discovered routes';

    public function handle(SitemapService $sitemapService): int
    {
        $this->info('🔍 Checking route status...');

        $environment = $this->option('environment') ?? app()->environment();
        $routeId = $this->option('route');
        $force = $this->option('force');

        if ($force) {
            $this->warn('⚠️  Force mode enabled - ignoring environment settings');
        }

        try {
            if ($routeId) {
                $route = SitemapRoute::find($routeId);
                if (!$route) {
                    $this->error("❌ Route with ID {$routeId} not found");
                    return 1;
                }

                $this->info("🔍 Checking status for route: {$route->uri}");
                $result = $sitemapService->statusCheck->checkRoutes(collect([$route]));
            } else {
                $result = $sitemapService->checkAllRoutesStatus();
            }

            if ($result['success'] ?? true) {
                $this->info('✅ Status check completed successfully!');
                
                $stats = $result['results'] ?? $result;
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Routes', $stats['total']],
                        ['Successful', $stats['successful']],
                        ['Failed', $stats['failed']],
                        ['Errors', $stats['errors']],
                        ['Success Rate', $this->calculateSuccessRate($stats) . '%'],
                    ]
                );

                if ($stats['errors'] > 0) {
                    $this->warn("⚠️  {$stats['errors']} routes encountered errors");
                }
            } else {
                $this->error('❌ Status check failed: ' . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Status check failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function calculateSuccessRate(array $stats): float
    {
        if ($stats['total'] === 0) {
            return 0.0;
        }

        return round(($stats['successful'] / $stats['total']) * 100, 2);
    }
} 