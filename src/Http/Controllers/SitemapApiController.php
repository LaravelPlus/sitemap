<?php

namespace LaravelPlus\Sitemap\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelPlus\Sitemap\Services\SitemapService;
use LaravelPlus\Sitemap\Models\SitemapRoute;
use LaravelPlus\Sitemap\Http\Requests\UpdatePriorityRequest;
use LaravelPlus\Sitemap\Http\Requests\UpdateChangeFreqRequest;

class SitemapApiController extends Controller
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        $stats = $this->sitemapService->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get routes with pagination and filtering.
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

        if ($request->has('search')) {
            $query->where('uri', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
        }

        $routes = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $routes,
        ]);
    }

    /**
     * Get specific route details.
     */
    public function route(SitemapRoute $route)
    {
        $route->load(['statusChecks' => function ($query) {
            $query->orderBy('checked_at', 'desc')->limit(10);
        }, 'errors' => function ($query) {
            $query->orderBy('occurred_at', 'desc')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $route,
        ]);
    }

    /**
     * Update route priority.
     */
    public function updatePriority(UpdatePriorityRequest $request, SitemapRoute $route)
    {
        $success = $this->sitemapService->updateRoutePriority($route->id, $request->priority);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Route priority updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update route priority',
        ], 400);
    }

    /**
     * Update route change frequency.
     */
    public function updateChangeFreq(UpdateChangeFreqRequest $request, SitemapRoute $route)
    {
        $success = $this->sitemapService->updateRouteChangeFreq($route->id, $request->changefreq);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Route change frequency updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update route change frequency',
        ], 400);
    }

    /**
     * Toggle route active status.
     */
    public function toggleRoute(SitemapRoute $route)
    {
        $success = $this->sitemapService->toggleRouteStatus($route->id);
        
        if ($success) {
            $route->refresh();
            return response()->json([
                'success' => true,
                'message' => 'Route status toggled successfully',
                'data' => [
                    'is_active' => $route->is_active,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to toggle route status',
        ], 400);
    }

    /**
     * Discover routes.
     */
    public function discover(Request $request)
    {
        $result = $this->sitemapService->discoverAndStoreRoutes();
        
        return response()->json($result);
    }

    /**
     * Check status of routes.
     */
    public function checkStatus(Request $request)
    {
        $routeId = $request->get('route_id');
        
        if ($routeId) {
            $route = SitemapRoute::find($routeId);
            if (!$route) {
                return response()->json([
                    'success' => false,
                    'message' => 'Route not found',
                ], 404);
            }
            
            $result = $this->sitemapService->statusCheck->checkRoutes(collect([$route]));
        } else {
            $result = $this->sitemapService->checkAllRoutesStatus();
        }
        
        return response()->json($result);
    }

    /**
     * Generate sitemap.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xml,json,csv',
        ]);

        try {
            $sitemap = $this->sitemapService->generateSitemap($request->format);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'sitemap' => $sitemap,
                    'format' => $request->format,
                    'generated_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cleanup old data.
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30);
        
        $result = $this->sitemapService->cleanupOldData($days);
        
        return response()->json([
            'success' => true,
            'message' => 'Cleanup completed successfully',
            'data' => $result,
        ]);
    }
} 