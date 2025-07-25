<?php

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Facades\Route;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use Illuminate\Support\Collection;

class RouteDiscoveryService
{
    /**
     * Discover all GET routes in the application.
     */
    public function discoverRoutes(): Collection
    {
        $routes = collect();
        $config = config('sitemap.route_discovery', [
            'methods' => ['GET', 'HEAD'],
            'exclude_patterns' => [
                'admin/*',
                'api/*',
                '_debugbar/*',
                'telescope/*',
                'horizon/*',
                'log-viewer/*',
            ],
            'include_patterns' => [],
            'max_routes' => 1000,
        ]);
        
        $allRoutes = Route::getRoutes();
        \Log::info('Sitemap route discovery started', [
            'total_routes_found' => count($allRoutes),
            'config' => $config,
        ]);
        
        $includedCount = 0;
        $excludedCount = 0;
        
        foreach ($allRoutes as $route) {
            if (!$this->shouldIncludeRoute($route, $config)) {
                $excludedCount++;
                \Log::debug('Sitemap route excluded', [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                ]);
                continue;
            }

            $includedCount++;
            $routes->push($this->createRouteData($route));
        }
        
        \Log::info('Sitemap route discovery completed', [
            'total_routes' => count($allRoutes),
            'included_routes' => $includedCount,
            'excluded_routes' => $excludedCount,
            'final_routes' => $routes->count(),
        ]);

        return $routes->unique('uri');
    }

    /**
     * Check if a route should be included based on configuration.
     */
    protected function shouldIncludeRoute($route, array $config): bool
    {
        try {
            $methods = $route->methods();
            $uri = $route->uri();

            // Check if route uses allowed methods
            if (!isset($config['methods']) || !array_intersect($methods, $config['methods'])) {
                return false;
            }

            // Check exclude patterns
            if (isset($config['exclude_patterns'])) {
                foreach ($config['exclude_patterns'] as $pattern) {
                    if ($this->matchesPattern($uri, $pattern)) {
                        return false;
                    }
                }
            }

            // Check include patterns (if any are specified)
            if (isset($config['include_patterns']) && !empty($config['include_patterns'])) {
                $included = false;
                foreach ($config['include_patterns'] as $pattern) {
                    if ($this->matchesPattern($uri, $pattern)) {
                        $included = true;
                        break;
                    }
                }
                if (!$included) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Sitemap route inclusion check failed', [
                'error' => $e->getMessage(),
                'uri' => $route->uri() ?? 'unknown',
                'config' => $config,
            ]);
            return false;
        }
    }

    /**
     * Check if URI matches a pattern.
     */
    protected function matchesPattern(string $uri, string $pattern): bool
    {
        // Convert wildcard pattern to regex
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return preg_match('/^' . $pattern . '$/', $uri);
    }

    /**
     * Create route data from a route instance.
     */
    protected function createRouteData($route): array
    {
        try {
            $action = $route->getAction();
            $controller = $action['controller'] ?? null;
            
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'methods' => $route->methods(),
                'controller' => $controller,
                'action' => $this->extractAction($controller),
                'middleware' => $route->middleware(),
                'domain' => $route->getDomain(),
                'is_active' => true,
                'priority' => $this->calculatePriority($route),
                'changefreq' => $this->calculateChangeFreq($route),
                'environment' => app()->environment(),
                'metadata' => [
                    'parameters' => $route->parameterNames(),
                    'compiled' => $route->getCompiled(),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Sitemap route data creation failed', [
                'error' => $e->getMessage(),
                'uri' => $route->uri() ?? 'unknown',
            ]);
            
            // Return minimal route data
            return [
                'uri' => $route->uri() ?? 'unknown',
                'name' => $route->getName(),
                'methods' => ['GET'],
                'controller' => null,
                'action' => null,
                'middleware' => [],
                'domain' => null,
                'is_active' => true,
                'priority' => 0.5,
                'changefreq' => 'weekly',
                'environment' => app()->environment(),
                'metadata' => [],
            ];
        }
    }

    /**
     * Extract action name from controller.
     */
    protected function extractAction(?string $controller): ?string
    {
        if (!$controller) {
            return null;
        }

        if (str_contains($controller, '@')) {
            return explode('@', $controller)[1];
        }

        if (str_contains($controller, '::')) {
            return explode('::', $controller)[1];
        }

        return null;
    }

    /**
     * Calculate priority based on route characteristics.
     */
    protected function calculatePriority($route): float
    {
        $uri = $route->uri();
        $priority = 0.5;

        // Homepage gets highest priority
        if ($uri === '/') {
            return 1.0;
        }

        // Static pages get higher priority
        if (!str_contains($uri, '{')) {
            $priority += 0.2;
        }

        // Shorter URLs get higher priority
        $priority += max(0, (10 - strlen($uri)) * 0.01);

        // Named routes get slightly higher priority
        if ($route->getName()) {
            $priority += 0.1;
        }

        return min(1.0, $priority);
    }

    /**
     * Calculate change frequency based on route characteristics.
     */
    protected function calculateChangeFreq($route): string
    {
        $uri = $route->uri();

        // Static pages change less frequently
        if (!str_contains($uri, '{')) {
            return 'monthly';
        }

        // Dynamic pages change more frequently
        if (str_contains($uri, 'blog') || str_contains($uri, 'news')) {
            return 'weekly';
        }

        if (str_contains($uri, 'products') || str_contains($uri, 'shop')) {
            return 'daily';
        }

        return 'weekly';
    }
} 