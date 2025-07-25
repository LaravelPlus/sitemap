<?php

namespace LaravelPlus\Sitemap\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Sitemap\Services\StatusCheckService;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Events\RoutesStatusChecked;

class CheckRoutesStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 2;
    public $maxExceptions = 2;

    protected string $environment;
    protected ?string $userId;
    protected int $batchSize;
    protected bool $checkAll;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $environment = null, ?string $userId = null, int $batchSize = 50, bool $checkAll = false)
    {
        $this->environment = $environment ?? app()->environment();
        $this->userId = $userId;
        $this->batchSize = $batchSize;
        $this->checkAll = $checkAll;
        $this->onQueue('sitemap');
    }

    /**
     * Execute the job.
     */
    public function handle(StatusCheckService $statusCheckService): void
    {
        $startTime = microtime(true);
        
        Log::info('Sitemap status check job started', [
            'environment' => $this->environment,
            'user_id' => $this->userId,
            'batch_size' => $this->batchSize,
            'check_all' => $this->checkAll,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            // Get routes to check
            $routes = $this->getRoutesToCheck();
            
            if ($routes->isEmpty()) {
                Log::info('Sitemap status check job: no routes to check', [
                    'environment' => $this->environment,
                ]);
                return;
            }

            // Check routes status
            $results = $statusCheckService->checkRoutes($routes);
            
            $executionTime = microtime(true) - $startTime;
            
            Log::info('Sitemap status check job completed', [
                'routes_checked' => $results['total'],
                'successful' => $results['successful'],
                'failed' => $results['failed'],
                'errors' => $results['errors'],
                'success_rate' => $results['success_rate'] ?? 0,
                'execution_time' => round($executionTime * 1000, 2) . 'ms',
                'environment' => $this->environment,
            ]);

            // Fire event
            event(new RoutesStatusChecked(
                $results['total'],
                $results['successful'],
                $results['failed'],
                $results['errors'],
                $this->environment,
                $executionTime
            ));

        } catch (\Exception $e) {
            Log::error('Sitemap status check job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'environment' => $this->environment,
                'job_id' => $this->job->getJobId(),
            ]);

            throw $e;
        }
    }

    /**
     * Get routes to check based on configuration.
     */
    protected function getRoutesToCheck()
    {
        $query = SitemapRoute::active();
        
        if ($this->environment) {
            $query->forEnvironment($this->environment);
        }

        if (!$this->checkAll) {
            // Only check routes that haven't been checked recently
            $query->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhere('last_checked_at', '<=', now()->subMinutes(30));
            });
        }

        return $query->limit($this->batchSize)->get();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Sitemap status check job failed permanently', [
            'error' => $exception->getMessage(),
            'environment' => $this->environment,
            'job_id' => $this->job->getJobId(),
        ]);
    }
} 