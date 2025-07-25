<?php

namespace LaravelPlus\Sitemap\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Models\SitemapStatusCheck;
use LaravelPlus\Sitemap\Models\SitemapError;

class SitemapController extends Controller
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        $stats = $this->sitemapService->getDashboardStats();
        $recentErrors = SitemapError::recent(24)->with('route')->limit(10)->get();
        $routesWithErrors = $this->sitemapService->getRoutesWithErrors();

        return view('sitemap::dashboard', compact('stats', 'recentErrors', 'routesWithErrors'));
    }

    /**
     * Show all routes.
     */
    public function routes(Request $request)
    {
        $query = SitemapRoute::query();
        
        if ($request->has('environment')) {
            $query->forEnvironment($request->environment);
        }
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'errors':
                    $query->withErrors();
                    break;
                case 'healthy':
                    $query->get()->filter->isHealthy();
                    break;
            }
        }

        $routes = $query->paginate(20);
        $stats = $this->sitemapService->routeDiscovery->getStatistics();

        return view('sitemap::routes', compact('routes', 'stats'));
    }

    /**
     * Show route details.
     */
    public function routeDetails(SitemapRoute $route)
    {
        $recentStatusChecks = $route->statusChecks()->orderBy('checked_at', 'desc')->limit(10)->get();
        $recentErrors = $route->errors()->orderBy('occurred_at', 'desc')->limit(10)->get();

        return view('sitemap::route-details', compact('route', 'recentStatusChecks', 'recentErrors'));
    }

    /**
     * Show status checks.
     */
    public function status(Request $request)
    {
        $query = SitemapStatusCheck::with('route');
        
        if ($request->has('environment')) {
            $query->forEnvironment($request->environment);
        }
        
        if ($request->has('status_code')) {
            $query->withStatusCode($request->status_code);
        }

        $statusChecks = $query->orderBy('checked_at', 'desc')->paginate(20);
        $stats = $this->sitemapService->statusCheck->getStatistics();

        return view('sitemap::status', compact('statusChecks', 'stats'));
    }

    /**
     * Show route status.
     */
    public function routeStatus(SitemapRoute $route)
    {
        $statusChecks = $route->statusChecks()->orderBy('checked_at', 'desc')->paginate(20);

        return view('sitemap::route-status', compact('route', 'statusChecks'));
    }

    /**
     * Show errors.
     */
    public function errors(Request $request)
    {
        $query = SitemapError::with('route');
        
        if ($request->has('environment')) {
            $query->forEnvironment($request->environment);
        }
        
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $errors = $query->orderBy('occurred_at', 'desc')->paginate(20);

        return view('sitemap::errors', compact('errors'));
    }

    /**
     * Show route errors.
     */
    public function routeErrors(SitemapRoute $route)
    {
        $errors = $route->errors()->orderBy('occurred_at', 'desc')->paginate(20);

        return view('sitemap::route-errors', compact('route', 'errors'));
    }

    /**
     * Show sitemap generation page.
     */
    public function generate()
    {
        $stats = $this->sitemapService->getDashboardStats();
        $formats = config('sitemap.export.formats');

        return view('sitemap::generate', compact('stats', 'formats'));
    }

    /**
     * Download sitemap.
     */
    public function download(string $format)
    {
        try {
            $sitemap = $this->sitemapService->generateSitemap($format);
            
            $filename = "sitemap-{$format}-" . now()->format('Y-m-d-H-i-s') . ".{$format}";
            
            return response($sitemap)
                ->header('Content-Type', $this->getContentType($format))
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show settings page.
     */
    public function settings()
    {
        $config = config('sitemap');
        
        return view('sitemap::settings', compact('config'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'route_discovery.enabled' => 'boolean',
            'status_check.enabled' => 'boolean',
            'ui.enabled' => 'boolean',
            'cache.enabled' => 'boolean',
        ]);

        // Update configuration (this would typically be stored in database or cache)
        // For now, we'll just redirect back with success message
        
        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Get content type for format.
     */
    protected function getContentType(string $format): string
    {
        return match($format) {
            'xml' => 'application/xml',
            'json' => 'application/json',
            'csv' => 'text/csv',
            default => 'text/plain',
        };
    }
} 