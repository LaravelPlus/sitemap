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

class StatusCheckService
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->config = config('sitemap.status_check');
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
    }

    /**
     * Check status of all routes for an environment.
     */
    public function checkAllRoutes(string $environment = null): array
    {
        $routes = SitemapRoute::active();
        
        if ($environment) {
            $routes->forEnvironment($environment);
        }

        $routes = $routes->get();
        
        return $this->checkRoutes($routes);
    }

    /**
     * Check status of specific routes.
     */
    public function checkRoutes(Collection $routes): array
    {
        $results = [
            'total' => $routes->count(),
            'successful' => 0,
            'failed' => 0,
            'errors' => 0,
            'details' => [],
        ];

        // Process routes in batches for concurrent requests
        $batches = $routes->chunk($this->config['concurrent_requests']);
        
        foreach ($batches as $batch) {
            $batchResults = $this->checkRouteBatch($batch);
            
            $results['successful'] += $batchResults['successful'];
            $results['failed'] += $batchResults['failed'];
            $results['errors'] += $batchResults['errors'];
            $results['details'] = array_merge($results['details'], $batchResults['details']);
        }

        return $results;
    }

    /**
     * Check a batch of routes concurrently.
     */
    protected function checkRouteBatch(Collection $routes): array
    {
        $promises = [];
        $routeMap = [];

        foreach ($routes as $route) {
            $url = $route->full_url;
            $promise = $this->client->getAsync($url);
            $promises[$url] = $promise;
            $routeMap[$url] = $route;
        }

        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => 0,
            'details' => [],
        ];

        try {
            $responses = Promise\Utils::settle($promises)->wait();
            
            foreach ($responses as $url => $response) {
                $route = $routeMap[$url];
                $result = $this->processResponse($route, $response);
                
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
        }

        return $results;
    }

    /**
     * Process a single response.
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
            'error_message' => null,
        ];

        if ($response['state'] === Promise\Promise::FULFILLED) {
            $httpResponse = $response['value'];
            $statusCode = $httpResponse->getStatusCode();
            $responseTime = $this->calculateResponseTime($httpResponse);
            $responseSize = strlen($httpResponse->getBody()->getContents());
            
            $result['status_code'] = $statusCode;
            $result['response_time'] = $responseTime;
            $result['success'] = in_array($statusCode, $this->config['acceptable_status_codes']);
            
            // Store status check
            $this->storeStatusCheck($route, $result, $httpResponse);
            
            // Update route with latest status
            $this->updateRouteStatus($route, $result);
            
        } else {
            $exception = $response['reason'];
            $result['error'] = true;
            $result['error_message'] = $exception->getMessage();
            
            // Store error
            $this->storeError($route, $exception);
            
            // Update route error count
            $this->updateRouteErrorStatus($route, $exception);
        }

        return $result;
    }

    /**
     * Calculate response time from headers.
     */
    protected function calculateResponseTime($response): float
    {
        $headers = $response->getHeaders();
        
        if (isset($headers['X-Runtime'])) {
            return (float) $headers['X-Runtime'][0];
        }
        
        // Fallback to microtime calculation
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Store status check result.
     */
    protected function storeStatusCheck(SitemapRoute $route, array $result, $response): void
    {
        SitemapStatusCheck::create([
            'route_id' => $route->id,
            'status_code' => $result['status_code'],
            'response_time' => $result['response_time'],
            'response_size' => strlen($response->getBody()->getContents()),
            'headers' => $response->getHeaders(),
            'body_preview' => substr($response->getBody()->getContents(), 0, 500),
            'checked_at' => now(),
            'environment' => app()->environment(),
        ]);
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
     * Get overall statistics.
     */
    public function getStatistics(string $environment = null): array
    {
        $query = SitemapStatusCheck::query();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        $total = $query->count();
        $successful = $query->clone()->successful()->count();
        $failed = $query->clone()->failed()->count();
        
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