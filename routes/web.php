<?php

use Illuminate\Support\Facades\Route;
use LaravelPlus\Sitemap\Http\Controllers\SitemapController;
use LaravelPlus\Sitemap\Http\Controllers\SitemapApiController;

$prefix = config('sitemap.ui.route_prefix', 'sitemap');
$middleware = config('sitemap.ui.middleware', ['web', 'auth']);

Route::prefix($prefix)->middleware($middleware)->group(function () {
    
    // Dashboard
    Route::get('/', [SitemapController::class, 'dashboard'])->name('sitemap.dashboard');
    
    // Routes management
    Route::get('/routes', [SitemapController::class, 'routes'])->name('sitemap.routes');
    Route::get('/routes/{route}', [SitemapController::class, 'routeDetails'])->name('sitemap.route.details');
    
    // Status checks
    Route::get('/status', [SitemapController::class, 'status'])->name('sitemap.status');
    Route::get('/status/{route}', [SitemapController::class, 'routeStatus'])->name('sitemap.route.status');
    
    // Errors
    Route::get('/errors', [SitemapController::class, 'errors'])->name('sitemap.errors');
    Route::get('/errors/{route}', [SitemapController::class, 'routeErrors'])->name('sitemap.route.errors');
    
    // Sitemap generation
    Route::get('/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');
    Route::get('/download/{format}', [SitemapController::class, 'download'])->name('sitemap.download');
    
    // Settings
    Route::get('/settings', [SitemapController::class, 'settings'])->name('sitemap.settings');
    Route::post('/settings', [SitemapController::class, 'updateSettings'])->name('sitemap.settings.update');
    
    // API routes
    Route::prefix('api')->group(function () {
        Route::get('/stats', [SitemapApiController::class, 'stats'])->name('sitemap.api.stats');
        Route::get('/routes', [SitemapApiController::class, 'routes'])->name('sitemap.api.routes');
        Route::get('/routes/{route}', [SitemapApiController::class, 'route'])->name('sitemap.api.route');
        Route::put('/routes/{route}/priority', [SitemapApiController::class, 'updatePriority'])->name('sitemap.api.route.priority');
        Route::put('/routes/{route}/changefreq', [SitemapApiController::class, 'updateChangeFreq'])->name('sitemap.api.route.changefreq');
        Route::put('/routes/{route}/toggle', [SitemapApiController::class, 'toggleRoute'])->name('sitemap.api.route.toggle');
        Route::post('/discover', [SitemapApiController::class, 'discover'])->name('sitemap.api.discover');
        Route::post('/check-status', [SitemapApiController::class, 'checkStatus'])->name('sitemap.api.check-status');
        Route::post('/generate', [SitemapApiController::class, 'generate'])->name('sitemap.api.generate');
        Route::delete('/cleanup', [SitemapApiController::class, 'cleanup'])->name('sitemap.api.cleanup');
    });
}); 