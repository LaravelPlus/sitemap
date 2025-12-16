<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Sitemap\Events\RoutesStatusChecked;
use Throwable;

final class NotifyStatusCheckComplete implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'sitemap-notifications';

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
    public function handle(RoutesStatusChecked $event): void
    {
        $successRate = $event->totalRoutes > 0 ? round(($event->successful / $event->totalRoutes) * 100, 2) : 0;

        // Update cache with latest status check results
        Cache::put("sitemap_status_check_{$event->environment}_latest", [
            'total_routes' => $event->totalRoutes,
            'successful' => $event->successful,
            'failed' => $event->failed,
            'errors' => $event->errors,
            'success_rate' => $successRate,
            'execution_time' => $event->executionTime,
            'timestamp' => now()->toISOString(),
        ], 3600);

        // Log the status check completion
        Log::info('Status check completed', [
            'total_routes' => $event->totalRoutes,
            'successful' => $event->successful,
            'failed' => $event->failed,
            'errors' => $event->errors,
            'success_rate' => $successRate . '%',
            'environment' => $event->environment,
            'execution_time' => round($event->executionTime * 1000, 2) . 'ms',
        ]);

        // Check if we need to send alerts
        if ($successRate < 80) {
            $this->sendLowSuccessRateAlert($event, $successRate);
        }

        if ($event->errors > 10) {
            $this->sendHighErrorRateAlert($event);
        }
    }

    /**
     * Send alert for low success rate.
     */
    protected function sendLowSuccessRateAlert(RoutesStatusChecked $event, float $successRate): void
    {
        Log::warning('Low success rate detected in status check', [
            'success_rate' => $successRate . '%',
            'total_routes' => $event->totalRoutes,
            'failed' => $event->failed,
            'environment' => $event->environment,
        ]);

        // Here you could send notifications via email, Slack, etc.
        // For now, we'll just log it
    }

    /**
     * Send alert for high error rate.
     */
    protected function sendHighErrorRateAlert(RoutesStatusChecked $event): void
    {
        Log::error('High error rate detected in status check', [
            'errors' => $event->errors,
            'total_routes' => $event->totalRoutes,
            'environment' => $event->environment,
        ]);

        // Here you could send notifications via email, Slack, etc.
        // For now, we'll just log it
    }

    /**
     * Handle a job failure.
     */
    public function failed(RoutesStatusChecked $event, Throwable $exception): void
    {
        Log::error('Failed to process status check completion notification', [
            'error' => $exception->getMessage(),
            'event_data' => [
                'total_routes' => $event->totalRoutes,
                'environment' => $event->environment,
            ],
        ]);
    }
}
