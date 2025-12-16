<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Sitemap\Events\RoutesDiscovered;
use LaravelPlus\Sitemap\Repositories\SitemapRouteRepository;
use LaravelPlus\Sitemap\Services\RouteDiscoveryService;
use Throwable;

final class DiscoverRoutesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    public $tries = 3;

    public $maxExceptions = 3;

    protected string $environment;

    protected ?string $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $environment = null, ?string $userId = null)
    {
        $this->environment = $environment ?? app()->environment();
        $this->userId = $userId;
        $this->onQueue('sitemap');
    }

    /**
     * Execute the job.
     */
    public function handle(RouteDiscoveryService $discoveryService, SitemapRouteRepository $routeRepository): void
    {
        $startTime = microtime(true);

        Log::info('Sitemap route discovery job started', [
            'environment' => $this->environment,
            'user_id' => $this->userId,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            // Discover routes
            $routes = $discoveryService->discoverRoutes();

            // Store routes in batches
            $storedCount = $routeRepository->storeFromDiscovery($routes);

            $executionTime = microtime(true) - $startTime;

            Log::info('Sitemap route discovery job completed', [
                'routes_discovered' => $routes->count(),
                'routes_stored' => $storedCount,
                'execution_time' => round($executionTime * 1000, 2) . 'ms',
                'environment' => $this->environment,
            ]);

            // Fire event
            event(new RoutesDiscovered($routes->count(), $storedCount, $this->environment, $executionTime));

        } catch (Exception $e) {
            Log::error('Sitemap route discovery job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'environment' => $this->environment,
                'job_id' => $this->job->getJobId(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Sitemap route discovery job failed permanently', [
            'error' => $exception->getMessage(),
            'environment' => $this->environment,
            'job_id' => $this->job->getJobId(),
        ]);
    }
}
