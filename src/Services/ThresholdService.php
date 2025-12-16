<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use LaravelPlus\Sitemap\Models\SitemapRoute;

final class ThresholdService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('sitemap.thresholds', [
            'enabled' => true,
            'response_time' => [
                'warning' => 1000,
                'critical' => 2000,
                'alert' => 5000,
            ],
            'error_rate' => [
                'warning' => 5,
                'critical' => 10,
                'alert' => 20,
            ],
            'status_code' => [
                'warning' => [404, 429, 500, 502, 503, 504],
                'critical' => [500, 502, 503, 504],
            ],
            'monitoring' => [
                'all_routes' => true,
                'specific_routes' => [],
                'prefixes' => [],
            ],
            'notifications' => [
                'enabled' => true,
                'channels' => ['log' => true],
                'recipients' => [],
            ],
        ]);
    }

    /**
     * Check thresholds for a single route status check.
     */
    public function checkRouteThresholds(SitemapRoute $route, array $statusCheck): array
    {
        if (!$this->config['enabled']) {
            return [];
        }

        $alerts = [];

        // Check response time thresholds
        if (isset($statusCheck['response_time']) && $statusCheck['response_time'] > 0) {
            $responseTime = $statusCheck['response_time'];
            $thresholds = $this->getRouteThresholds($route, 'response_time');

            if ($responseTime >= $thresholds['alert']) {
                $alerts[] = $this->createAlert($route, 'response_time', 'alert', $responseTime, $thresholds['alert']);
            } elseif ($responseTime >= $thresholds['critical']) {
                $alerts[] = $this->createAlert($route, 'response_time', 'critical', $responseTime, $thresholds['critical']);
            } elseif ($responseTime >= $thresholds['warning']) {
                $alerts[] = $this->createAlert($route, 'response_time', 'warning', $responseTime, $thresholds['warning']);
            }
        }

        // Check status code thresholds
        if (isset($statusCheck['status_code'])) {
            $statusCode = $statusCheck['status_code'];

            if (in_array($statusCode, $this->config['status_code']['critical'])) {
                $alerts[] = $this->createAlert($route, 'status_code', 'critical', $statusCode, 'critical status code');
            } elseif (in_array($statusCode, $this->config['status_code']['warning'])) {
                $alerts[] = $this->createAlert($route, 'status_code', 'warning', $statusCode, 'warning status code');
            }
        }

        // Send notifications for alerts
        if (!empty($alerts)) {
            $this->sendThresholdNotifications($alerts);
        }

        return $alerts;
    }

    /**
     * Check thresholds for multiple routes.
     */
    public function checkBulkThresholds(Collection $routes): array
    {
        if (!$this->config['enabled']) {
            return [];
        }

        $alerts = [];
        $routeGroups = $this->groupRoutesByPrefix($routes);

        foreach ($routeGroups as $prefix => $routeGroup) {
            $groupAlerts = $this->checkGroupThresholds($prefix, $routeGroup);
            $alerts = array_merge($alerts, $groupAlerts);
        }

        // Send notifications for bulk alerts
        if (!empty($alerts)) {
            $this->sendThresholdNotifications($alerts);
        }

        return $alerts;
    }

    /**
     * Check thresholds for a group of routes (by prefix).
     */
    private function checkGroupThresholds(string $prefix, Collection $routes): array
    {
        $alerts = [];
        $totalRoutes = $routes->count();
        $errorCount = 0;
        $totalResponseTime = 0;
        $responseTimeCount = 0;

        foreach ($routes as $route) {
            $latestCheck = $route->statusChecks()->latest()->first();

            if ($latestCheck) {
                if ($latestCheck->status_code >= 400) {
                    $errorCount++;
                }

                if ($latestCheck->response_time > 0) {
                    $totalResponseTime += $latestCheck->response_time;
                    $responseTimeCount++;
                }
            }
        }

        // Calculate error rate
        if ($totalRoutes > 0) {
            $errorRate = ($errorCount / $totalRoutes) * 100;
            $thresholds = $this->getPrefixThresholds($prefix, 'error_rate');

            if ($errorRate >= $thresholds['alert']) {
                $alerts[] = $this->createGroupAlert($prefix, 'error_rate', 'alert', $errorRate, $thresholds['alert'], $totalRoutes);
            } elseif ($errorRate >= $thresholds['critical']) {
                $alerts[] = $this->createGroupAlert($prefix, 'error_rate', 'critical', $errorRate, $thresholds['critical'], $totalRoutes);
            } elseif ($errorRate >= $thresholds['warning']) {
                $alerts[] = $this->createGroupAlert($prefix, 'error_rate', 'warning', $errorRate, $thresholds['warning'], $totalRoutes);
            }
        }

        // Calculate average response time
        if ($responseTimeCount > 0) {
            $avgResponseTime = $totalResponseTime / $responseTimeCount;
            $thresholds = $this->getPrefixThresholds($prefix, 'response_time');

            if ($avgResponseTime >= $thresholds['alert']) {
                $alerts[] = $this->createGroupAlert($prefix, 'response_time', 'alert', $avgResponseTime, $thresholds['alert'], $totalRoutes);
            } elseif ($avgResponseTime >= $thresholds['critical']) {
                $alerts[] = $this->createGroupAlert($prefix, 'response_time', 'critical', $avgResponseTime, $thresholds['critical'], $totalRoutes);
            } elseif ($avgResponseTime >= $thresholds['warning']) {
                $alerts[] = $this->createGroupAlert($prefix, 'response_time', 'warning', $avgResponseTime, $thresholds['warning'], $totalRoutes);
            }
        }

        return $alerts;
    }

    /**
     * Get thresholds for a specific route.
     */
    private function getRouteThresholds(SitemapRoute $route, string $type): array
    {
        $defaultThresholds = $this->config[$type] ?? [];

        // Check specific route thresholds
        $specificRoutes = $this->config['monitoring']['specific_routes'] ?? [];
        if (isset($specificRoutes[$route->uri])) {
            return array_merge($defaultThresholds, $specificRoutes[$route->uri]);
        }

        // Check prefix thresholds
        foreach ($this->config['monitoring']['prefixes'] ?? [] as $prefix => $thresholds) {
            if (str_starts_with($route->uri, $prefix)) {
                return array_merge($defaultThresholds, $thresholds);
            }
        }

        return $defaultThresholds;
    }

    /**
     * Get thresholds for a prefix.
     */
    private function getPrefixThresholds(string $prefix, string $type): array
    {
        $defaultThresholds = $this->config[$type] ?? [];
        $prefixThresholds = $this->config['monitoring']['prefixes'][$prefix] ?? [];

        return array_merge($defaultThresholds, $prefixThresholds);
    }

    /**
     * Group routes by prefix for bulk monitoring.
     */
    private function groupRoutesByPrefix(Collection $routes): array
    {
        $groups = [];

        foreach ($routes as $route) {
            $prefix = $this->getRoutePrefix($route->uri);
            if (!isset($groups[$prefix])) {
                $groups[$prefix] = collect();
            }
            $groups[$prefix]->push($route);
        }

        return $groups;
    }

    /**
     * Get the prefix for a route URI.
     */
    private function getRoutePrefix(string $uri): string
    {
        $parts = explode('/', mb_trim($uri, '/'));

        return $parts[0] ?? 'default';
    }

    /**
     * Create an alert for a single route.
     */
    private function createAlert(SitemapRoute $route, string $type, string $level, $value, $threshold): array
    {
        return [
            'type' => 'route',
            'level' => $level,
            'metric' => $type,
            'route_id' => $route->id,
            'route_uri' => $route->uri,
            'value' => $value,
            'threshold' => $threshold,
            'timestamp' => now(),
            'message' => "Route {$route->uri} {$type} threshold exceeded: {$value} (threshold: {$threshold})",
        ];
    }

    /**
     * Create an alert for a route group.
     */
    private function createGroupAlert(string $prefix, string $type, string $level, $value, $threshold, int $routeCount): array
    {
        return [
            'type' => 'group',
            'level' => $level,
            'metric' => $type,
            'prefix' => $prefix,
            'value' => $value,
            'threshold' => $threshold,
            'route_count' => $routeCount,
            'timestamp' => now(),
            'message' => "Route group '{$prefix}' {$type} threshold exceeded: {$value} (threshold: {$threshold}) - {$routeCount} routes affected",
        ];
    }

    /**
     * Send threshold notifications.
     */
    private function sendThresholdNotifications(array $alerts): void
    {
        if (!$this->config['notifications']['enabled']) {
            return;
        }

        foreach ($alerts as $alert) {
            // Log alerts
            if ($this->config['notifications']['channels']['log'] ?? false) {
                Log::warning('Sitemap threshold alert', $alert);
            }

            // Email notifications
            if ($this->config['notifications']['channels']['email'] ?? false) {
                $this->sendEmailNotification($alert);
            }

            // Slack notifications
            if ($this->config['notifications']['channels']['slack'] ?? false) {
                $this->sendSlackNotification($alert);
            }

            // Webhook notifications
            if ($this->config['notifications']['channels']['webhook'] ?? false) {
                $this->sendWebhookNotification($alert);
            }
        }
    }

    /**
     * Send email notification.
     */
    private function sendEmailNotification(array $alert): void
    {
        // Implementation for email notifications
        // This would use Laravel's notification system
    }

    /**
     * Send Slack notification.
     */
    private function sendSlackNotification(array $alert): void
    {
        // Implementation for Slack notifications
        // This would use Laravel's notification system
    }

    /**
     * Send webhook notification.
     */
    private function sendWebhookNotification(array $alert): void
    {
        // Implementation for webhook notifications
        // This would use Laravel's HTTP client
    }
}
