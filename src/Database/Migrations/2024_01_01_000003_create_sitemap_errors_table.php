<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('route_id')->constrained('sitemap_routes')->onDelete('cascade');
            $table->string('error_type');
            $table->text('error_message');
            $table->integer('error_code')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->timestamp('occurred_at');
            $table->string('environment');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['route_id', 'occurred_at']);
            $table->index(['error_type', 'environment']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_errors');
    }
};
