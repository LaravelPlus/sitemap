<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sitemap_status_checks', function (Blueprint $table) {
            // Check if the wrong column exists and rename it
            if (Schema::hasColumn('sitemap_status_checks', 'sitemap_route_id')) {
                $table->renameColumn('sitemap_route_id', 'route_id');
            }
            
            // Add foreign key constraint if it doesn't exist
            if (!Schema::hasColumn('sitemap_status_checks', 'route_id')) {
                $table->foreignId('route_id')->constrained('sitemap_routes')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sitemap_status_checks', function (Blueprint $table) {
            if (Schema::hasColumn('sitemap_status_checks', 'route_id')) {
                $table->renameColumn('route_id', 'sitemap_route_id');
            }
        });
    }
}; 