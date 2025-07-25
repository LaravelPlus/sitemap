<?php

namespace LaravelPlus\Sitemap\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Events\SitemapGenerated;

class GenerateSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 2;
    public $maxExceptions = 2;

    protected string $format;
    protected string $environment;
    protected ?string $userId;
    protected bool $saveToDisk;

    /**
     * Create a new job instance.
     */
    public function __construct(string $format = 'xml', ?string $environment = null, ?string $userId = null, bool $saveToDisk = true)
    {
        $this->format = $format;
        $this->environment = $environment ?? app()->environment();
        $this->userId = $userId;
        $this->saveToDisk = $saveToDisk;
        $this->onQueue('sitemap');
    }

    /**
     * Execute the job.
     */
    public function handle(SitemapService $sitemapService): void
    {
        $startTime = microtime(true);
        
        Log::info('Sitemap generation job started', [
            'format' => $this->format,
            'environment' => $this->environment,
            'user_id' => $this->userId,
            'save_to_disk' => $this->saveToDisk,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            // Generate sitemap
            $sitemap = $sitemapService->generateSitemap($this->format);
            
            $executionTime = microtime(true) - $startTime;
            $fileSize = strlen($sitemap);
            
            // Save to disk if requested
            $filePath = null;
            if ($this->saveToDisk) {
                $filePath = $this->saveSitemapToDisk($sitemap);
            }
            
            Log::info('Sitemap generation job completed', [
                'format' => $this->format,
                'file_size' => $this->formatFileSize($fileSize),
                'execution_time' => round($executionTime * 1000, 2) . 'ms',
                'environment' => $this->environment,
                'file_path' => $filePath,
            ]);

            // Fire event
            event(new SitemapGenerated(
                $this->format,
                $fileSize,
                $executionTime,
                $this->environment,
                $filePath
            ));

        } catch (\Exception $e) {
            Log::error('Sitemap generation job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'format' => $this->format,
                'environment' => $this->environment,
                'job_id' => $this->job->getJobId(),
            ]);

            throw $e;
        }
    }

    /**
     * Save sitemap to disk.
     */
    protected function saveSitemapToDisk(string $sitemap): string
    {
        $filename = "sitemap-{$this->format}-" . now()->format('Y-m-d-H-i-s') . ".{$this->format}";
        $path = "sitemaps/{$this->environment}/{$filename}";
        
        Storage::disk('public')->put($path, $sitemap);
        
        return $path;
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
    public function failed(\Throwable $exception): void
    {
        Log::error('Sitemap generation job failed permanently', [
            'error' => $exception->getMessage(),
            'format' => $this->format,
            'environment' => $this->environment,
            'job_id' => $this->job->getJobId(),
        ]);
    }
} 