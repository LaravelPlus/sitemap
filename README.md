# LaravelPlus Sitemap Package

A comprehensive Laravel package for sitemap management with route discovery, status checking, and environment-specific features.

## Features

- 🔍 **Route Discovery**: Automatically discover and analyze GET routes from your Laravel application
- ✅ **Status Checking**: Monitor route health with concurrent HTTP requests and detailed error tracking
- 🌍 **Environment Management**: Different configurations for production, staging, and development
- 📊 **Dashboard**: Beautiful UI to monitor routes, status checks, and errors
- 🗺️ **Sitemap Generation**: Generate sitemaps in XML, JSON, and CSV formats
- ⚡ **Concurrent Processing**: Fast status checking with configurable concurrency
- 🔔 **Error Tracking**: Detailed error logging with categorization and severity levels
- 📈 **Statistics**: Comprehensive analytics and reporting
- 🎛️ **Route Management**: Toggle routes, update priorities, and change frequencies
- 🧹 **Data Cleanup**: Automatic cleanup of old status checks and errors

## Installation

### 1. Add the package to your composer.json

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/laravelplus/sitemap"
        }
    ],
    "require": {
        "laravelplus/sitemap": "@dev"
    }
}
```

### 2. Install the package

```bash
composer install
```

### 3. Publish the configuration and migrations

```bash
php artisan vendor:publish --tag=sitemap-config
php artisan vendor:publish --tag=sitemap-migrations
```

### 4. Run migrations

```bash
php artisan migrate
```

## Configuration

The package configuration is located in `config/sitemap.php`. Here are the main sections:

### Route Discovery

```php
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
```

### Status Checking

```php
'status_check' => [
    'enabled' => env('SITEMAP_STATUS_CHECK_ENABLED', true),
    'timeout' => env('SITEMAP_STATUS_CHECK_TIMEOUT', 30),
    'concurrent_requests' => env('SITEMAP_CONCURRENT_REQUESTS', 10),
    'retry_attempts' => env('SITEMAP_RETRY_ATTEMPTS', 3),
    'acceptable_status_codes' => [200, 201, 202, 301, 302, 404],
    'exclude_status_codes' => [500, 502, 503, 504],
],
```

### Environment Settings

```php
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
```

## Usage

### Artisan Commands

#### Discover Routes

```bash
# Discover routes for current environment
php artisan sitemap:discover

# Discover routes for specific environment
php artisan sitemap:discover --environment=production

# Force discovery even if disabled
php artisan sitemap:discover --force
```

#### Check Route Status

```bash
# Check all routes
php artisan sitemap:check-status

# Check specific route
php artisan sitemap:check-status --route=1

# Check for specific environment
php artisan sitemap:check-status --environment=production
```

#### Generate Sitemap

```bash
# Generate XML sitemap
php artisan sitemap:generate --format=xml

# Generate JSON sitemap
php artisan sitemap:generate --format=json

# Generate CSV sitemap
php artisan sitemap:generate --format=csv

# Save to file
php artisan sitemap:generate --format=xml --output=public/sitemap.xml
```

### Web Interface

Access the sitemap dashboard at `/sitemap` (configurable via `sitemap.ui.route_prefix`).

#### Available Routes

- `/sitemap` - Dashboard
- `/sitemap/routes` - Route management
- `/sitemap/status` - Status checks
- `/sitemap/errors` - Error tracking
- `/sitemap/generate` - Sitemap generation
- `/sitemap/settings` - Configuration

### API Endpoints

#### Statistics

```bash
GET /sitemap/api/stats
```

#### Routes

```bash
GET /sitemap/api/routes
GET /sitemap/api/routes/{id}
PUT /sitemap/api/routes/{id}/priority
PUT /sitemap/api/routes/{id}/changefreq
PUT /sitemap/api/routes/{id}/toggle
```

#### Actions

```bash
POST /sitemap/api/discover
POST /sitemap/api/check-status
POST /sitemap/api/generate
DELETE /sitemap/api/cleanup
```

### Programmatic Usage

```php
use LaravelPlus\Sitemap\Services\SitemapService;

class SitemapController extends Controller
{
    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    public function discover()
    {
        $result = $this->sitemapService->discoverAndStoreRoutes();
        return response()->json($result);
    }

    public function checkStatus()
    {
        $result = $this->sitemapService->checkAllRoutesStatus();
        return response()->json($result);
    }

    public function generateSitemap()
    {
        $sitemap = $this->sitemapService->generateSitemap('xml');
        return response($sitemap)->header('Content-Type', 'application/xml');
    }
}
```

## Environment Variables

Add these to your `.env` file:

```env
# Route Discovery
SITEMAP_ROUTE_DISCOVERY_ENABLED=true
SITEMAP_MAX_ROUTES=1000

# Status Checking
SITEMAP_STATUS_CHECK_ENABLED=true
SITEMAP_STATUS_CHECK_TIMEOUT=30
SITEMAP_CONCURRENT_REQUESTS=10
SITEMAP_RETRY_ATTEMPTS=3

# Cache
SITEMAP_CACHE_ENABLED=true
SITEMAP_CACHE_TTL=3600

# UI
SITEMAP_UI_ENABLED=true

# Notifications
SITEMAP_NOTIFICATIONS_ENABLED=true
SITEMAP_MAIL_NOTIFICATIONS=false
SITEMAP_SLACK_NOTIFICATIONS=false
SITEMAP_WEBHOOK_NOTIFICATIONS=false
SITEMAP_NOTIFICATION_EMAIL=
SITEMAP_SLACK_WEBHOOK=
SITEMAP_WEBHOOK_URL=
```

## Database Tables

The package creates three main tables:

### sitemap_routes

Stores discovered routes with metadata and status information.

### sitemap_status_checks

Stores individual status check results for routes.

### sitemap_errors

Stores detailed error information for failed route checks.

## Features in Detail

### Route Discovery

- Automatically discovers GET routes from your Laravel application
- Configurable exclusion patterns for admin routes, API routes, etc.
- Calculates priority and change frequency based on route characteristics
- Supports environment-specific discovery

### Status Checking

- Concurrent HTTP requests for fast checking
- Configurable timeout and retry attempts
- Detailed error categorization (timeout, SSL errors, server errors, etc.)
- Response time and size tracking
- Environment-specific status checking

### Error Tracking

- Categorizes errors by type (timeout, connection failed, SSL error, etc.)
- Severity levels (critical, warning, info)
- Stack traces and detailed error messages
- Recent error tracking and cleanup

### Sitemap Generation

- Multiple formats: XML, JSON, CSV
- Configurable inclusion of lastmod, changefreq, and priority
- Environment-specific generation
- Download and API endpoints

### Dashboard

- Real-time statistics
- Route health monitoring
- Recent errors and status checks
- Quick actions for discovery, status checking, and generation
- Responsive design with Tailwind CSS

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support, please open an issue on GitHub or contact the LaravelPlus team. 