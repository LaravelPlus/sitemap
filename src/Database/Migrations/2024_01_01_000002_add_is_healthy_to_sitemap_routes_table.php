<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('sitemap_routes', function (Blueprint $table): void {
            if (!Schema::hasColumn('sitemap_routes', 'is_healthy')) {
                $table->boolean('is_healthy')->default(true)->after('is_active');
                $table->index(['environment', 'is_healthy']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sitemap_routes', function (Blueprint $table): void {
            if (Schema::hasColumn('sitemap_routes', 'is_healthy')) {
                $table->dropIndex(['environment', 'is_healthy']);
                $table->dropColumn('is_healthy');
            }
        });
    }
};
