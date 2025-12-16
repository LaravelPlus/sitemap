<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('uri');
            $table->string('name')->nullable();
            $table->json('methods');
            $table->string('controller')->nullable();
            $table->string('action')->nullable();
            $table->json('middleware')->nullable();
            $table->string('domain')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_healthy')->default(true);
            $table->decimal('priority', 2, 1)->default(0.5);
            $table->string('changefreq')->default('weekly');
            $table->timestamp('last_checked_at')->nullable();
            $table->integer('last_status_code')->nullable();
            $table->decimal('last_response_time', 8, 3)->nullable();
            $table->integer('error_count')->default(0);
            $table->text('last_error_message')->nullable();
            $table->string('environment');
            $table->json('metadata')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();

            // Performance indexes
            $table->index(['environment', 'is_active']);
            $table->index(['environment', 'is_healthy']);
            $table->index(['uri', 'environment']);
            $table->index('last_checked_at');
            $table->index('error_count');
            $table->index(['is_active', 'is_healthy']);
            $table->index(['environment', 'last_checked_at']);
            $table->index(['environment', 'error_count']);
            $table->index(['priority', 'is_active']);
            $table->index(['changefreq', 'is_active']);

            // Composite indexes for common queries
            $table->index(['environment', 'is_active', 'is_healthy']);
            $table->index(['environment', 'last_checked_at', 'is_active']);
            $table->index(['error_count', 'environment', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_routes');
    }
};
