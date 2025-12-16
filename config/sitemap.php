<?php

declare(strict_types=1);

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
            '_debugbar/*',
            '_ignition/*',
            '_ignition/health-check',
            'telescope/*',
            'horizon/*',
            'log-viewer/*',
            'nova/*',
            'filament/*',
            'livewire/*',
            'broadcasting/*',
            'sanctum/*',
            'oauth/*',
            'passport/*',
            'vendor/*',
            'storage/*',
            'assets/*',
            'js/*',
            'css/*',
            'images/*',
            'fonts/*',
            'favicon.ico',
            'robots.txt',
            'sitemap.xml',
            'sitemap/*',
            'health',
            'up',
            'down',
            'artisan',
            'queue/*',
            'cache/*',
            'config/*',
            'route/*',
            'view/*',
            'clear/*',
            'optimize/*',
            'migrate/*',
            'seed/*',
            'test/*',
            'tests/*',
            'docs/*',
            'api-docs/*',
            'swagger/*',
            'graphql/*',
            'admin/*',
            'api/*',
            'webhook/*',
            'auth/*',
            'login',
            'logout',
            'register',
            'password/*',
            'email/*',
            'verification/*',
            'two-factor/*',
            'profile/*',
            'user/*',
            'users/*',
            'dashboard/*',
            'panel/*',
            'backend/*',
            'frontend/*',
            'dev/*',
            'development/*',
            'staging/*',
            'demo/*',
            'temp/*',
            'tmp/*',
            'logs/*',
            'backup/*',
            'export/*',
            'import/*',
            'upload/*',
            'download/*',
            'file/*',
            'media/*',
            'static/*',
            'build/*',
            'dist/*',
            'node_modules/*',
            'composer/*',
            'npm/*',
            'yarn/*',
            'webpack/*',
            'vite/*',
            'mix/*',
            'laravel-mix/*',
        ],
        'include_patterns' => [],
        'max_routes' => env('SITEMAP_MAX_ROUTES', 1000),
    ],

    // Status checking settings
    'status_check' => [
        'enabled' => env('SITEMAP_STATUS_CHECK_ENABLED', true),
        'bulk_check_enabled' => env('SITEMAP_BULK_CHECK_ENABLED', false), // Disabled by default to prevent server overload
        'timeout' => env('SITEMAP_STATUS_CHECK_TIMEOUT', 10), // Reduced from 30 to 10 seconds
        'concurrent_requests' => env('SITEMAP_CONCURRENT_REQUESTS', 3), // Reduced from 10 to 3
        'retry_attempts' => env('SITEMAP_RETRY_ATTEMPTS', 1), // Reduced from 3 to 1
        'acceptable_status_codes' => [200, 201, 202, 301, 302, 404],
        'exclude_status_codes' => [500, 502, 503, 504],
        'max_routes_per_check' => env('SITEMAP_MAX_ROUTES_PER_CHECK', 50), // New: limit routes per check
        'delay_between_checks' => env('SITEMAP_DELAY_BETWEEN_CHECKS', 1), // New: delay between checks in seconds
        'skip_timeout_routes' => env('SITEMAP_SKIP_TIMEOUT_ROUTES', true), // New: skip routes that timeout
    ],

    // Threshold alerting settings
    'thresholds' => [
        'enabled' => env('SITEMAP_THRESHOLDS_ENABLED', true),
        'response_time' => [
            'warning' => env('SITEMAP_RESPONSE_TIME_WARNING', 1000), // 1 second
            'critical' => env('SITEMAP_RESPONSE_TIME_CRITICAL', 2000), // 2 seconds
            'alert' => env('SITEMAP_RESPONSE_TIME_ALERT', 5000), // 5 seconds
        ],
        'error_rate' => [
            'warning' => env('SITEMAP_ERROR_RATE_WARNING', 5), // 5% error rate
            'critical' => env('SITEMAP_ERROR_RATE_CRITICAL', 10), // 10% error rate
            'alert' => env('SITEMAP_ERROR_RATE_ALERT', 20), // 20% error rate
        ],
        'status_code' => [
            'warning' => [404, 429, 500, 502, 503, 504],
            'critical' => [500, 502, 503, 504],
        ],
        'monitoring' => [
            'all_routes' => env('SITEMAP_MONITOR_ALL_ROUTES', true),
            'specific_routes' => [
                // Add specific routes to monitor with custom thresholds
                // 'api/users' => ['response_time' => 500, 'error_rate' => 2],
                // 'admin/dashboard' => ['response_time' => 1000, 'error_rate' => 5],
            ],
            'prefixes' => [
                // Monitor routes by prefix with custom thresholds
                'api' => ['response_time' => 1000, 'error_rate' => 5],
                'admin' => ['response_time' => 2000, 'error_rate' => 10],
                'public' => ['response_time' => 500, 'error_rate' => 2],
            ],
        ],
        'notifications' => [
            'enabled' => env('SITEMAP_THRESHOLD_NOTIFICATIONS', true),
            'channels' => [
                'log' => env('SITEMAP_THRESHOLD_LOG', true),
                'email' => env('SITEMAP_THRESHOLD_EMAIL', false),
                'slack' => env('SITEMAP_THRESHOLD_SLACK', false),
                'webhook' => env('SITEMAP_THRESHOLD_WEBHOOK', false),
            ],
            'recipients' => [
                'email' => env('SITEMAP_THRESHOLD_EMAIL_RECIPIENTS', []),
                'slack_webhook' => env('SITEMAP_THRESHOLD_SLACK_WEBHOOK'),
                'webhook_url' => env('SITEMAP_THRESHOLD_WEBHOOK_URL'),
            ],
        ],
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
        'middleware' => ['web'],
        'permissions' => [
            'view' => 'sitemap.view',
            'manage' => 'sitemap.manage',
            'admin' => 'sitemap.admin',
        ],
    ],

    // API settings
    'api' => [
        'enabled' => env('SITEMAP_API_ENABLED', true),
        'middleware' => ['api'],
        'rate_limit' => env('SITEMAP_API_RATE_LIMIT', 60), // requests per minute
        'throttle' => [
            'enabled' => env('SITEMAP_API_THROTTLE_ENABLED', true),
            'max_attempts' => env('SITEMAP_API_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('SITEMAP_API_DECAY_MINUTES', 1),
        ],
        'cors' => [
            'enabled' => env('SITEMAP_API_CORS_ENABLED', false),
            'allowed_origins' => env('SITEMAP_API_CORS_ORIGINS', ['*']),
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
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
