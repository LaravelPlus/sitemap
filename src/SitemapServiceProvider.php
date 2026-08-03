<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SitemapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sitemap.php', 'sitemap');

        // Singleton: add()/only() calls from the app's providers must reach the
        // same instance the route resolves.
        $this->app->singleton(Sitemap::class);
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/sitemap.php' => config_path('sitemap.php')], 'sitemap-config');

        if ($this->app->runningInConsole()) {
            $this->commands([ShowCommand::class, RoutesCommand::class]);
        }

        if (config('sitemap.route') !== null) {
            Route::get(config('sitemap.route'), fn () => $this->app->make(Sitemap::class)->response())
                ->name('sitemap');
        }
    }
}
