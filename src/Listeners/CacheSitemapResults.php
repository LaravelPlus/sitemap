<?php

namespace LaravelPlus\Sitemap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use LaravelPlus\Sitemap\Events\SitemapGenerated;

class CacheSitemapResults implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'sitemap-cache';
    public $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SitemapGenerated $event): void
    {
        // Cache the sitemap generation results
        $cacheKey = "sitemap_generated_{$event->environment}_{$event->format}";
        
        Cache::put($cacheKey, [
            'format' => $event->format,
            'file_size' => $event->fileSize,
            'execution_time' => $event->executionTime,
            'environment' => $event->environment,
            'file_path' => $event->filePath,
            'generated_at' => now()->toISOString(),
        ], 3600); // Cache for 1 hour

        // Update last generated time
        Cache::put("sitemap_last_generated_{$event->environment}", now()->toISOString(), 86400);

        // Log the sitemap generation
        Log::info('Sitemap generated and cached', [
            'format' => $event->format,
            'file_size' => $this->formatFileSize($event->fileSize),
            'execution_time' => round($event->executionTime * 1000, 2) . 'ms',
            'environment' => $event->environment,
            'file_path' => $event->filePath,
        ]);

        // Update statistics
        $this->updateSitemapStatistics($event);
    }

    /**
     * Update sitemap generation statistics.
     */
    protected function updateSitemapStatistics(SitemapGenerated $event): void
    {
        $statsKey = "sitemap_stats_{$event->environment}";
        $stats = Cache::get($statsKey, [
            'total_generated' => 0,
            'total_file_size' => 0,
            'avg_execution_time' => 0,
            'last_generated' => null,
        ]);

        $stats['total_generated']++;
        $stats['total_file_size'] += $event->fileSize;
        $stats['avg_execution_time'] = ($stats['avg_execution_time'] * ($stats['total_generated'] - 1) + $event->executionTime) / $stats['total_generated'];
        $stats['last_generated'] = now()->toISOString();

        Cache::put($statsKey, $stats, 86400); // Cache for 24 hours
    }

    /**
     * Format file size for display.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(SitemapGenerated $event, \Throwable $exception): void
    {
        Log::error('Failed to cache sitemap generation results', [
            'error' => $exception->getMessage(),
            'event_data' => [
                'format' => $event->format,
                'environment' => $event->environment,
            ],
        ]);
    }
} 