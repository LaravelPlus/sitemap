<?php

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Collection;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Models\SitemapStatusCheck;
use LaravelPlus\Sitemap\Models\SitemapError;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Sitemap\Services\ThresholdService;
use Illuminate\Support\Facades\Cache;

class StatusCheckService
{
    protected Client $client;
    protected array $config;
    protected ThresholdService $thresholdService;

    public function __construct()
    {
        $this->config = config('sitemap.status_check', [
            'timeout' => 10,
            'concurrent_requests' => 3,
            'acceptable_status_codes' => [200, 201, 202, 301, 302, 404],
            'max_routes_per_check' => 50, // Added for limiting routes
            'delay_between_checks' => 1, // Added for delaying between batches
        ]);
        
        $this->client = new Client([
            'timeout' => $this->config['timeout'],
            'verify' => false, // Allow self-signed certificates
            'allow_redirects' => [
                'max' => 5,
                'strict' => false,
                'referer' => true,
                'protocols' => ['http', 'https'],
            ],
        ]);

        $this->thresholdService = new ThresholdService();
    }

    /**
     * Check status of all routes for an environment with optimizations.
     */
    public function checkAllRoutes(?string $environment = null): array
    {
        try {
            // Use cache to prevent duplicate checks
            $cacheKey = 'sitemap_status_check_' . ($environment ?? 'all') . '_' . now()->format('Y-m-d-H-i');
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Test database connection first
            try {
                $totalRoutes = SitemapRoute::count();
                Log::info('Sitemap database connection test', ['total_routes' => $totalRoutes]);
            } catch (\Exception $e) {
                Log::error('Sitemap database connection failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'errors' => 1,
                    'details' => [],
                    'error' => 'Database connection failed: ' . $e->getMessage(),
                ];
            }

            // Get routes that need checking (optimized query)
            $routes = SitemapRoute::active()
                ->where(function ($query) {
                    $query->whereNull('last_checked_at')
                          ->orWhere('last_checked_at', '<=', now()->subMinutes(30));
                });
            
            if ($environment) {
                $routes->forEnvironment($environment);
            }

            $routes = $routes->limit($this->config['max_routes_per_check'] ?? 50)->get();
            
            Log::info('Sitemap routes found for status check', [
                'total_routes' => $routes->count(),
                'environment' => $environment,
            ]);
            
            if ($routes->isEmpty()) {
                $result = [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'errors' => 0,
                    'details' => [],
                    'message' => 'No routes need checking at this time.',
                ];
                
                Cache::put($cacheKey, $result, 300);
                return $result;
            }
            
            $results = $this->checkRoutes($routes);
            
            // Check bulk thresholds for route groups
            $bulkThresholdAlerts = $this->thresholdService->checkBulkThresholds($routes);
            if (!empty($bulkThresholdAlerts)) {
                $results['threshold_alerts'] = $bulkThresholdAlerts;
            }
            
            // Cache results for 5 minutes
            Cache::put($cacheKey, $results, 300);
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Sitemap checkAllRoutes error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'environment' => $environment,
            ]);
            
            return [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => 1,
                'details' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check status of specific routes with optimized concurrency.
     */
    public function checkRoutes(Collection $routes): array
    {
        try {
            $results = [
                'total' => $routes->count(),
                'successful' => 0,
                'failed' => 0,
                'errors' => 0,
                'details' => [],
            ];

            if ($routes->isEmpty()) {
                return $results;
            }

            // Process routes in optimized batches
            $batchSize = min($this->config['concurrent_requests'], 10); // Cap at 10 concurrent
            $batches = $routes->chunk($batchSize);
            $batchCount = 0;
            
            foreach ($batches as $batch) {
                $batchCount++;
                Log::info('Sitemap processing batch', [
                    'batch_number' => $batchCount,
                    'batch_size' => $batch->count(),
                    'total_batches' => $batches->count(),
                ]);
                
                $batchResults = $this->checkRouteBatch($batch);
                
                // Merge batch results
                $results['successful'] += $batchResults['successful'];
                $results['failed'] += $batchResults['failed'];
                $results['errors'] += $batchResults['errors'];
                $results['details'] = array_merge($results['details'], $batchResults['details']);
                
                // Add delay between batches to prevent server overload
                if ($batchCount < $batches->count()) {
                    usleep($this->config['delay_between_checks'] * 1000000); // Convert to microseconds
                }
            }
            
            // Calculate success rate
            $results['success_rate'] = $results['total'] > 0 
                ? round(($results['successful'] / $results['total']) * 100, 2) 
                : 0;
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Sitemap checkRoutes error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'total' => $routes->count(),
                'successful' => 0,
                'failed' => 0,
                'errors' => 1,
                'details' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check a batch of routes concurrently.
     */
    protected function checkRouteBatch(Collection $routes): array
    {
        $promises = [];
        $routeMap = [];

        foreach ($routes as $route) {
            try {
                $url = $route->full_url;
                $promise = $this->client->getAsync($url);
                $promises[$url] = $promise;
                $routeMap[$url] = $route;
            } catch (\Exception $e) {
                Log::error('Sitemap route URL generation error', [
                    'route_id' => $route->id,
                    'uri' => $route->uri,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => 0,
            'details' => [],
        ];

        if (empty($promises)) {
            return $results;
        }

        try {
            // Set a timeout for the entire batch to prevent hanging
            $batchTimeout = $this->config['timeout'] * 2; // Double the individual timeout for batch
            $responses = Promise\Utils::settle($promises)->wait($batchTimeout);
            
            foreach ($responses as $url => $response) {
                $route = $routeMap[$url];
                $result = $this->processResponse($route, $response);
                
                // Skip timeout routes if configured
                if ($this->config['skip_timeout_routes'] ?? true) {
                    if (isset($result['error_message']) && 
                        (strpos($result['error_message'], 'timeout') !== false || 
                         strpos($result['error_message'], 'timed out') !== false)) {
                        Log::info('Sitemap skipping timeout route', [
                            'route_id' => $route->id,
                            'uri' => $route->uri,
                            'url' => $url,
                        ]);
                        continue;
                    }
                }
                
                $results['details'][] = $result;
                
                if ($result['success']) {
                    $results['successful']++;
                } elseif ($result['error']) {
                    $results['errors']++;
                } else {
                    $results['failed']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Sitemap status check batch failed', [
                'error' => $e->getMessage(),
                'routes' => $routes->pluck('uri')->toArray(),
            ]);
            
            // Mark all routes in batch as failed
            foreach ($routes as $route) {
                $results['details'][] = [
                    'route_id' => $route->id,
                    'uri' => $route->uri,
                    'success' => false,
                    'error' => true,
                    'status_code' => null,
                    'response_time' => null,
                    'error_message' => $e->getMessage(),
                ];
                $results['errors']++;
            }
        }

        return $results;
    }

    /**
     * Process a response and store the result.
     */
    protected function processResponse(SitemapRoute $route, array $response): array
    {
        $result = [
            'route_id' => $route->id,
            'uri' => $route->uri,
            'success' => false,
            'error' => false,
            'status_code' => null,
            'response_time' => null,
            'response_size' => null,
            'error_message' => null,
            'error_type' => null,
            'is_healthy' => false,
        ];

        if ($response['state'] === 'fulfilled') {
            $httpResponse = $response['value'];
            $statusCode = $httpResponse->getStatusCode();
            $responseTime = $this->calculateResponseTime($httpResponse);
            $responseSize = strlen($httpResponse->getBody()->getContents());

            $result['success'] = true;
            $result['status_code'] = $statusCode;
            $result['response_time'] = $responseTime;
            $result['response_size'] = $responseSize;
            $result['is_healthy'] = in_array($statusCode, $this->config['acceptable_status_codes']);

            // Store the status check
            $this->storeStatusCheck($route, $result, $httpResponse);

            // Update route status
            $this->updateRouteStatus($route, $result);

            // Check thresholds for this route
            $thresholdAlerts = $this->thresholdService->checkRouteThresholds($route, $result);
            if (!empty($thresholdAlerts)) {
                $result['threshold_alerts'] = $thresholdAlerts;
            }
        } else {
            $exception = $response['reason'];
            $result['error'] = true;
            $result['error_message'] = $exception->getMessage();
            $result['error_type'] = $this->getErrorType($exception);

            // Store the error
            $this->storeError($route, $exception);

            // Update route error status
            $this->updateRouteErrorStatus($route, $exception);
        }

        return $result;
    }

    /**
     * Calculate response time from headers.
     */
    protected function calculateResponseTime($response): float
    {
        try {
            $headers = $response->getHeaders();
            
            if (isset($headers['X-Runtime'])) {
                return (float) $headers['X-Runtime'][0];
            }
            
            // Fallback to microtime calculation
            return microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
        } catch (\Exception $e) {
            // If we can't get headers or calculate time, return a default value
            return 0.0;
        }
    }

    /**
     * Store status check result.
     */
    protected function storeStatusCheck(SitemapRoute $route, array $result, $response): void
    {
        try {
            $body = $response->getBody()->getContents();
            $headers = $response->getHeaders();
            
            SitemapStatusCheck::create([
                'route_id' => $route->id,
                'status_code' => $result['status_code'],
                'response_time' => $result['response_time'],
                'response_size' => strlen($body),
                'headers' => $headers,
                'body_preview' => substr($body, 0, 500),
                'checked_at' => now(),
                'environment' => app()->environment(),
            ]);
        } catch (\Exception $e) {
            // If we can't store the full response, store what we can
            SitemapStatusCheck::create([
                'route_id' => $route->id,
                'status_code' => $result['status_code'],
                'response_time' => $result['response_time'],
                'response_size' => $result['response_size'] ?? 0,
                'headers' => [],
                'body_preview' => '',
                'checked_at' => now(),
                'environment' => app()->environment(),
            ]);
        }
    }

    /**
     * Store error information.
     */
    protected function storeError(SitemapRoute $route, \Exception $exception): void
    {
        SitemapError::create([
            'route_id' => $route->id,
            'error_type' => $this->getErrorType($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'stack_trace' => $exception->getTraceAsString(),
            'occurred_at' => now(),
            'environment' => app()->environment(),
            'metadata' => [
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ]);
    }

    /**
     * Get error type from exception.
     */
    protected function getErrorType(\Exception $exception): string
    {
        if ($exception instanceof RequestException) {
            $response = $exception->getResponse();
            if ($response) {
                $statusCode = $response->getStatusCode();
                
                if ($statusCode >= 500) {
                    return 'server_error';
                }
                
                if ($statusCode === 404) {
                    return 'not_found';
                }
                
                if ($statusCode === 403) {
                    return 'forbidden';
                }
                
                return 'http_error';
            }
            
            if (str_contains($exception->getMessage(), 'timeout')) {
                return 'timeout';
            }
            
            if (str_contains($exception->getMessage(), 'SSL')) {
                return 'ssl_error';
            }
            
            return 'connection_failed';
        }
        
        return 'unknown';
    }

    /**
     * Update route status.
     */
    protected function updateRouteStatus(SitemapRoute $route, array $result): void
    {
        $route->update([
            'last_checked_at' => now(),
            'last_status_code' => $result['status_code'],
            'last_response_time' => $result['response_time'],
            'last_error_message' => null,
        ]);
    }

    /**
     * Update route error status.
     */
    protected function updateRouteErrorStatus(SitemapRoute $route, \Exception $exception): void
    {
        $route->increment('error_count');
        $route->update([
            'last_checked_at' => now(),
            'last_error_message' => $exception->getMessage(),
        ]);
    }

    /**
     * Get recent status checks for a route.
     */
    public function getRecentStatusChecks(SitemapRoute $route, int $limit = 10): Collection
    {
        return $route->statusChecks()
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent errors for a route.
     */
    public function getRecentErrors(SitemapRoute $route, int $limit = 10): Collection
    {
        return $route->errors()
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Test a specific route by URI.
     */
    public function testRoute(string $uri): array
    {
        try {
            $url = url($uri);
            $startTime = microtime(true);
            
            $response = $this->client->get($url);
            
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $statusCode = $response->getStatusCode();
            $responseSize = strlen($response->getBody()->getContents());
            
            $result = [
                'success' => true,
                'uri' => $uri,
                'url' => $url,
                'status_code' => $statusCode,
                'response_time' => round($responseTime, 2),
                'response_size' => $responseSize,
                'headers' => $response->getHeaders(),
                'is_healthy' => in_array($statusCode, $this->config['acceptable_status_codes']),
            ];
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'uri' => $uri,
                'url' => $url ?? url($uri),
                'status_code' => 0,
                'response_time' => 0,
                'error_message' => $e->getMessage(),
                'error_type' => $this->getErrorType($e),
                'is_healthy' => false,
            ];
        }
    }

    /**
     * Get overall statistics.
     */
    public function getStatistics(?string $environment = null): array
    {
        $query = SitemapStatusCheck::query();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        $total = $query->count();
        $successful = $query->clone()->where('status_code', '>=', 200)->where('status_code', '<', 300)->count();
        $failed = $query->clone()->where('status_code', '>=', 400)->count();
        
        $avgResponseTime = $query->clone()
            ->whereNotNull('response_time')
            ->avg('response_time');

        return [
            'total_checks' => $total,
            'successful_checks' => $successful,
            'failed_checks' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'avg_response_time' => round($avgResponseTime ?? 0, 3),
        ];
    }
} 