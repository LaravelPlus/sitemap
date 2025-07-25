<?php

namespace LaravelPlus\Sitemap\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitemapError extends Model
{
    protected $fillable = [
        'route_id',
        'error_type',
        'error_message',
        'error_code',
        'stack_trace',
        'occurred_at',
        'environment',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the route that this error belongs to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(SitemapRoute::class, 'route_id');
    }

    /**
     * Scope to filter by error type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('error_type', $type);
    }

    /**
     * Scope to filter by environment.
     */
    public function scopeForEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope to filter recent errors.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    /**
     * Get the error severity level.
     */
    public function getSeverityAttribute(): string
    {
        $criticalErrors = ['timeout', 'connection_failed', 'ssl_error'];
        $warningErrors = ['redirect', 'not_found', 'forbidden'];
        
        if (in_array($this->error_type, $criticalErrors)) {
            return 'critical';
        }
        
        if (in_array($this->error_type, $warningErrors)) {
            return 'warning';
        }
        
        return 'info';
    }

    /**
     * Get the error color for UI display.
     */
    public function getErrorColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'info',
        };
    }

    /**
     * Get a truncated error message for display.
     */
    public function getTruncatedMessageAttribute(): string
    {
        return strlen($this->error_message) > 100 
            ? substr($this->error_message, 0, 100) . '...'
            : $this->error_message;
    }
} 