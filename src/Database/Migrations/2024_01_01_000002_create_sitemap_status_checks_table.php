<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_status_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('sitemap_routes')->onDelete('cascade');
            $table->integer('status_code');
            $table->decimal('response_time', 8, 3)->nullable();
            $table->integer('response_size')->nullable();
            $table->json('headers')->nullable();
            $table->text('body_preview')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');
            $table->string('environment');
            $table->timestamps();

            $table->index(['route_id', 'checked_at']);
            $table->index(['status_code', 'environment']);
            $table->index('checked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_status_checks');
    }
}; 