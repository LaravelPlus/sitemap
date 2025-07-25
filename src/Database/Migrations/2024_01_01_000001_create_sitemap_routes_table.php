<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_routes', function (Blueprint $table) {
            $table->id();
            $table->string('uri');
            $table->string('name')->nullable();
            $table->json('methods');
            $table->string('controller')->nullable();
            $table->string('action')->nullable();
            $table->json('middleware')->nullable();
            $table->string('domain')->nullable();
            $table->boolean('is_active')->default(true);
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

            $table->index(['environment', 'is_active']);
            $table->index(['uri', 'environment']);
            $table->index('last_checked_at');
            $table->index('error_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_routes');
    }
}; 