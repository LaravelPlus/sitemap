<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelPlus\Sitemap\Services\SitemapService;

final class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate 
                            {--format=xml : Output format (xml, json, csv)}
                            {--environment= : Environment to generate sitemap for}
                            {--output= : Output file path (optional)}
                            {--force : Force generation even if disabled}';

    protected $description = 'Generate sitemap in specified format';

    public function handle(SitemapService $sitemapService): int
    {
        $this->info('🗺️  Generating sitemap...');

        $format = $this->option('format');
        $environment = $this->option('environment') ?? app()->environment();
        $outputPath = $this->option('output');
        $force = $this->option('force');

        if ($force) {
            $this->warn('⚠️  Force mode enabled - ignoring environment settings');
        }

        // Validate format
        $validFormats = ['xml', 'json', 'csv'];
        if (!in_array($format, $validFormats)) {
            $this->error("❌ Invalid format: {$format}. Valid formats: " . implode(', ', $validFormats));

            return 1;
        }

        try {
            $sitemap = $sitemapService->generateSitemap($format);

            if ($outputPath) {
                // Save to file
                $this->saveToFile($sitemap, $outputPath, $format);
                $this->info("✅ Sitemap saved to: {$outputPath}");
            } else {
                // Output to console
                $this->info('✅ Sitemap generated successfully!');
                $this->info("📊 Format: {$format}");
                $this->info("🌍 Environment: {$environment}");
                $this->line('');
                $this->line($sitemap);
            }

            // Show statistics
            $stats = $sitemapService->getDashboardStats();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Routes', $stats['routes']['total']],
                    ['Active Routes', $stats['routes']['active']],
                    ['Healthy Routes', $stats['routes']['healthy']],
                    ['Routes with Errors', $stats['routes']['with_errors']],
                ]
            );

        } catch (Exception $e) {
            $this->error('❌ Sitemap generation failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function saveToFile(string $content, string $path, string $format): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $content);

        // Set appropriate content type headers for web servers
        if (str_starts_with($path, 'public/')) {
            $this->info('💡 Tip: Access your sitemap at: ' . str_replace('public/', '', $path));
        }
    }
}
