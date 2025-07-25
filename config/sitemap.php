<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the sitemap package.
    |
    */

    // Route discovery settings
    'route_discovery' => [
        'enabled' => env('SITEMAP_ROUTE_DISCOVERY_ENABLED', true),
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
        'max_routes' => env('SITEMAP_MAX_ROUTES', 1000),
    ],

    // Status checking settings
    'status_check' => [
        'enabled' => env('SITEMAP_STATUS_CHECK_ENABLED', true),
        'timeout' => env('SITEMAP_STATUS_CHECK_TIMEOUT', 30),
        'concurrent_requests' => env('SITEMAP_CONCURRENT_REQUESTS', 10),
        'retry_attempts' => env('SITEMAP_RETRY_ATTEMPTS', 3),
        'acceptable_status_codes' => [200, 201, 202, 301, 302, 404],
        'exclude_status_codes' => [500, 502, 503, 504],
    ],

    // Environment settings
    'environments' => [
        'production' => [
            'enabled' => true,
            'cache_duration' => 3600, // 1 hour
            'check_frequency' => 3600, // 1 hour
            'notify_on_error' => true,
        ],
        'staging' => [
            'enabled' => true,
            'cache_duration' => 1800, // 30 minutes
            'check_frequency' => 1800, // 30 minutes
            'notify_on_error' => true,
        ],
        'development' => [
            'enabled' => false,
            'cache_duration' => 300, // 5 minutes
            'check_frequency' => 300, // 5 minutes
            'notify_on_error' => false,
        ],
    ],

    // Database settings
    'database' => [
        'table_prefix' => 'sitemap_',
        'tables' => [
            'routes' => 'sitemap_routes',
            'status_checks' => 'sitemap_status_checks',
            'errors' => 'sitemap_errors',
        ],
    ],

    // Cache settings
    'cache' => [
        'enabled' => env('SITEMAP_CACHE_ENABLED', true),
        'prefix' => 'sitemap',
        'ttl' => env('SITEMAP_CACHE_TTL', 3600),
    ],

    // Notification settings
    'notifications' => [
        'enabled' => env('SITEMAP_NOTIFICATIONS_ENABLED', true),
        'channels' => [
            'mail' => env('SITEMAP_MAIL_NOTIFICATIONS', false),
            'slack' => env('SITEMAP_SLACK_NOTIFICATIONS', false),
            'webhook' => env('SITEMAP_WEBHOOK_NOTIFICATIONS', false),
        ],
        'recipients' => [
            'email' => env('SITEMAP_NOTIFICATION_EMAIL'),
            'slack_webhook' => env('SITEMAP_SLACK_WEBHOOK'),
            'webhook_url' => env('SITEMAP_WEBHOOK_URL'),
        ],
    ],

    // UI settings
    'ui' => [
        'enabled' => env('SITEMAP_UI_ENABLED', true),
        'route_prefix' => 'sitemap',
        'middleware' => ['web', 'auth'],
        'permissions' => [
            'view' => 'sitemap.view',
            'manage' => 'sitemap.manage',
            'admin' => 'sitemap.admin',
        ],
    ],

    // Export settings
    'export' => [
        'formats' => ['xml', 'json', 'csv'],
        'default_format' => 'xml',
        'include_lastmod' => true,
        'include_changefreq' => true,
        'include_priority' => true,
    ],
]; 