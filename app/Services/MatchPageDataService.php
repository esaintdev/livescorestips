<?php

namespace App\Services;

use App\Models\Prediction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Provider enrichment for a match page (form, head-to-head, standings, team
 * stats), cached as one snapshot per fixture.
 *
 * Building it costs 7-9 sequential API calls, which made cold match pages take
 * 4-5s. Finished matches never change, so their snapshot is kept for 30 days;
 * upcoming ones for a few hours and are refreshed ahead of time by
 * `prediction:warm-pages`.
 */
class MatchPageDataService
{
    public const FINISHED_STATUSES = ['FT', 'AET', 'PEN', 'CANC', 'ABD', 'AWD', 'WO'];

    public const LIVE_STATUSES = ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'];

    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    public static function cacheKey($fixtureId): string
    {
        return "match_page_v2_{$fixtureId}";
    }

    public static function isFinished(array $fixture): bool
    {
        return in_array($fixture['fixture']['status']['short'] ?? null, self::FINISHED_STATUSES, true);
    }

    /**
     * @return array{homeForm: array, awayForm: array, homeRecent: array, awayRecent: array, headToHead: array, standings: array, homeStats: ?array, awayStats: ?array}
     */
    public function get(array $fixture, ?Prediction $prediction, bool $refresh = false): array
    {
        $fixtureId = $fixture['fixture']['id'] ?? ($prediction->fixture_id ?? null);

        if (! $fixtureId) {
            return $this->build($fixture, $prediction);
        }

        $key = self::cacheKey($fixtureId);

        if (! $refresh) {
            $cached = Cache::get($key);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $data = $this->build($fixture, $prediction);
        Cache::put($key, $data, $this->ttl($fixture, $data));

        return $data;
    }

    protected function ttl(array $fixture, array $data): int
    {
        // A failed/empty build is retried soon rather than frozen
        if (empty($data['headToHead']) && empty($data['standings']) && empty($data['homeStats'])) {
            return 300;
        }

        $status = $fixture['fixture']['status']['short'] ?? null;

        if (in_array($status, self::LIVE_STATUSES, true)) {
            return 600;
        }

        return self::isFinished($fixture) ? 30 * 86400 : 3 * 3600;
    }

    protected function build(array $fixture, ?Prediction $prediction): array
    {
        $homeTeamId = $fixture['teams']['home']['id'] ?? null;
        $awayTeamId = $fixture['teams']['away']['id'] ?? null;
        $leagueId = $fixture['league']['id'] ?? ($prediction->league_id ?? null);
        $season = $fixture['league']['season'] ?? null;

        $homeForm = $prediction->home_form ?? [];
        $awayForm = $prediction->away_form ?? [];
        $homeRecent = [];
        $awayRecent = [];
        $headToHead = [];
        $standings = [];
        $homeStats = null;
        $awayStats = null;

        // Provider enrichments are all best-effort.
        if ($homeTeamId && $awayTeamId) {
            try {
                $homeForm = $this->apiService->fetchTeamForm($homeTeamId, 5) ?: $homeForm;
                $awayForm = $this->apiService->fetchTeamForm($awayTeamId, 5) ?: $awayForm;
                $homeRecent = $this->apiService->fetchTeamRecentFixtures($homeTeamId, 5);
                $awayRecent = $this->apiService->fetchTeamRecentFixtures($awayTeamId, 5);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch team forms', ['error' => $e->getMessage()]);
            }

            try {
                $headToHead = $this->apiService->fetchHeadToHead($homeTeamId, $awayTeamId, 5) ?: [];
            } catch (\Exception $e) {
                Log::warning('Failed to fetch head-to-head', ['error' => $e->getMessage()]);
            }
        }

        if ($leagueId && $season) {
            try {
                $allGroups = $this->apiService->fetchLeagueStandings($leagueId, $season, true);
                foreach ($allGroups as $group) {
                    foreach ($group as $team) {
                        if (($homeTeamId && $team['team']['id'] == $homeTeamId) || ($awayTeamId && $team['team']['id'] == $awayTeamId)) {
                            $standings = $group;
                            break 2;
                        }
                    }
                }
                if (empty($standings) && ! empty($allGroups)) {
                    $standings = $allGroups[0];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch standings', ['error' => $e->getMessage()]);
                $standings = [];
            }
        }

        if ($homeTeamId && $awayTeamId && $leagueId && $season) {
            try {
                $homeStats = $this->apiService->fetchTeamStatistics($homeTeamId, $leagueId, $season);
                $awayStats = $this->apiService->fetchTeamStatistics($awayTeamId, $leagueId, $season);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch team statistics', ['error' => $e->getMessage()]);
                $homeStats = null;
                $awayStats = null;
            }
        }

        return compact('homeForm', 'awayForm', 'homeRecent', 'awayRecent', 'headToHead', 'standings', 'homeStats', 'awayStats');
    }
}
