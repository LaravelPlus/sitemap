<?php

use Illuminate\Support\Facades\Route;
use LaravelPlus\Sitemap\Http\Controllers\SitemapApiController;

$prefix = config('sitemap.ui.route_prefix', 'sitemap');
$middleware = config('sitemap.api.middleware', ['api']);

Route::prefix($prefix)->middleware($middleware)->group(function () {
    
    // API routes
    Route::prefix('api')->group(function () {
        // Statistics
        Route::get('/stats', [SitemapApiController::class, 'stats'])->name('sitemap.api.stats');
        
        // Routes management
        Route::get('/routes', [SitemapApiController::class, 'routes'])->name('sitemap.api.routes');
        Route::get('/routes/{route}', [SitemapApiController::class, 'route'])->name('sitemap.api.route');
        Route::put('/routes/{route}/priority', [SitemapApiController::class, 'updatePriority'])->name('sitemap.api.route.priority');
        Route::put('/routes/{route}/changefreq', [SitemapApiController::class, 'updateChangeFreq'])->name('sitemap.api.route.changefreq');
        Route::put('/routes/{route}/toggle', [SitemapApiController::class, 'toggleRoute'])->name('sitemap.api.route.toggle');
        
        // Discovery and status checking
        Route::post('/discover', [SitemapApiController::class, 'discover'])->name('sitemap.api.discover');
        Route::post('/check-status', [SitemapApiController::class, 'checkStatus'])->name('sitemap.api.check-status');
        Route::post('/check-route-status', [SitemapApiController::class, 'checkRouteStatus'])->name('sitemap.api.check-route-status');
        Route::post('/test-route', [SitemapApiController::class, 'testRoute'])->name('sitemap.api.test-route');
        Route::get('/threshold-alerts', [SitemapApiController::class, 'thresholdAlerts'])->name('sitemap.api.threshold-alerts');
        
        // Sitemap generation
        Route::post('/generate', [SitemapApiController::class, 'generate'])->name('sitemap.api.generate');
        
        // Settings
        Route::post('/settings', [SitemapApiController::class, 'saveSettings'])->name('sitemap.api.settings');
        Route::post('/settings/reset', [SitemapApiController::class, 'resetSettings'])->name('sitemap.api.settings.reset');
        
        // Maintenance
        Route::delete('/cleanup', [SitemapApiController::class, 'cleanup'])->name('sitemap.api.cleanup');
    });
}); 