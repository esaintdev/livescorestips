<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Services\MatchContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchPageContentTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://www.livescorestips.test';

    private function prediction(array $attributes = []): Prediction
    {
        return Prediction::factory()->create(array_merge([
            'fixture_id' => 555001,
            'slug' => 'home-city-vs-away-town',
            'home_team' => 'Home City',
            'away_team' => 'Away Town',
            'match_date' => '2026-09-20 15:00:00',
            'tip' => 'Over 1.5',
            'category' => 'Over 1.5 Goals',
            'status' => 'pending',
        ], $attributes));
    }

    private function renderPage(Prediction $prediction, array $overrides = [])
    {
        $this->fakeApi(array_merge([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => $prediction->fixture_id],
                'teams' => [
                    'home' => ['id' => 101, 'name' => $prediction->home_team, 'logo' => null],
                    'away' => ['id' => 202, 'name' => $prediction->away_team, 'logo' => null],
                ],
            ]),
        ], $overrides));

        return $this->get(self::HOST.'/match/'.$prediction->slug.'/'.$prediction->match_date->format('Y-m-d'));
    }

    public function test_empty_stats_and_standings_are_not_rendered(): void
    {
        $prediction = $this->prediction();

        $response = $this->renderPage($prediction);

        $response->assertStatus(200);
        $response->assertDontSee('No Stats Available');
        $response->assertDontSee('No Standings Available');
        $response->assertDontSee('Team Stats');
        $response->assertDontSee('League Standings');
    }

    public function test_empty_h2h_is_not_rendered(): void
    {
        $prediction = $this->prediction();

        $response = $this->renderPage($prediction);

        $response->assertDontSee('No H2H History');
    }

    public function test_analysis_and_factors_are_rendered(): void
    {
        $prediction = $this->prediction();

        $response = $this->renderPage($prediction, [
            'fetchTeamForm' => ['W', 'W', 'D'],
            'fetchHeadToHead' => [
                ['goals' => ['home' => 2, 'away' => 1], 'teams' => ['home' => ['id' => 101, 'name' => 'Home City'], 'away' => ['id' => 202, 'name' => 'Away Town']], 'fixture' => ['date' => '2026-01-01']],
            ],
        ]);

        $response->assertSee('Prediction Analysis');
        $response->assertSee('Key Match Factors');
    }

    public function test_finished_page_shows_summary_and_result(): void
    {
        $prediction = $this->prediction([
            'fixture_id' => 555020,
            'slug' => 'aik-stockholm-vs-mjallby-aif',
            'home_team' => 'AIK Stockholm',
            'away_team' => 'Mjallby AIF',
            'tip' => 'Over 1.5',
            'category' => 'Over 1.5 Goals',
        ]);

        $response = $this->renderPage($prediction, [
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555020, 'status' => ['short' => 'FT', 'elapsed' => 90]],
                'teams' => [
                    'home' => ['id' => 101, 'name' => 'AIK Stockholm', 'logo' => null],
                    'away' => ['id' => 202, 'name' => 'Mjallby AIF', 'logo' => null],
                ],
                'goals' => ['home' => 1, 'away' => 1],
            ]),
        ]);

        $response->assertSee('Prediction Result');
        $response->assertSee('WON');
        $response->assertSee('1:1');
        $response->assertSee('producing 2 total goals', false);
        $response->assertSee('Over 1.5 prediction therefore landed successfully', false);
    }

    public function test_original_expert_prediction_remains_visible(): void
    {
        $prediction = $this->prediction();

        $response = $this->renderPage($prediction);

        $response->assertSee('Expert Prediction');
        $response->assertSee('Over 1.5');
    }

    public function test_related_category_link_is_server_rendered(): void
    {
        $prediction = $this->prediction();

        $response = $this->renderPage($prediction);

        $response->assertSee('Related Predictions');
        $response->assertSee(self::HOST.'/over-1-5-goals', false);
    }

    public function test_related_matches_are_crawlable_and_exclude_same_fixture(): void
    {
        $prediction = $this->prediction();

        $related = $this->prediction([
            'fixture_id' => 555099,
            'slug' => 'other-city-vs-other-town',
            'home_team' => 'Other City',
            'away_team' => 'Other Town',
            'match_date' => '2026-09-21 15:00:00',
        ]);

        // Same provider fixture, different record/slug — must never be linked.
        $sameFixture = $this->prediction([
            'fixture_id' => 555001,
            'slug' => 'home-city-vs-away-town-dup',
            'tip' => 'BTTS',
            'category' => 'Both Teams To Score',
        ]);

        $response = $this->renderPage($prediction);

        $response->assertSee('Other City vs Other Town');
        $response->assertSee(self::HOST.'/match/other-city-vs-other-town/2026-09-21', false);
        $response->assertDontSee('home-city-vs-away-town-dup');

        // Service-level guarantees.
        $service = app(MatchContentService::class);
        $results = $service->related($prediction);
        $this->assertTrue($results->every(fn ($r) => $r->fixture_id !== $prediction->fixture_id));
        $this->assertTrue($results->every(fn ($r) => $r->id !== $prediction->id));
    }

    public function test_related_links_use_canonical_record_per_fixture(): void
    {
        $prediction = $this->prediction();

        // Canonical record first (real tip), then an alternate duplicate.
        $canonical = $this->prediction([
            'fixture_id' => 555077,
            'slug' => 'canonical-city-vs-other-town',
            'home_team' => 'Canonical City',
            'away_team' => 'Other Town',
            'match_date' => '2026-09-21 15:00:00',
        ]);

        $this->prediction([
            'fixture_id' => 555077,
            'slug' => 'canonical-city-vs-other-town-555077-2',
            'home_team' => 'Canonical City',
            'away_team' => 'Other Town',
            'match_date' => '2026-09-21 15:00:00',
            'category' => 'Home Win',
        ]);

        $response = $this->renderPage($prediction);

        $response->assertSee(self::HOST.'/match/canonical-city-vs-other-town/2026-09-21', false);
        $response->assertDontSee('canonical-city-vs-other-town-555077-2');
    }

    public function test_finished_page_does_not_fabricate_season_stats(): void
    {
        $prediction = $this->prediction(['fixture_id' => 555030, 'slug' => 'no-stats-vs-no-stats']);

        $response = $this->renderPage($prediction);

        $response->assertDontSee('have scored');
        $response->assertDontSee('have conceded');
    }
}
