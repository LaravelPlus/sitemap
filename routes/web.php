<?php

use Illuminate\Support\Facades\Route;
use LaravelPlus\Sitemap\Http\Controllers\SitemapController;

$prefix = config('sitemap.ui.route_prefix', 'sitemap');
$middleware = config('sitemap.ui.middleware', ['web']);

Route::prefix($prefix)->middleware($middleware)->group(function () {
    
    // Dashboard
    Route::get('/', [SitemapController::class, 'dashboard'])->name('sitemap.dashboard');
    
    // Routes management
    Route::get('/routes', [SitemapController::class, 'routes'])->name('sitemap.routes');
    Route::get('/routes/{route}', [SitemapController::class, 'routeDetails'])->name('sitemap.route.details');
    
    // Route discovery
    Route::post('/discover', [SitemapController::class, 'discover'])->name('sitemap.discover');
    Route::post('/check-status', [SitemapController::class, 'checkStatus'])->name('sitemap.check-status');
    
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
    
    // Data management
    Route::post('/data/empty', [SitemapController::class, 'emptyData'])->name('sitemap.data.empty');
    Route::post('/data/truncate', [SitemapController::class, 'truncateOldData'])->name('sitemap.data.truncate');
    Route::post('/cache/clear', [SitemapController::class, 'clearCache'])->name('sitemap.cache.clear');
    
    // Job management
    Route::get('/jobs/status', [SitemapController::class, 'jobStatus'])->name('sitemap.jobs.status');
    Route::get('/jobs/history', [SitemapController::class, 'jobHistory'])->name('sitemap.jobs.history');
}); 