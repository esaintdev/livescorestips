<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\MatchPageDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmMatchPages extends Command
{
    protected $signature = 'prediction:warm-pages
                            {--backfill=150 : Max finished sitemap matches (last 30 days) without a snapshot to build per run}
                            {--pause=250 : Milliseconds to wait between fixtures (API per-minute limit)}';

    protected $description = 'Pre-build match page data so match pages load from cache';

    public function handle(ApiFootballService $apiService, MatchPageDataService $pageData)
    {
        $pause = (int) $this->option('pause') * 1000;

        // 1. Yesterday..+2 days: always refresh (form, odds context, live/final status change)
        $recent = $this->fixtureIds(now()->subDay()->startOfDay(), now()->addDays(2)->endOfDay());
        $this->info('Refreshing '.count($recent).' recent/upcoming fixtures...');
        [$ok, $failed] = $this->warm($recent, $apiService, $pageData, true, $pause);
        $this->line("  done: $ok, failed: $failed");

        // 2. Older matches still in the sitemap (indexable leagues): build once
        // (finished snapshots last 30 days)
        $older = array_values(array_filter(
            $this->fixtureIds(now()->subDays(30)->startOfDay(), now()->subDays(2)->endOfDay(), true),
            fn ($id) => ! Cache::has(MatchPageDataService::cacheKey($id))
        ));
        $older = array_slice($older, 0, (int) $this->option('backfill'));
        $this->info('Backfilling '.count($older).' older fixtures without a snapshot...');
        [$ok, $failed] = $this->warm($older, $apiService, $pageData, false, $pause);
        $this->line("  done: $ok, failed: $failed");

        return 0;
    }

    /**
     * Distinct provider fixture ids with a match date in the window, newest first.
     */
    protected function fixtureIds($from, $to, bool $indexableOnly = false): array
    {
        return Prediction::whereNotNull('slug')
            ->whereNotNull('fixture_id')
            ->whereBetween('match_date', [$from, $to])
            ->orderByDesc('match_date')
            ->get(['fixture_id', 'league_id'])
            ->when($indexableOnly, fn ($rows) => $rows->filter(fn ($row) => league_is_indexable($row->league_id)))
            ->pluck('fixture_id')
            ->unique()
            ->values()
            ->all();
    }

    protected function warm(array $fixtureIds, ApiFootballService $apiService, MatchPageDataService $pageData, bool $refresh, int $pause): array
    {
        $ok = 0;
        $failed = 0;

        foreach ($fixtureIds as $fixtureId) {
            try {
                $fixture = $apiService->fetchFixtureDetails($fixtureId);
                if (! $fixture) {
                    $failed++;

                    continue;
                }

                $pageData->get($fixture, Prediction::canonicalForFixture($fixtureId), $refresh);
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("  ! Fixture $fixtureId: ".$e->getMessage());
            }

            usleep($pause);
        }

        return [$ok, $failed];
    }
}
