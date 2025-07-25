<?php

namespace LaravelPlus\Sitemap\Services;

use Illuminate\Support\Facades\Route;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use Illuminate\Support\Collection;

class RouteDiscoveryService
{
    /**
     * Discover all GET routes from the application.
     */
    public function discoverRoutes(): Collection
    {
        $routes = collect();
        $config = config('sitemap.route_discovery');
        
        foreach (Route::getRoutes() as $route) {
            if (!$this->shouldIncludeRoute($route, $config)) {
                continue;
            }

            $routes->push($this->createRouteData($route));
        }

        return $routes->unique('uri');
    }

    /**
     * Check if a route should be included based on configuration.
     */
    protected function shouldIncludeRoute($route, array $config): bool
    {
        $methods = $route->methods();
        $uri = $route->uri();

        // Check if route uses allowed methods
        if (!array_intersect($methods, $config['methods'])) {
            return false;
        }

        // Check exclude patterns
        foreach ($config['exclude_patterns'] as $pattern) {
            if ($this->matchesPattern($uri, $pattern)) {
                return false;
            }
        }

        // Check include patterns (if any are specified)
        if (!empty($config['include_patterns'])) {
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
    }

    /**
     * Check if URI matches a pattern.
     */
    protected function matchesPattern(string $uri, string $pattern): bool
    {
        $pattern = str_replace('*', '.*', $pattern);
        return preg_match('/^' . $pattern . '$/', $uri);
    }

    /**
     * Create route data from a route instance.
     */
    protected function createRouteData($route): array
    {
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

    /**
     * Store discovered routes in the database.
     */
    public function storeRoutes(Collection $routes): int
    {
        $stored = 0;
        $environment = app()->environment();

        foreach ($routes as $routeData) {
            $routeData['environment'] = $environment;
            
            $route = SitemapRoute::updateOrCreate(
                ['uri' => $routeData['uri'], 'environment' => $environment],
                $routeData
            );

            $stored++;
        }

        return $stored;
    }

    /**
     * Get routes for a specific environment.
     */
    public function getRoutesForEnvironment(string $environment): Collection
    {
        return SitemapRoute::forEnvironment($environment)->active()->get();
    }

    /**
     * Get routes with errors.
     */
    public function getRoutesWithErrors(string $environment = null): Collection
    {
        $query = SitemapRoute::withErrors();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        return $query->get();
    }

    /**
     * Get route statistics.
     */
    public function getStatistics(string $environment = null): array
    {
        $query = SitemapRoute::query();
        
        if ($environment) {
            $query->forEnvironment($environment);
        }

        $total = $query->count();
        $active = $query->clone()->active()->count();
        $withErrors = $query->clone()->withErrors()->count();
        $healthy = $query->clone()->get()->filter->isHealthy()->count();

        return [
            'total' => $total,
            'active' => $active,
            'with_errors' => $withErrors,
            'healthy' => $healthy,
            'error_rate' => $total > 0 ? round(($withErrors / $total) * 100, 2) : 0,
        ];
    }
} 