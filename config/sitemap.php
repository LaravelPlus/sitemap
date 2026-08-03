<?php

declare(strict_types=1);

return [
    /*
     * Where the sitemap is served. Set to null to register no route and wire
     * the map into an existing controller yourself.
     */
    'route' => env('SITEMAP_ROUTE', 'sitemap.xml'),

    /*
     * Any route carrying one of these middleware is private and never listed —
     * a page a guest can't open is a page a crawler can't open. This does the
     * heavy lifting; the prefix list below only mops up guest-reachable routes
     * that still don't belong in search (utility, legal noise, dev helpers).
     */
    'private_middleware' => ['auth', 'auth.session', 'auth.basic', 'verified', 'signed', 'password.confirm', 'can', 'role', 'permission'],

    /*
     * URI prefixes kept out of the map: auth, admin, utility and API surfaces.
     * Matched on whole path segments, so `api` excludes `api/x` but not
     * `apidocs`. Routes with parameters (`jobs/{slug}`) are always skipped —
     * register those through Sitemap::add().
     */
    'exclude' => [
        'login', 'logout', 'register', 'password', 'forgot-password',
        'reset-password', 'confirm-password', 'verify-email', 'email',
        'two-factor', 'two-factor-challenge',
        'admin', 'dashboard', 'settings', 'profile', 'user', 'account',
        'api', 'up', 'storage', '_ignition', '_debugbar',
        'locale', 'cookie-settings',
    ],
];
