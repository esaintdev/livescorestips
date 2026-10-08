<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_regeneration_without_odds_does_not_delete_existing_prediction(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555001,
            'tip' => 'Awaiting odds',
            'slug' => 'home-city-vs-away-town',
            'status' => 'pending',
        ]);

        $this->fakeApi([
            'fetchFixturesByRange' => [$this->fixturePayload()],
            'fetchOddsByDate' => [],
            'fetchOddsByFixture' => [],
        ]);

        app(PredictionService::class)->generateAutoPredictions('2026-09-21', 5);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->id,
            'fixture_id' => 555001,
            'slug' => 'home-city-vs-away-town',
        ]);
    }

    public function test_regeneration_with_no_analysis_does_not_delete_existing_prediction(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555002,
            'tip' => 'Awaiting odds',
            'slug' => 'home-city-vs-away-town-2',
            'status' => 'pending',
        ]);

        // An odds entry whose bookmaker exposes no supported market, so neither
        // quick analysis nor the fallback can produce a tip.
        $emptyOdds = [[
            'fixture' => ['id' => 555002],
            'bookmakers' => [[
                'name' => '1xBet',
                'bets' => [[
                    'name' => 'Unsupported Market',
                    'values' => [['value' => 'Whatever', 'odd' => 2.0]],
                ]],
            ]],
        ]];

        $this->fakeApi([
            'fetchFixturesByRange' => [$this->fixturePayload(['fixture' => ['id' => 555002]])],
            'fetchOddsByDate' => $emptyOdds,
            'fetchOddsByFixture' => [],
        ]);

        app(PredictionService::class)->generateAutoPredictions('2026-09-21', 5);

        $this->assertDatabaseHas('predictions', ['id' => $prediction->id]);
    }

    public function test_fixture_no_longer_not_started_does_not_delete_existing_prediction(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555003,
            'tip' => 'Awaiting odds',
            'slug' => 'home-city-vs-away-town-3',
        ]);

        $finishedFixture = $this->fixturePayload([
            'fixture' => ['id' => 555003, 'status' => ['short' => 'FT', 'elapsed' => 90]],
            'goals' => ['home' => 1, 'away' => 1],
        ]);

        $this->fakeApi([
            'fetchFixturesByRange' => [$finishedFixture],
            'fetchOddsByDate' => [],
        ]);

        app(PredictionService::class)->generateAutoPredictions('2026-09-21', 5);

        $this->assertDatabaseHas('predictions', ['id' => $prediction->id]);
    }

    public function test_successful_regeneration_updates_in_place_and_preserves_slug(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555001,
            'tip' => 'Awaiting odds',
            'slug' => 'home-city-vs-away-town',
            'status' => 'pending',
        ]);

        $fixture = $this->fixturePayload();

        $updated = app(PredictionService::class)->createPredictionRecord(
            $fixture,
            ['tip' => '1', 'category' => 'Home Win', 'market_type' => '1x2'],
            ['value' => 1.50, 'bookmaker' => '1xBet'],
            ['W', 'D'],
            ['L', 'L'],
            $prediction
        );

        $fresh = $prediction->fresh();

        $this->assertSame($prediction->id, $updated->id, 'The record must be updated in place.');
        $this->assertSame($prediction->id, $fresh->id);
        $this->assertSame('home-city-vs-away-town', $fresh->slug, 'The slug must be preserved.');
        $this->assertSame('1', $fresh->tip);
        $this->assertSame(1, Prediction::where('fixture_id', 555001)->count());
        $this->assertNotNull($fresh->content_updated_at);
    }

    public function test_api_outage_does_not_delete_existing_prediction(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555004,
            'tip' => '1X',
            'slug' => 'home-city-vs-away-town-4',
            'status' => 'pending',
        ]);

        $this->fakeApi([
            'fetchFixturesByRange' => function () {
                throw new \RuntimeException('provider outage');
            },
        ]);

        try {
            app(PredictionService::class)->generateAutoPredictions('2026-09-21', 5);
        } catch (\RuntimeException $e) {
            // Expected — the provider is down.
        }

        $this->assertDatabaseHas('predictions', ['id' => $prediction->id]);
    }
}
