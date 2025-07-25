# LaravelPlus Sitemap Package

A comprehensive Laravel package for discovering, monitoring, and managing GET routes with status checking, threshold alerting, and advanced performance monitoring.

## Features

- **Route Discovery**: Automatically discover all GET routes in your Laravel application
- **Status Monitoring**: Check response times and status codes for all routes
- **Threshold Alerting**: Monitor response times and error rates with configurable thresholds
- **Background Jobs**: Asynchronous processing for route discovery and status checks
- **Event System**: Event-driven architecture with listeners for logging and notifications
- **Performance Monitoring**: Real-time performance metrics and caching optimization
- **Data Management**: Comprehensive data management with clear, truncate, and cache operations
- **Modern UI**: Beautiful, responsive interface with real-time updates and Vue 3
- **API Support**: RESTful API for integration with other systems
- **Queue Integration**: Background job processing with configurable queues
- **Caching System**: Intelligent caching for improved performance
- **Foreign Key Safety**: Proper database operations with constraint handling

## Installation

1. Install the package via Composer:
```bash
composer require laravelplus/sitemap
```

2. Publish the configuration and migrations:
```bash
php artisan vendor:publish --tag=sitemap-config
php artisan vendor:publish --tag=sitemap-migrations
```

3. Run the migrations:
```bash
php artisan migrate
```

4. Set up the queue (recommended):
```bash
php artisan queue:table
php artisan migrate
```

## Configuration

### Basic Configuration

Add these environment variables to your `.env` file:

```env
# Enable/disable features
SITEMAP_ENABLED=true
SITEMAP_STATUS_CHECK_ENABLED=true
SITEMAP_BULK_CHECK_ENABLED=true
SITEMAP_THRESHOLDS_ENABLED=true
SITEMAP_QUEUE_ENABLED=true
SITEMAP_EVENTS_ENABLED=true

# Response time thresholds (in milliseconds)
SITEMAP_RESPONSE_TIME_WARNING=1000
SITEMAP_RESPONSE_TIME_CRITICAL=2000
SITEMAP_RESPONSE_TIME_ALERT=5000

# Error rate thresholds (in percentage)
SITEMAP_ERROR_RATE_WARNING=5
SITEMAP_ERROR_RATE_CRITICAL=10
SITEMAP_ERROR_RATE_ALERT=20

# Status check settings
SITEMAP_STATUS_CHECK_TIMEOUT=10
SITEMAP_CONCURRENT_REQUESTS=10
SITEMAP_MAX_ROUTES_PER_CHECK=50
SITEMAP_DELAY_BETWEEN_CHECKS=1

# Queue settings
SITEMAP_QUEUE_CONNECTION=database
SITEMAP_QUEUE_NAME=sitemap
SITEMAP_QUEUE_RETRY_ATTEMPTS=3
SITEMAP_QUEUE_TIMEOUT=300

# Performance settings
SITEMAP_CACHE_TTL=300
SITEMAP_CACHE_ENABLED=true
SITEMAP_PERFORMANCE_MONITORING=true

# Notifications
SITEMAP_THRESHOLD_NOTIFICATIONS=true
SITEMAP_THRESHOLD_LOG=true
```

### Advanced Configuration

The package includes comprehensive configuration options in `config/sitemap.php`:

```php
return [
    // Route discovery settings
    'route_discovery' => [
        'enabled' => env('SITEMAP_ROUTE_DISCOVERY_ENABLED', true),
        'methods' => ['GET', 'HEAD'],
        'exclude_patterns' => [
            'admin/*',
            'api/*',
            '_debugbar/*',
            '_ignition/*',
            '_ignition/health-check',
            'telescope/*',
            'horizon/*',
            'log-viewer/*',
        ],
        'include_patterns' => [],
        'max_routes' => 1000,
    ],

    // Status check settings
    'status_check' => [
        'enabled' => env('SITEMAP_STATUS_CHECK_ENABLED', true),
        'bulk_check_enabled' => env('SITEMAP_BULK_CHECK_ENABLED', true),
        'timeout' => env('SITEMAP_STATUS_CHECK_TIMEOUT', 10),
        'concurrent_requests' => env('SITEMAP_CONCURRENT_REQUESTS', 10),
        'max_routes_per_check' => env('SITEMAP_MAX_ROUTES_PER_CHECK', 50),
        'delay_between_checks' => env('SITEMAP_DELAY_BETWEEN_CHECKS', 1),
        'acceptable_status_codes' => [200, 201, 202, 204],
    ],

    // Performance and caching
    'cache' => [
        'enabled' => env('SITEMAP_CACHE_ENABLED', true),
        'ttl' => env('SITEMAP_CACHE_TTL', 300),
        'prefix' => 'sitemap_',
    ],

    // Queue settings
    'queue' => [
        'enabled' => env('SITEMAP_QUEUE_ENABLED', true),
        'connection' => env('SITEMAP_QUEUE_CONNECTION', 'database'),
        'queues' => [
            'sitemap' => env('SITEMAP_QUEUE_NAME', 'sitemap'),
            'sitemap-logs' => env('SITEMAP_LOGS_QUEUE', 'sitemap-logs'),
            'sitemap-notifications' => env('SITEMAP_NOTIFICATIONS_QUEUE', 'sitemap-notifications'),
            'sitemap-cache' => env('SITEMAP_CACHE_QUEUE', 'sitemap-cache'),
        ],
        'retry_attempts' => env('SITEMAP_QUEUE_RETRY_ATTEMPTS', 3),
        'timeout' => env('SITEMAP_QUEUE_TIMEOUT', 300),
    ],

    // Event settings
    'events' => [
        'enabled' => env('SITEMAP_EVENTS_ENABLED', true),
        'broadcasting' => [
            'enabled' => env('SITEMAP_BROADCASTING_ENABLED', false),
            'channel' => env('SITEMAP_BROADCAST_CHANNEL', 'sitemap'),
        ],
        'listeners' => [
            'log_routes_discovered' => env('SITEMAP_LOG_ROUTES_DISCOVERED', true),
            'notify_status_check_complete' => env('SITEMAP_NOTIFY_STATUS_CHECK', true),
            'cache_sitemap_results' => env('SITEMAP_CACHE_SITEMAP_RESULTS', true),
        ],
    ],
];
```

## Usage

### Web Interface

Access the sitemap dashboard at: `http://your-app.com/sitemap`

**Available Pages:**
- **Dashboard**: Overview with statistics and quick actions
- **Routes**: Detailed list of all discovered routes
- **Status**: Route status monitoring and health checks
- **Errors**: Error tracking and debugging
- **Settings**: Configuration and data management
- **Generate**: Sitemap generation and export

### API Endpoints

#### Route Management
- `POST /sitemap/discover` - Discover new routes (queued job)
- `POST /sitemap/check-status` - Check status of all routes (queued job)
- `GET /sitemap/jobs/status` - Get job status and progress
- `GET /sitemap/jobs/history` - Get recent job history

#### Data Management
- `POST /sitemap/data/empty` - Clear all sitemap data
- `POST /sitemap/data/truncate` - Remove old data (configurable days)
- `POST /sitemap/cache/clear` - Clear all sitemap caches

#### Settings
- `GET /sitemap/settings` - Get current configuration
- `POST /sitemap/settings` - Update configuration

### Artisan Commands

```bash
# Discover routes (synchronous)
php artisan sitemap:discover

# Check status of all routes (synchronous)
php artisan sitemap:check-status

# Generate sitemap XML
php artisan sitemap:generate

# Process background jobs
php artisan queue:work --queue=sitemap
```

## Background Jobs & Events

### Available Jobs

- **DiscoverRoutesJob**: Discovers and stores application routes
- **CheckRoutesStatusJob**: Performs status checks on all routes
- **GenerateSitemapJob**: Generates sitemap files in background

### Available Events

- **RoutesDiscovered**: Fired when routes are discovered
- **RoutesStatusChecked**: Fired when status checks complete
- **SitemapGenerated**: Fired when sitemap is generated

### Available Listeners

- **LogRoutesDiscovered**: Logs route discovery events
- **NotifyStatusCheckComplete**: Handles status check notifications
- **CacheSitemapResults**: Caches sitemap generation results

## Data Management

The package provides comprehensive data management capabilities:

### Clear All Data
```javascript
// Via web interface
fetch('/sitemap/data/empty', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token }
});
```

### Truncate Old Data
```javascript
// Remove data older than X days
fetch('/sitemap/data/truncate', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token },
    body: JSON.stringify({ days: 30 })
});
```

### Clear Cache
```javascript
// Clear all sitemap caches
fetch('/sitemap/cache/clear', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token }
});
```

## Performance Monitoring

The package includes comprehensive performance monitoring:

### Dashboard Metrics
- **Execution Time**: Dashboard load time and performance
- **Cache Hits/Misses**: Cache performance statistics
- **Database Queries**: Query count and performance
- **Slow Queries**: Queries taking longer than 100ms
- **Average Query Time**: Overall database performance

### Performance Optimization
- **Intelligent Caching**: 5-minute TTL for route statistics
- **Eager Loading**: Preloaded relationships to prevent N+1 queries
- **Database Indexes**: Optimized indexes for common queries
- **Batch Operations**: Chunked processing for large datasets
- **Concurrent Requests**: Configurable concurrency for status checks

## Threshold Alerting

The package includes a comprehensive threshold alerting system:

### Response Time Monitoring
- **Warning**: Routes taking longer than 1000ms
- **Critical**: Routes taking longer than 2000ms  
- **Alert**: Routes taking longer than 5000ms

### Error Rate Monitoring
- **Warning**: Error rates above 5%
- **Critical**: Error rates above 10%
- **Alert**: Error rates above 20%

### Status Code Monitoring
- **Warning**: 404, 429, 500, 502, 503, 504
- **Critical**: 500, 502, 503, 504

### Notifications
- **Log**: Alerts are logged to Laravel's log system
- **Email**: Send alerts via email (configurable)
- **Slack**: Send alerts to Slack (configurable)
- **Webhook**: Send alerts to external webhooks (configurable)

## Database Schema

The package creates three main tables:

### sitemap_routes
- `id`, `uri`, `name`, `methods`, `controller`, `action`
- `environment`, `is_active`, `is_healthy`
- `priority`, `changefreq`, `last_checked_at`
- `last_status_code`, `last_response_time`, `error_count`

### sitemap_status_checks
- `id`, `route_id`, `status_code`, `response_time`
- `checked_at`, `environment`, `user_id`

### sitemap_errors
- `id`, `route_id`, `error_message`, `error_type`
- `occurred_at`, `environment`, `user_id`

## Customization

### Custom Thresholds

You can set custom thresholds for specific routes or prefixes:

```php
// In config/sitemap.php
'thresholds' => [
    'monitoring' => [
        'prefixes' => [
            'api' => ['response_time' => 1000, 'error_rate' => 5],
            'admin' => ['response_time' => 2000, 'error_rate' => 10],
        ],
    ],
],
```

### Custom Notifications

Implement custom notification channels by extending the `ThresholdService`:

```php
class CustomThresholdService extends ThresholdService
{
    protected function sendCustomNotification(array $alert): void
    {
        // Your custom notification logic
    }
}
```

### Custom Event Listeners

Register custom event listeners in your `EventServiceProvider`:

```php
protected $listen = [
    RoutesDiscovered::class => [
        CustomRoutesDiscoveredListener::class,
    ],
];
```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**: The package now handles foreign key constraints properly with safe deletion order.

2. **Queue Jobs Not Processing**: Ensure your queue worker is running:
```bash
php artisan queue:work --queue=sitemap
```

3. **Performance Issues**: Enable caching and monitor performance metrics in the dashboard.

4. **Route Discovery Issues**: Check the exclusion patterns in `config/sitemap.php`.

### Debugging

Enable debug logging in your `.env`:
```env
LOG_LEVEL=debug
```

Check the Laravel logs for detailed information about route discovery and status checks.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE). 