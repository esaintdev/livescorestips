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
                            {--max-seconds=110 : Stop after this long (shared hosting kills long-running processes)}
                            {--refresh-after=7200 : Rebuild recent/upcoming snapshots older than this many seconds}
                            {--pause=250 : Milliseconds to wait between fixtures (API per-minute limit)}';

    protected $description = 'Pre-build match page data so match pages load from cache (short, frequent runs)';

    protected float $deadline;

    public function handle(ApiFootballService $apiService, MatchPageDataService $pageData)
    {
        $this->deadline = microtime(true) + (int) $this->option('max-seconds');
        $pause = (int) $this->option('pause') * 1000;
        $staleBefore = time() - (int) $this->option('refresh-after');

        // 1. Yesterday..+2 days, soonest first: rebuild only missing or stale
        //    snapshots (yesterday's become 30-day "finished" snapshots).
        $recent = array_values(array_filter(
            $this->fixtureIds(now()->subDay()->startOfDay(), now()->addDays(2)->endOfDay(), 'asc'),
            fn ($id) => (MatchPageDataService::builtAt($id) ?? 0) < $staleBefore
        ));
        $this->info(count($recent).' recent/upcoming fixtures need a refresh...');
        [$ok, $failed] = $this->warm($recent, $apiService, $pageData, true, $pause);
        $this->line("  done: $ok, failed: $failed");

        // 2. With the time left: older matches still in the sitemap (indexable
        //    leagues) that have no snapshot yet. Built once; finished ones last 30 days.
        if (! $this->outOfTime()) {
            $older = array_values(array_filter(
                $this->fixtureIds(now()->subDays(30)->startOfDay(), now()->subDays(2)->endOfDay(), 'desc', true),
                fn ($id) => ! Cache::has(MatchPageDataService::cacheKey($id))
            ));
            $this->info(count($older).' older sitemap fixtures without a snapshot...');
            [$ok, $failed] = $this->warm($older, $apiService, $pageData, false, $pause);
            $this->line("  done: $ok, failed: $failed");
        }

        if ($this->outOfTime()) {
            $this->line('Time budget reached; the next run continues.');
        }

        return 0;
    }

    /**
     * Distinct provider fixture ids with a match date in the window.
     */
    protected function fixtureIds($from, $to, string $direction, bool $indexableOnly = false): array
    {
        return Prediction::whereNotNull('slug')
            ->whereNotNull('fixture_id')
            ->whereBetween('match_date', [$from, $to])
            ->orderBy('match_date', $direction)
            ->orderBy('match_time', $direction)
            ->get(['fixture_id', 'league_id'])
            ->when($indexableOnly, fn ($rows) => $rows->filter(fn ($row) => league_is_indexable($row->league_id)))
            ->pluck('fixture_id')
            ->unique()
            ->values()
            ->all();
    }

    protected function outOfTime(): bool
    {
        return microtime(true) >= $this->deadline;
    }

    protected function warm(array $fixtureIds, ApiFootballService $apiService, MatchPageDataService $pageData, bool $refresh, int $pause): array
    {
        $ok = 0;
        $failed = 0;

        foreach ($fixtureIds as $fixtureId) {
            if ($this->outOfTime()) {
                break;
            }

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
