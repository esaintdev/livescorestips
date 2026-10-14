<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\MatchContentService;
use App\Services\PredictionResultService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MatchController extends Controller
{
    protected $apiService;

    protected $contentService;

    protected $resultService;

    public function __construct(
        ApiFootballService $apiService,
        MatchContentService $contentService,
        PredictionResultService $resultService
    ) {
        $this->apiService = $apiService;
        $this->contentService = $contentService;
        $this->resultService = $resultService;
    }

    public function show($slug): JsonResponse
    {
        try {
            $prediction = $this->resolvePrediction($slug);

            if (! $prediction) {
                if (ctype_digit((string) $slug)) {
                    $fixture = $this->safeFetchFixture($slug);
                    if ($fixture) {
                        return $this->respond($fixture, null, $slug);
                    }
                }

                return response()->json(['message' => 'Match not found'], 404);
            }

            $fixture = $this->safeFetchFixture($prediction->fixture_id);

            if (! $fixture) {
                $fixture = $this->fixtureFromPrediction($prediction);
            }

            return $this->respond($fixture, $prediction, $prediction->fixture_id);
        } catch (\Exception $e) {
            \Log::error('Error loading match details', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            $prediction = $this->resolvePrediction($slug);
            if ($prediction) {
                return $this->respond($this->fixtureFromPrediction($prediction), $prediction, $prediction->fixture_id);
            }

            return response()->json(['message' => 'Match not found'], 404);
        }
    }

    private function resolvePrediction($slug): ?Prediction
    {
        $prediction = Prediction::where('slug', $slug)->first();
        if ($prediction) {
            return $prediction;
        }

        if (ctype_digit((string) $slug)) {
            return Prediction::canonicalForFixture($slug);
        }

        return null;
    }

    private function safeFetchFixture($fixtureId): ?array
    {
        try {
            return $this->apiService->fetchFixtureDetails($fixtureId) ?: null;
        } catch (\Throwable $e) {
            \Log::warning('Fixture provider enrichment failed', [
                'fixture_id' => $fixtureId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function respond(array $fixture, ?Prediction $prediction, $fixtureId): JsonResponse
    {
        $homeTeamId = $fixture['teams']['home']['id'] ?? null;
        $awayTeamId = $fixture['teams']['away']['id'] ?? null;
        $leagueId = $fixture['league']['id'] ?? ($prediction->league_id ?? null);
        $season = $fixture['league']['season'] ?? null;

        $homeForm = $prediction->home_form ?? [];
        $awayForm = $prediction->away_form ?? [];
        $headToHead = [];
        $standings = [];
        $homeStats = null;
        $awayStats = null;

        // Provider enrichments are all best-effort.
        if ($homeTeamId && $awayTeamId) {
            try {
                $homeForm = $this->apiService->fetchTeamForm($homeTeamId, 5) ?: $homeForm;
                $awayForm = $this->apiService->fetchTeamForm($awayTeamId, 5) ?: $awayForm;
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch team forms', ['error' => $e->getMessage()]);
            }

            try {
                $headToHead = $this->apiService->fetchHeadToHead($homeTeamId, $awayTeamId, 5) ?: [];
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch head-to-head', ['error' => $e->getMessage()]);
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
                \Log::warning('Failed to fetch standings', ['error' => $e->getMessage()]);
                $standings = [];
            }
        }

        if ($homeTeamId && $awayTeamId && $leagueId && $season) {
            try {
                $homeStats = $this->apiService->fetchTeamStatistics($homeTeamId, $leagueId, $season);
                $awayStats = $this->apiService->fetchTeamStatistics($awayTeamId, $leagueId, $season);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch team statistics', ['error' => $e->getMessage()]);
                $homeStats = null;
                $awayStats = null;
            }
        }

        // Settlement result (shared service — never duplicated).
        $predictionResult = null;
        $summary = null;
        if ($prediction) {
            $homeGoals = $fixture['goals']['home'] ?? $prediction->home_score;
            $awayGoals = $fixture['goals']['away'] ?? $prediction->away_score;
            $statusShort = $fixture['fixture']['status']['short'] ?? null;

            $resolved = $this->resultService->resolveStatus($prediction->tip, $homeGoals, $awayGoals, $statusShort);
            $predictionResult = $prediction->isTerminal() ? $prediction->status : $resolved;

            if ($predictionResult !== PredictionResultService::PENDING) {
                $summary = $this->resultService->summary($prediction, $predictionResult, $homeGoals, $awayGoals);
            }
        }

        // Editorial content built from available data only.
        $analysis = null;
        $factors = [];
        $related = collect();
        $categorySlug = null;

        if ($prediction) {
            $ctx = [
                'home_form' => $homeForm,
                'away_form' => $awayForm,
                'h2h' => $headToHead,
                'standings' => $standings,
                'home_stats' => $homeStats,
                'away_stats' => $awayStats,
                'home_team' => $fixture['teams']['home']['name'] ?? $prediction->home_team,
                'away_team' => $fixture['teams']['away']['name'] ?? $prediction->away_team,
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
            ];

            $analysis = $this->contentService->analysis($prediction, $ctx);
            $factors = $this->contentService->factors($prediction, $ctx);
            $related = $this->contentService->related($prediction);
            $categorySlug = $this->contentService->categorySlug($prediction);
        }

        $comments = $prediction
            ? $prediction->comments()->approved()->latest()->get()
            : collect();

        $statusShort = $fixture['fixture']['status']['short'] ?? null;
        $isLive = in_array($statusShort, ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE']);

        $matchDate = $prediction->match_date
            ? $prediction->match_date->format('Y-m-d')
            : (isset($fixture['fixture']['date']) ? Carbon::parse($fixture['fixture']['date'])->format('Y-m-d') : null);

        $matchTime = $prediction->match_time
            ? Carbon::parse($prediction->match_time)->format('H:i')
            : (isset($fixture['fixture']['date']) ? Carbon::parse($fixture['fixture']['date'])->format('H:i') : null);

        return response()->json([
            'data' => [
                'id' => $prediction->id ?? null,
                'fixture_id' => $fixtureId,
                'slug' => $prediction->slug ?? null,
                'league' => [
                    'id' => $leagueId,
                    'name' => $fixture['league']['name'] ?? ($prediction->league_name ?? null),
                    'country' => $fixture['league']['country'] ?? ($prediction->league_country ?? null),
                    'logo' => isset($fixture['league']['logo']) ? $fixture['league']['logo'] : ($prediction->league_logo ? url($prediction->league_logo) : null),
                    'season' => $season,
                ],
                'home_team' => [
                    'id' => $homeTeamId,
                    'name' => $fixture['teams']['home']['name'] ?? ($prediction->home_team ?? null),
                    'logo' => $fixture['teams']['home']['logo'] ?? ($prediction->home_team_logo ? url($prediction->home_team_logo) : null),
                ],
                'away_team' => [
                    'id' => $awayTeamId,
                    'name' => $fixture['teams']['away']['name'] ?? ($prediction->away_team ?? null),
                    'logo' => $fixture['teams']['away']['logo'] ?? ($prediction->away_team_logo ? url($prediction->away_team_logo) : null),
                ],
                'score' => [
                    'home' => $fixture['goals']['home'] ?? ($prediction->home_score ?? null),
                    'away' => $fixture['goals']['away'] ?? ($prediction->away_score ?? null),
                ],
                'match_date' => $matchDate,
                'match_time' => $matchTime,
                'status_short' => $statusShort,
                'status_elapsed' => $fixture['fixture']['status']['elapsed'] ?? null,
                'is_live' => $isLive,
                'venue' => $fixture['fixture']['venue']['name'] ?? null,
                'referee' => $fixture['fixture']['referee'] ?? null,
                'prediction' => $prediction ? [
                    'tip' => $prediction->tip,
                    'category' => $prediction->category,
                    'odds' => $prediction->odds,
                    'probability' => $prediction->probability,
                    'status' => $prediction->status,
                ] : null,
                'result' => $predictionResult,
                'result_summary' => $summary,
                'home_form' => array_values(array_slice((array) $homeForm, -5)),
                'away_form' => array_values(array_slice((array) $awayForm, -5)),
                'head_to_head' => collect($headToHead)->take(5)->map(fn ($m) => [
                    'date' => $m['fixture']['date'] ?? null,
                    'home_team' => $m['teams']['home']['name'] ?? null,
                    'away_team' => $m['teams']['away']['name'] ?? null,
                    'home_score' => $m['goals']['home'] ?? null,
                    'away_score' => $m['goals']['away'] ?? null,
                ])->values(),
                'standings' => collect($standings)->take(20)->map(fn ($row) => [
                    'rank' => $row['rank'] ?? null,
                    'team_id' => $row['team']['id'] ?? null,
                    'team_name' => $row['team']['name'] ?? null,
                    'team_logo' => $row['team']['logo'] ?? null,
                    'played' => $row['all']['played'] ?? null,
                    'win' => $row['all']['win'] ?? null,
                    'draw' => $row['all']['draw'] ?? null,
                    'lose' => $row['all']['lose'] ?? null,
                    'goals_for' => $row['all']['goals']['for'] ?? null,
                    'goals_against' => $row['all']['goals']['against'] ?? null,
                    'points' => $row['points'] ?? null,
                ])->values(),
                'team_stats' => ($homeStats && $awayStats) ? [
                    'home' => $this->teamStats($homeStats),
                    'away' => $this->teamStats($awayStats),
                ] : null,
                'analysis' => $analysis,
                'factors' => $factors,
                'related' => $related->map(fn ($p) => [
                    'slug' => $p->slug,
                    'home_team' => $p->home_team,
                    'away_team' => $p->away_team,
                    'category' => $p->category,
                    'tip' => $p->tip,
                    'match_date' => $p->match_date ? $p->match_date->format('Y-m-d') : null,
                ])->values(),
                'category_slug' => $categorySlug,
                'comments' => $comments->map(fn ($c) => [
                    'name' => $c->name,
                    'comment' => $c->comment,
                    'created_at' => $c->created_at ? $c->created_at->toIso8601String() : null,
                ])->values(),
            ],
        ]);
    }

    private function teamStats(array $stats): array
    {
        return [
            'played' => $stats['fixtures']['played']['total'] ?? 0,
            'wins' => $stats['fixtures']['wins']['total'] ?? 0,
            'draws' => $stats['fixtures']['draws']['total'] ?? 0,
            'losses' => $stats['fixtures']['loses']['total'] ?? 0,
            'goals_for' => $stats['goals']['for']['total']['total'] ?? 0,
            'goals_against' => $stats['goals']['against']['total']['total'] ?? 0,
        ];
    }

    private function fixtureFromPrediction(Prediction $prediction): array
    {
        return [
            'fixture' => [
                'id' => $prediction->fixture_id,
                'date' => $prediction->match_date ? $prediction->match_date->toIso8601String() : null,
                'status' => ['short' => null, 'elapsed' => null],
                'venue' => ['name' => null],
                'referee' => null,
            ],
            'league' => [
                'id' => $prediction->league_id,
                'name' => $prediction->league_name,
                'country' => $prediction->league_country,
                'logo' => $prediction->league_logo,
                'season' => null,
            ],
            'teams' => [
                'home' => [
                    'id' => null,
                    'name' => $prediction->home_team,
                    'logo' => $prediction->home_team_logo,
                ],
                'away' => [
                    'id' => null,
                    'name' => $prediction->away_team,
                    'logo' => $prediction->away_team_logo,
                ],
            ],
            'goals' => [
                'home' => $prediction->home_score,
                'away' => $prediction->away_score,
            ],
        ];
    }
}
