<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Sitemap\Events\RoutesDiscovered;
use Throwable;

final class LogRoutesDiscovered implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'sitemap-logs';

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
    public function handle(RoutesDiscovered $event): void
    {
        Log::info('Routes discovered event logged', [
            'routes_discovered' => $event->routesDiscovered,
            'routes_stored' => $event->routesStored,
            'environment' => $event->environment,
            'execution_time' => round($event->executionTime * 1000, 2) . 'ms',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(RoutesDiscovered $event, Throwable $exception): void
    {
        Log::error('Failed to log routes discovered event', [
            'error' => $exception->getMessage(),
            'event_data' => [
                'routes_discovered' => $event->routesDiscovered,
                'environment' => $event->environment,
            ],
        ]);
    }
}
