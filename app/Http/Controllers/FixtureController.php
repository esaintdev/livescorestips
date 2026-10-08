<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\MatchContentService;
use App\Services\PredictionResultService;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FixtureController extends Controller
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

    /**
     * Show a match page.
     *
     * A persisted prediction is the source of truth for the URL's existence.
     * The external provider only enriches the page; it never decides whether a
     * known match page exists. Invalid matches return 404, never a homepage
     * redirect.
     */
    public function show($slug, $date)
    {
        try {
            $prediction = $this->resolvePrediction($slug);

            if (! $prediction) {
                // No local record. Only a numeric provider fixture id may be
                // confirmed directly with the provider, and only when the
                // provider positively returns a real fixture.
                if (ctype_digit((string) $slug)) {
                    $fixture = $this->safeFetchFixture($slug);
                    if ($fixture) {
                        return $this->render($fixture, null, $slug, $date);
                    }
                }

                abort(404);
            }

            $canonicalDate = $prediction->match_date
                ? $prediction->match_date->format('Y-m-d')
                : $date;

            // Legacy fixture-id URL, or a record resolved by fixture id: send
            // the crawler to the canonical slug URL (single 301 hop).
            if ((string) $slug !== (string) $prediction->slug) {
                return redirect()->route('fixture.show', [$prediction->slug, $canonicalDate], 301);
            }

            // Wrong date for a real fixture: 301 to the canonical date.
            if ($date !== $canonicalDate) {
                return redirect()->route('fixture.show', [$prediction->slug, $canonicalDate], 301);
            }

            // Enrich from the provider. Failure falls back to persisted data.
            $fixture = $this->safeFetchFixture($prediction->fixture_id);

            if (! $fixture) {
                $fixture = $this->fixtureFromPrediction($prediction);
            }

            return $this->render($fixture, $prediction, $prediction->fixture_id, $canonicalDate);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error loading fixture details', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If we have a persisted record, still render it; otherwise 404.
            $prediction = $this->resolvePrediction($slug);
            if ($prediction) {
                return $this->render($this->fixtureFromPrediction($prediction), $prediction, $prediction->fixture_id, $date);
            }

            abort(404);
        } finally {
            \DB::disconnect();
        }
    }

    /**
     * Resolve a persisted prediction by slug, or by provider fixture id.
     */
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

    /**
     * Fetch provider fixture details without ever letting a provider failure
     * bubble up as a page failure.
     */
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

    /**
     * Render the match page, enriching with provider data where available.
     */
    private function render(array $fixture, ?Prediction $prediction, $fixtureId, string $date)
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

        // Settlement result (shared service — never duplicated in the view).
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

        $structuredData = $this->structuredData($fixture, $prediction, $categorySlug);

        return view('fixture.show', compact(
            'fixture',
            'prediction',
            'homeForm',
            'awayForm',
            'headToHead',
            'standings',
            'homeStats',
            'awayStats',
            'comments',
            'predictionResult',
            'summary',
            'analysis',
            'factors',
            'related',
            'categorySlug',
            'structuredData'
        ));
    }

    /**
     * Valid JSON-LD built only from data that is actually present.
     *
     * @return array<int, array>
     */
    private function structuredData(array $fixture, ?Prediction $prediction, ?string $categorySlug): array
    {
        $homeName = $fixture['teams']['home']['name'] ?? null;
        $awayName = $fixture['teams']['away']['name'] ?? null;

        if (! $homeName || ! $awayName) {
            return [];
        }

        $url = ($prediction && $prediction->slug && $prediction->match_date)
            ? route('fixture.show', [$prediction->slug, $prediction->match_date->format('Y-m-d')])
            : url()->current();

        $event = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $homeName.' vs '.$awayName,
            'url' => $url,
            'sport' => 'Football',
            'eventStatus' => $this->eventStatus($fixture['fixture']['status']['short'] ?? null),
            'competitor' => [
                ['@type' => 'SportsTeam', 'name' => $homeName],
                ['@type' => 'SportsTeam', 'name' => $awayName],
            ],
        ];

        if (! empty($fixture['fixture']['date'])) {
            $event['startDate'] = Carbon::parse($fixture['fixture']['date'])->toIso8601String();
        }

        if (! empty($fixture['fixture']['venue']['name'])) {
            $event['location'] = ['@type' => 'Place', 'name' => $fixture['fixture']['venue']['name']];
        }

        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ];
        $position = 2;

        if ($categorySlug && $prediction) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $prediction->category,
                'item' => route('category.show', $categorySlug),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $homeName.' vs '.$awayName,
            'item' => $url,
        ];

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return [$event, $breadcrumb];
    }

    private function eventStatus(?string $statusShort): string
    {
        $short = $statusShort !== null ? strtoupper(trim($statusShort)) : null;

        return match ($short) {
            'PST' => 'https://schema.org/EventPostponed',
            'CANC', 'ABD', 'AWD', 'WO' => 'https://schema.org/EventCancelled',
            default => 'https://schema.org/EventScheduled',
        };
    }

    /**
     * Build a minimal fixture array from persisted local data so a known match
     * page survives a provider outage.
     */
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
