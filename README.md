# laravelplus/sitemap

XML sitemap built from your app's own routes, plus whatever dynamic URLs you register.

```bash
composer require laravelplus/sitemap
```

That's it — `/sitemap.xml` is live. The provider is auto-discovered; there is no
route to add, no controller to write, no table to migrate.

## What lands in the map

Every registered `GET` route, minus:

- routes with URI parameters (`jobs/{slug}`) — those are templates, their real
  URLs come from `add()`
- routes carrying auth-ish middleware (`auth`, `verified`, `signed`, `role`, …)
  — a page a guest can't open is a page a crawler can't open. This is the filter
  that does the work; a hand-maintained URI list always drifts behind the app.
- URI prefixes listed in `config/sitemap.php`

## Multiple domains

A route declared with `Route::domain()` is listed on **its own** host, not on
`APP_URL`. On a multi-domain app that is the difference between a usable sitemap
and one that points crawlers at pages which 404:

```
in   https://example.com/pricing
in   https://p.example.com/            <- viewer domain, same `/` path
out  https://api.example.com/v1/user   private middleware: auth
```

`sitemap:routes` prints absolute URLs for the same reason — on a multi-domain
app the path alone is ambiguous.

## Dynamic URLs

`add()` takes a callable run **per request**, so it may hit the database, and it
returns an `iterable` — yield from a lazy query and a 50k-listing sitemap never
hydrates 50k models.

```php
// AppServiceProvider::boot()
app(Sitemap::class)->add(function (): iterable {
    foreach (Job::query()->active()->lazyById() as $job) {
        yield ['loc' => route('jobs.show', $job->slug), 'lastmod' => $job->updated_at?->toAtomString()];
    }
});
```

A plain string works too when there's no `lastmod`: `yield '/pricing';`

## Pre-launch gates

Behind a waitlist or coming-soon wall, most routes 302 elsewhere. A sitemap of
redirects burns crawl budget and buries the one page that answers 200:

```php
$sitemap->only(fn (): ?array => config('wishlist.enabled')
    ? ['early-access', 'promo', 'employers/signup']
    : null);
```

Array or callable; the callable is resolved per request, so flipping the gate
needs no redeploy. `null` means no restriction.

## Config

```bash
php artisan vendor:publish --tag=sitemap-config
```

- `route` — where it's served (default `sitemap.xml`); `null` registers nothing
  and leaves you to call `app(Sitemap::class)->response()` yourself
- `private_middleware` — middleware names that mark a route non-public
- `exclude` — URI prefixes, matched on whole segments (`api` excludes `api/x`,
  not `apidocs`)

## Deliberately not here

No database tables, no dashboard, no status-checking crawler, no
changefreq/priority (Google ignores both), no sitemap index. Add a sitemap index
when you cross 50,000 URLs or 50 MB — the limits where it starts to matter.
