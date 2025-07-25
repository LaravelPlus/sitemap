<?php

namespace LaravelPlus\Sitemap\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SitemapRoute extends Model
{
    use HasSlug;

    protected $fillable = [
        'uri',
        'name',
        'methods',
        'controller',
        'action',
        'middleware',
        'domain',
        'is_active',
        'priority',
        'changefreq',
        'last_checked_at',
        'last_status_code',
        'last_response_time',
        'error_count',
        'last_error_message',
        'environment',
        'metadata',
    ];

    protected $casts = [
        'methods' => 'array',
        'middleware' => 'array',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'metadata' => 'array',
        'priority' => 'float',
    ];

    protected $attributes = [
        'is_active' => true,
        'priority' => 0.5,
        'changefreq' => 'weekly',
        'error_count' => 0,
    ];

    /**
     * Get the slug options for the model.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('uri')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the status checks for this route.
     */
    public function statusChecks(): HasMany
    {
        return $this->hasMany(SitemapStatusCheck::class, 'route_id');
    }

    /**
     * Get the errors for this route.
     */
    public function errors(): HasMany
    {
        return $this->hasMany(SitemapError::class, 'route_id');
    }

    /**
     * Scope to filter by environment.
     */
    public function scopeForEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope to filter active routes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter routes with errors.
     */
    public function scopeWithErrors($query)
    {
        return $query->where('error_count', '>', 0);
    }

    /**
     * Get the full URL for this route.
     */
    public function getFullUrlAttribute(): string
    {
        try {
            $baseUrl = config('app.url', 'http://localhost');
            return rtrim($baseUrl, '/') . '/' . ltrim($this->uri, '/');
        } catch (\Exception $e) {
            \Log::error('Sitemap route URL generation failed', [
                'error' => $e->getMessage(),
                'uri' => $this->uri,
            ]);
            
            // Fallback to localhost
            return 'http://localhost/' . ltrim($this->uri, '/');
        }
    }

    /**
     * Check if the route is healthy.
     */
    public function isHealthy(): bool
    {
        try {
            $acceptableCodes = config('sitemap.status_check.acceptable_status_codes', [200]);
            return $this->error_count === 0 && 
                   in_array($this->last_status_code, $acceptableCodes);
        } catch (\Exception $e) {
            \Log::error('Sitemap route health check failed', [
                'error' => $e->getMessage(),
                'route_id' => $this->id,
                'uri' => $this->uri,
            ]);
            
            // Default to unhealthy if there are errors
            return $this->error_count === 0;
        }
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isHealthy()) {
            return 'success';
        }

        if ($this->error_count > 5) {
            return 'danger';
        }

        return 'warning';
    }

    /**
     * Get the status of the route.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isHealthy()) {
            return 'healthy';
        }

        if ($this->error_count > 0) {
            return 'error';
        }

        return 'unknown';
    }

    /**
     * Get the response time of the route.
     */
    public function getResponseTimeAttribute(): ?float
    {
        return $this->last_response_time;
    }
} 