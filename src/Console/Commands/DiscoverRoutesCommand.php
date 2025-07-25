<?php

namespace LaravelPlus\Sitemap\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\Sitemap\Services\SitemapService;

class DiscoverRoutesCommand extends Command
{
    protected $signature = 'sitemap:discover 
                            {--environment= : Environment to discover routes for}
                            {--force : Force discovery even if disabled}';

    protected $description = 'Discover and store GET routes for sitemap management';

    public function handle(SitemapService $sitemapService): int
    {
        $this->info('🔍 Discovering routes for sitemap...');

        $environment = $this->option('environment') ?? app()->environment();
        $force = $this->option('force');

        if ($force) {
            $this->warn('⚠️  Force mode enabled - ignoring environment settings');
        }

        try {
            $result = $sitemapService->discoverAndStoreRoutes();

            if ($result['success']) {
                $this->info('✅ Route discovery completed successfully!');
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Routes Discovered', $result['routes_discovered']],
                        ['Routes Stored', $result['routes_stored']],
                        ['Environment', $result['environment']],
                    ]
                );
            } else {
                $this->error('❌ Route discovery failed: ' . $result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Route discovery failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 