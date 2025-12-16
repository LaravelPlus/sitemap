<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LaravelPlus\Sitemap\Events\RoutesDiscovered;
use LaravelPlus\Sitemap\Events\RoutesStatusChecked;
use LaravelPlus\Sitemap\Events\SitemapGenerated;
use LaravelPlus\Sitemap\Listeners\CacheSitemapResults;
use LaravelPlus\Sitemap\Listeners\LogRoutesDiscovered;
use LaravelPlus\Sitemap\Listeners\NotifyStatusCheckComplete;
use LaravelPlus\Sitemap\Repositories\SitemapErrorRepository;
use LaravelPlus\Sitemap\Repositories\SitemapRouteRepository;
use LaravelPlus\Sitemap\Repositories\SitemapStatusCheckRepository;
use LaravelPlus\Sitemap\Services\RouteDiscoveryService;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Services\StatusCheckService;
use LaravelPlus\Sitemap\Services\ThresholdService;

final class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(SitemapRouteRepository::class);
        $this->app->singleton(SitemapStatusCheckRepository::class);
        $this->app->singleton(SitemapErrorRepository::class);

        // Register services
        $this->app->singleton(RouteDiscoveryService::class);
        $this->app->singleton(StatusCheckService::class);
        $this->app->singleton(ThresholdService::class);

        // Register main service with proper dependency injection
        $this->app->singleton(SitemapService::class, fn ($app) => new SitemapService(
            $app->make(RouteDiscoveryService::class),
            $app->make(StatusCheckService::class),
            $app->make(SitemapRouteRepository::class),
            $app->make(SitemapStatusCheckRepository::class),
            $app->make(SitemapErrorRepository::class)
        ));

        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/sitemap.php', 'sitemap');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register event listeners
        $this->registerEventListeners();

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/sitemap.php' => config_path('sitemap.php'),
        ], 'sitemap-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'sitemap-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Resources/Views' => resource_path('views/vendor/sitemap'),
        ], 'sitemap-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'sitemap');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Register commands
        $this->commands([
            \LaravelPlus\Sitemap\Console\Commands\DiscoverRoutesCommand::class,
            \LaravelPlus\Sitemap\Console\Commands\CheckStatusCommand::class,
            \LaravelPlus\Sitemap\Console\Commands\GenerateSitemapCommand::class,
        ]);
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Routes discovered event
        Event::listen(
            RoutesDiscovered::class,
            LogRoutesDiscovered::class
        );

        // Routes status checked event
        Event::listen(
            RoutesStatusChecked::class,
            NotifyStatusCheckComplete::class
        );

        // Sitemap generated event
        Event::listen(
            SitemapGenerated::class,
            CacheSitemapResults::class
        );
    }
}
