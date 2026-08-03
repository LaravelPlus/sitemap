<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap;

use Illuminate\Http\Response;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

/**
 * Builds the public XML sitemap from the application's own registered GET
 * routes, so a new marketing page is in the sitemap the moment it has a route.
 *
 * Dynamic pages (jobs, companies, posts) come from sources registered with
 * add(); pre-launch gates narrow the whole map with only().
 */
final class Sitemap
{
    /** @var list<callable(): iterable<string|array{loc:string,lastmod?:string}>> */
    private array $sources = [];

    /** @var list<string>|callable(): ?iterable<string>|null URIs to restrict the map to, `null` = no restriction. */
    private $only = null;

    /**
     * Register a source of dynamic URLs. The callable is invoked per request,
     * not at boot, so it may query the database.
     *
     * @param  callable(): iterable<string|array{loc:string,lastmod?:string}>  $source
     */
    public function add(callable $source): self
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * Restrict the sitemap to these URIs (leading slash optional) and drop
     * everything else — route-derived and dynamic alike.
     *
     * Use it when a gate (waitlist, coming-soon) redirects most of the app:
     * a sitemap full of redirects wastes crawl budget and hides the one page
     * that actually answers 200.
     *
     * Pass a callable when the restriction depends on runtime state — it is
     * resolved per request, so flipping the gate needs no redeploy. Returning
     * null from it means "no restriction".
     *
     * @param  list<string>|callable(): ?iterable<string>  $uris
     */
    public function only(array|callable $uris): self
    {
        $this->only = $uris;

        return $this;
    }

    /**
     * @return list<array{loc:string,lastmod:string|null}>
     */
    public function entries(): array
    {
        $entries = [];

        foreach ($this->routeUris() as $uri) {
            $entries[] = ['loc' => url($uri), 'lastmod' => null];
        }

        foreach ($this->sources as $source) {
            foreach ($source() as $item) {
                $item = is_array($item) ? $item : ['loc' => $item];
                $entries[] = [
                    'loc' => str_starts_with($item['loc'], 'http') ? $item['loc'] : url($item['loc']),
                    'lastmod' => $item['lastmod'] ?? null,
                ];
            }
        }

        $only = is_callable($this->only) ? ($this->only)() : $this->only;

        if ($only !== null) {
            $allowed = array_map(static fn (string $uri): string => '/'.ltrim($uri, '/'), [...$only]);
            $entries = array_filter($entries, static fn (array $e): bool => in_array(
                '/'.ltrim((string) parse_url($e['loc'], PHP_URL_PATH), '/'),
                $allowed,
                true,
            ));
        }

        // Same page reachable via two routes must not be submitted twice.
        return array_values(array_column(array_reverse($entries), null, 'loc'));
    }

    public function response(): Response
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->entries() as $entry) {
            $body .= '<url><loc>'.htmlspecialchars($entry['loc'], ENT_XML1).'</loc>'
                .($entry['lastmod'] !== null ? '<lastmod>'.htmlspecialchars($entry['lastmod'], ENT_XML1).'</lastmod>' : '')
                .'</url>';
        }

        return response($body.'</urlset>', 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Every GET route with the verdict on why it is in the map or out of it.
     * `sitemap:routes` renders this — "why is my page missing" is the whole
     * reason a sitemap needs a CLI at all.
     *
     * @return list<array{uri:string,included:bool,reason:string}>
     */
    public function auditRoutes(): array
    {
        $exclude = (array) config('sitemap.exclude', []);
        $private = (array) config('sitemap.private_middleware', []);
        $audit = [];

        foreach (Route::getRoutes() as $route) {
            /** @var RoutingRoute $route */
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = '/'.ltrim($route->uri(), '/');
            $blocked = array_intersect($private, $this->middlewareNames($route));

            $audit[$uri] = match (true) {
                $uri === '/' => ['uri' => $uri, 'included' => true, 'reason' => 'public route'],
                str_contains($uri, '{') => ['uri' => $uri, 'included' => false, 'reason' => 'has parameters — register real URLs with Sitemap::add()'],
                // sitemap.xml, robots.txt, feeds: not pages, never listed.
                (bool) preg_match('/\.(xml|txt|json|rss|atom|ics|pdf|css|js)$/', $uri) => ['uri' => $uri, 'included' => false, 'reason' => 'not an HTML page'],
                ($prefix = $this->excludedBy($uri, $exclude)) !== null => ['uri' => $uri, 'included' => false, 'reason' => "excluded by config prefix '{$prefix}'"],
                // A route a guest can't reach is a route a crawler can't reach.
                // This is the load-bearing filter: an exclude list of URI
                // prefixes always drifts behind the app's private surface.
                $blocked !== [] => ['uri' => $uri, 'included' => false, 'reason' => 'private middleware: '.implode(', ', $blocked)],
                default => ['uri' => $uri, 'included' => true, 'reason' => 'public route'],
            };
        }

        return array_values($audit);
    }

    /** @return list<string> */
    private function routeUris(): array
    {
        return array_column(array_filter($this->auditRoutes(), static fn (array $r): bool => $r['included']), 'uri');
    }

    /**
     * Middleware names without their parameters, so `role:admin` matches `role`.
     *
     * @return list<string>
     */
    private function middlewareNames(RoutingRoute $route): array
    {
        return array_values(array_map(
            static fn (mixed $m): string => is_string($m) ? explode(':', $m, 2)[0] : '',
            $route->gatherMiddleware(),
        ));
    }

    /**
     * The configured prefix that excludes this URI, or null. Matched on whole
     * segments, so `api` excludes `api/x` but not `apidocs`.
     *
     * @param  list<string>  $exclude
     */
    private function excludedBy(string $uri, array $exclude): ?string
    {
        $path = ltrim($uri, '/');

        foreach ($exclude as $prefix) {
            $prefix = trim($prefix, '/');

            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $prefix;
            }
        }

        return null;
    }
}
