<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\MatchContentService;
use App\Services\MatchPageDataService;
use App\Services\PredictionResultService;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FixtureController extends Controller
{
    protected $apiService;

    protected $contentService;

    protected $resultService;

    protected $pageData;

    public function __construct(
        ApiFootballService $apiService,
        MatchContentService $contentService,
        PredictionResultService $resultService,
        MatchPageDataService $pageData
    ) {
        $this->apiService = $apiService;
        $this->contentService = $contentService;
        $this->resultService = $resultService;
        $this->pageData = $pageData;
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

        // Form, H2H, standings and team stats: one cached snapshot per fixture
        [
            'homeForm' => $homeForm,
            'awayForm' => $awayForm,
            'homeRecent' => $homeRecent,
            'awayRecent' => $awayRecent,
            'headToHead' => $headToHead,
            'standings' => $standings,
            'homeStats' => $homeStats,
            'awayStats' => $awayStats,
        ] = $this->pageData->get($fixture, $prediction);

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
        $preview = [];
        $h2hSummary = null;
        $oddsValue = null;
        $whyThisTip = [];
        $faq = [];
        $homeRecentRows = [];
        $awayRecentRows = [];

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
                'home_recent' => $homeRecent,
                'away_recent' => $awayRecent,
            ];

            $analysis = $this->contentService->analysis($prediction, $ctx);
            $factors = $this->contentService->factors($prediction, $ctx);
            $related = $this->contentService->related($prediction);
            $categorySlug = $this->contentService->categorySlug($prediction);

            $preview = $this->contentService->preview($prediction, $fixture, $ctx);
            $h2hSummary = $this->contentService->h2hSummary($headToHead, $homeTeamId, $awayTeamId, $ctx['home_team'], $ctx['away_team']);
            $oddsValue = $this->contentService->oddsValue($prediction);
            $whyThisTip = $this->contentService->whyThisTip($prediction, $ctx);
            $faq = $this->contentService->faq($prediction, $fixture, $ctx, $predictionResult);
            $homeRecentRows = $this->contentService->recentRows($homeRecent, $homeTeamId);
            $awayRecentRows = $this->contentService->recentRows($awayRecent, $awayTeamId);
        }

        $comments = $prediction
            ? $prediction->comments()->approved()->latest()->get()
            : collect();

        $structuredData = $this->structuredData($fixture, $prediction, $categorySlug);

        if (! empty($faq)) {
            $structuredData[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($item) => [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
                ], $faq),
            ];
        }

        $exploreLinks = $this->exploreLinks($fixture, $prediction);

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
            'structuredData',
            'preview',
            'h2hSummary',
            'oddsValue',
            'whyThisTip',
            'faq',
            'homeRecentRows',
            'awayRecentRows',
            'exploreLinks'
        ));
    }

    /**
     * Internal links from a match to its league and country hubs (the
     * market hub is already linked from "Related Predictions").
     *
     * @return array<int, array{label: string, url: string}>
     */
    private function exploreLinks(array $fixture, ?Prediction $prediction): array
    {
        $links = [];
        $leagueId = $fixture['league']['id'] ?? ($prediction->league_id ?? null);
        $leagueName = $fixture['league']['name'] ?? ($prediction->league_name ?? null);

        $popular = collect($this->apiService->getPopularLeagues())->firstWhere('id', (int) $leagueId);
        if ($popular && ! str_starts_with($popular['name'], 'League #')) {
            $links[] = ['label' => $popular['name'].' predictions', 'url' => route('league.show', ['id' => $popular['id'], 'slug' => $popular['slug']])];
        }

        $country = $prediction->league_country ?? null;
        if ($country && $country !== 'World') {
            $links[] = ['label' => str_replace('-', ' ', $country).' football predictions', 'url' => route('country.show', ['name' => $country])];
        }

        $links[] = ['label' => 'All football predictions today', 'url' => route('predictions.all')];

        return $links;
    }

    /**
     * Build JSON-LD structured data for the match page: a SportsEvent and a BreadcrumbList.
     * Valid Schema.org structured data with guaranteed location for Google Search.
     *
     * @return array<int, array>
     */
    public function structuredData(array $fixture, ?Prediction $prediction, ?string $categorySlug): array
    {
        $homeName = $fixture['teams']['home']['name'] ?? null;
        $awayName = $fixture['teams']['away']['name'] ?? null;

        if (! $homeName || ! $awayName) {
            return [];
        }

        $url = ($prediction && $prediction->slug && $prediction->match_date)
            ? route('fixture.show', [$prediction->slug, $prediction->match_date->format('Y-m-d')])
            : url()->current();

        $leagueName = ! empty($fixture['league']['name']) ? trim($fixture['league']['name']) : null;
        $description = 'Live score, predictions, head-to-head statistics, and match preview for '.$homeName.' vs '.$awayName.($leagueName ? ' in '.$leagueName : '').'.';

        $venueName = ! empty($fixture['fixture']['venue']['name'])
            ? trim($fixture['fixture']['venue']['name'])
            : trim($homeName).' Stadium';

        $venueCity = ! empty($fixture['fixture']['venue']['city'])
            ? trim($fixture['fixture']['venue']['city'])
            : null;

        $country = ! empty($fixture['league']['country'])
            ? trim($fixture['league']['country'])
            : null;

        $location = [
            '@type' => 'Place',
            'name' => $venueName,
        ];

        $address = ['@type' => 'PostalAddress'];
        if ($venueCity) {
            $address['addressLocality'] = $venueCity;
        }
        if ($country) {
            $address['addressCountry'] = $country;
        }
        if (! empty($address['addressLocality']) || ! empty($address['addressCountry'])) {
            $location['address'] = $address;
        }

        $event = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $homeName.' vs '.$awayName,
            'description' => $description,
            'url' => $url,
            'sport' => 'Football',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => $this->eventStatus($fixture['fixture']['status']['short'] ?? null),
            'location' => $location,
            'competitor' => [
                ['@type' => 'SportsTeam', 'name' => $homeName],
                ['@type' => 'SportsTeam', 'name' => $awayName],
            ],
        ];

        $startDate = null;
        if (! empty($fixture['fixture']['date'])) {
            try {
                $startDate = Carbon::parse($fixture['fixture']['date']);
            } catch (\Throwable $e) {
                $startDate = null;
            }
        }

        if (! $startDate && $prediction && $prediction->match_date) {
            $startDate = $prediction->match_date instanceof Carbon
                ? $prediction->match_date
                : Carbon::parse($prediction->match_date);
        }

        if ($startDate) {
            $event['startDate'] = $startDate->toIso8601String();
            $event['endDate'] = $startDate->copy()->addMinutes(110)->toIso8601String();
        }

        $images = array_values(array_filter([
            $fixture['teams']['home']['logo'] ?? null,
            $fixture['teams']['away']['logo'] ?? null,
            $fixture['league']['logo'] ?? null,
        ]));

        if (! empty($images)) {
            $event['image'] = $images;
        }

        if ($leagueName) {
            $organizer = [
                '@type' => 'Organization',
                'name' => $leagueName,
            ];
            if (! empty($fixture['league']['logo'])) {
                $organizer['logo'] = $fixture['league']['logo'];
            }
            $event['organizer'] = $organizer;
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
