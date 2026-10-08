<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Services\ApiFootballService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LeagueStandingsTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://www.livescorestips.test';

    /**
     * Regression test for the recurring 500 on /league/{id}:
     * two callers share one cache key but request different shapes. The cache
     * must always store ALL groups, so the league page never receives the
     * all-groups array and crashes on $row['rank'].
     */
    public function test_standings_cache_shape_is_consistent_regardless_of_flag(): void
    {
        Cache::flush();

        Http::fake([
            '*' => Http::response([
                'response' => [[
                    'league' => [
                        'standings' => [
                            [
                                ['rank' => 1, 'team' => ['id' => 101, 'name' => 'Alpha']],
                                ['rank' => 2, 'team' => ['id' => 102, 'name' => 'Beta']],
                            ],
                            [
                                ['rank' => 1, 'team' => ['id' => 201, 'name' => 'Gamma']],
                            ],
                        ],
                    ],
                ]],
            ], 200),
        ]);

        $service = app(ApiFootballService::class);

        // Fixture page requests ALL groups first.
        $allGroups = $service->fetchLeagueStandings(848, 2026, true);
        $this->assertCount(2, $allGroups, 'Expected two groups');
        $this->assertCount(2, $allGroups[0], 'First group should hold two rows');

        // League page must now receive the FIRST GROUP (rows), not all groups.
        $firstGroup = $service->fetchLeagueStandings(848, 2026, false);
        $this->assertCount(2, $firstGroup, 'Expected a group of rows, not groups');
        $this->assertArrayHasKey('rank', $firstGroup[0], 'Expected a standings row, not a group');
        $this->assertSame('Alpha', $firstGroup[0]['team']['name']);
    }

    public function test_league_page_renders_when_standings_rows_are_partial(): void
    {
        Prediction::factory()->create([
            'fixture_id' => 94,
            'league_id' => 94,
            'slug' => 'some-team-vs-other-team',
        ]);

        $this->fakeApi([
            'getLeagueById' => [
                'id' => 94,
                'name' => 'Primeira Liga',
                'country' => 'Portugal',
                'logo' => null,
                'flag' => null,
            ],
            // Rows missing rank/all/goals — must not crash the view.
            'fetchLeagueStandings' => [
                ['team' => ['id' => 1, 'name' => 'Partial FC', 'logo' => null]],
            ],
            'fetchLeagueResults' => [],
            'fetchLeagueUpcomingFixtures' => [],
        ]);

        $response = $this->get(self::HOST.'/league/94/primeira-liga');

        $response->assertStatus(200);
        $response->assertSee('Primeira Liga');
    }

    public function test_league_page_200_with_valid_standings(): void
    {
        $this->fakeApi([
            'getLeagueById' => [
                'id' => 94,
                'name' => 'Primeira Liga',
                'country' => 'Portugal',
                'logo' => null,
                'flag' => null,
            ],
            'fetchLeagueStandings' => [
                [
                    'rank' => 1,
                    'team' => ['id' => 1, 'name' => 'Top FC', 'logo' => null],
                    'points' => 30,
                    'all' => ['played' => 10, 'win' => 9, 'draw' => 3, 'lose' => 1, 'goals' => ['for' => 20, 'against' => 5]],
                ],
            ],
            'fetchLeagueResults' => [],
            'fetchLeagueUpcomingFixtures' => [],
        ]);

        $response = $this->get(self::HOST.'/league/94/primeira-liga');

        $response->assertStatus(200);
        $response->assertSee('Top FC');
    }
}
