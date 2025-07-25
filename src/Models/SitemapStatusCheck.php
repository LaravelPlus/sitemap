<?php

namespace LaravelPlus\Sitemap\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitemapStatusCheck extends Model
{
    protected $fillable = [
        'route_id',
        'status_code',
        'response_time',
        'response_size',
        'headers',
        'body_preview',
        'error_message',
        'checked_at',
        'environment',
    ];

    protected $casts = [
        'headers' => 'array',
        'checked_at' => 'datetime',
        'response_time' => 'float',
        'response_size' => 'integer',
    ];

    /**
     * Get the route that this status check belongs to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(SitemapRoute::class, 'route_id');
    }

    /**
     * Scope to filter by status code.
     */
    public function scopeWithStatusCode($query, int $statusCode)
    {
        return $query->where('status_code', $statusCode);
    }

    /**
     * Scope to filter by environment.
     */
    public function scopeForEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope to filter successful checks.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status_code', config('sitemap.status_check.acceptable_status_codes', [200]));
    }

    /**
     * Scope to filter failed checks.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status_code', config('sitemap.status_check.exclude_status_codes', [500, 502, 503, 504]));
    }

    /**
     * Check if this status check was successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status_code, config('sitemap.status_check.acceptable_status_codes', [200]));
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isSuccessful()) {
            return 'success';
        }

        if (in_array($this->status_code, [500, 502, 503, 504])) {
            return 'danger';
        }

        if (in_array($this->status_code, [301, 302, 404])) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get formatted response time.
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->response_time < 1) {
            return number_format($this->response_time * 1000, 0) . 'ms';
        }

        return number_format($this->response_time, 2) . 's';
    }

    /**
     * Get formatted response size.
     */
    public function getFormattedResponseSizeAttribute(): string
    {
        $size = $this->response_size;
        
        if ($size < 1024) {
            return $size . ' B';
        }
        
        if ($size < 1024 * 1024) {
            return number_format($size / 1024, 1) . ' KB';
        }
        
        return number_format($size / (1024 * 1024), 1) . ' MB';
    }
} 