<?php

namespace Tests;

use App\Services\ApiFootballService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The production AppServiceProvider forces HTTPS. Tests use a local
        // HTTP host, so align generated URLs with the test requests.
        URL::forceScheme('http');
    }

    /**
     * Bind a fake ApiFootballService so tests never hit the network.
     *
     * @param  array<string, mixed>  $overrides  method => value|Closure
     */
    protected function fakeApi(array $overrides = []): void
    {
        $defaults = [
            'fetchFixtures' => [],
            'fetchFixturesByRange' => [],
            'fetchOdds' => [],
            'fetchOddsByDate' => [],
            'fetchOddsByFixture' => [],
            'fetchTeamForm' => [],
            'fetchTeamGoalMetrics' => ['avg_total_goals' => null, 'btts_rate' => null],
            'fetchFixtureDetails' => null,
            'fetchHeadToHead' => [],
            'fetchLeagueStandings' => [],
            'fetchTeamStatistics' => null,
            'fetchLiveFixtures' => [],
            'fetchLiveFixturesCount' => 0,
            'fetchLeagueResults' => [],
            'fetchLeagueUpcomingFixtures' => [],
            'getLeagueById' => null,
            'fetchAllLeagues' => [],
            'fetchCountries' => [],
            'getPopularLeagues' => [],
        ];

        $this->mock(ApiFootballService::class, function ($mock) use ($defaults, $overrides) {
            foreach (array_merge($defaults, $overrides) as $method => $value) {
                $expectation = $mock->shouldReceive($method);
                if ($value instanceof \Closure) {
                    $expectation->andReturnUsing($value);
                } else {
                    $expectation->andReturn($value);
                }
            }
        });
    }

    /**
     * Minimal provider fixture payload.
     */
    protected function fixturePayload(array $overrides = []): array
    {
        $base = [
            'fixture' => [
                'id' => 555001,
                'date' => now()->addDay()->toIso8601String(),
                'status' => ['short' => 'NS', 'elapsed' => null],
                'venue' => ['name' => 'Test Stadium'],
                'referee' => 'A Referee',
            ],
            'league' => [
                'id' => 39,
                'name' => 'Premier League',
                'country' => 'England',
                'logo' => null,
                'season' => 2026,
            ],
            'teams' => [
                'home' => ['id' => 101, 'name' => 'Home City', 'logo' => null],
                'away' => ['id' => 202, 'name' => 'Away Town', 'logo' => null],
            ],
            'goals' => ['home' => null, 'away' => null],
        ];

        return array_replace_recursive($base, $overrides);
    }
}
