<?php

namespace LaravelPlus\Sitemap\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Services\RouteDiscoveryService;
use LaravelPlus\Sitemap\Services\StatusCheckService;
use LaravelPlus\Sitemap\Console\Commands\DiscoverRoutesCommand;
use LaravelPlus\Sitemap\Console\Commands\CheckStatusCommand;
use LaravelPlus\Sitemap\Console\Commands\GenerateSitemapCommand;

class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/sitemap.php', 'sitemap'
        );

        $this->app->singleton(SitemapService::class, function ($app) {
            return new SitemapService();
        });

        $this->app->singleton(RouteDiscoveryService::class, function ($app) {
            return new RouteDiscoveryService();
        });

        $this->app->singleton(StatusCheckService::class, function ($app) {
            return new StatusCheckService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../src/Resources/Views', 'sitemap');
        $this->loadMigrationsFrom(__DIR__.'/../../src/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DiscoverRoutesCommand::class,
                CheckStatusCommand::class,
                GenerateSitemapCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../../config/sitemap.php' => config_path('sitemap.php'),
            ], 'sitemap-config');

            $this->publishes([
                __DIR__.'/../../src/Database/Migrations' => database_path('migrations'),
            ], 'sitemap-migrations');
        }
    }
} 