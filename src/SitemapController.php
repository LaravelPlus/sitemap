<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap;

use Illuminate\Http\Response;

/**
 * Serves the map. A controller class rather than a route Closure: `route:cache`
 * serializes every route action, and a Closure declared inside a service
 * provider captures `$this` — so caching would walk the provider into the whole
 * container and blow the stack.
 */
final class SitemapController
{
    public function __invoke(Sitemap $sitemap): Response
    {
        return $sitemap->response();
    }
}
