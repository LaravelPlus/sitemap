<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap;

use Illuminate\Console\Command;

/**
 * Every GET route with the verdict on it. Answers "why is my page not in the
 * sitemap?" without reading the filter code.
 */
final class RoutesCommand extends Command
{
    protected $signature = 'sitemap:routes
                            {filter? : Only routes whose URI contains this}
                            {--included : Only routes that made the map}
                            {--excluded : Only routes that did not}';

    protected $description = 'List every GET route and why it is in or out of the sitemap';

    public function handle(Sitemap $sitemap): int
    {
        $routes = $sitemap->auditRoutes();

        if ($filter = $this->argument('filter')) {
            $routes = array_filter($routes, static fn (array $r): bool => str_contains($r['uri'], (string) $filter));
        }

        if ($this->option('included')) {
            $routes = array_filter($routes, static fn (array $r): bool => $r['included']);
        }

        if ($this->option('excluded')) {
            $routes = array_filter($routes, static fn (array $r): bool => ! $r['included']);
        }

        if ($routes === []) {
            $this->warn('No matching GET routes.');

            return self::SUCCESS;
        }

        usort($routes, static fn (array $a, array $b): int => [$b['included'], $a['uri']] <=> [$a['included'], $b['uri']]);

        $this->table(['', 'URI', 'Reason'], array_map(static fn (array $r): array => [
            $r['included'] ? '<info>in</info>' : '<comment>out</comment>',
            $r['uri'],
            $r['reason'],
        ], $routes));

        $in = count(array_filter($routes, static fn (array $r): bool => $r['included']));
        $this->line(sprintf('  <info>%d in</info>, <comment>%d out</comment>', $in, count($routes) - $in));

        return self::SUCCESS;
    }
}
