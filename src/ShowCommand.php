<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * The finished map — route-derived URLs, dynamic sources and any only()
 * restriction, exactly as a crawler would receive it.
 */
final class ShowCommand extends Command
{
    protected $signature = 'sitemap:show
                            {filter? : Only URLs containing this}
                            {--xml : Print the raw XML instead of a table}
                            {--check : Request every URL and flag anything that is not a 200}';

    protected $description = 'Show the URLs the sitemap will serve';

    public function handle(Sitemap $sitemap): int
    {
        if ($this->option('xml') && ! $this->argument('filter')) {
            $this->line((string) $sitemap->response()->getContent());

            return self::SUCCESS;
        }

        $entries = $sitemap->entries();

        if ($filter = $this->argument('filter')) {
            $entries = array_values(array_filter($entries, static fn (array $e): bool => str_contains($e['loc'], (string) $filter)));
        }

        if ($entries === []) {
            $this->warn('Sitemap is empty.');

            return self::SUCCESS;
        }

        if (! $this->option('check')) {
            $this->table(['URL', 'Last modified'], array_map(
                static fn (array $e): array => [$e['loc'], $e['lastmod'] ?? '—'],
                $entries,
            ));
            $this->line(sprintf('  <info>%d URLs</info>', count($entries)));

            return self::SUCCESS;
        }

        // A sitemap of redirects burns crawl budget and hides the pages that
        // do answer — the failure this package exists to stop.
        $bad = [];
        $bar = $this->output->createProgressBar(count($entries));

        foreach ($entries as $entry) {
            $status = rescue(
                fn (): int => Http::withoutRedirecting()->timeout(10)->get($entry['loc'])->status(),
                0,
                report: false,
            );

            if ($status !== 200) {
                $bad[] = [$entry['loc'], $status === 0 ? 'unreachable' : (string) $status];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($bad === []) {
            $this->info(sprintf('All %d URLs answer 200.', count($entries)));

            return self::SUCCESS;
        }

        $this->table(['URL', 'Status'], $bad);
        $this->error(sprintf('%d of %d URLs do not answer 200.', count($bad), count($entries)));

        return self::FAILURE;
    }
}
