<?php

namespace Tests\Feature;

use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionCapTest extends TestCase
{
    use RefreshDatabase;

    private string $date = '2026-10-05';

    private function oddsEntry(int $fixtureId): array
    {
        return [
            'fixture' => ['id' => $fixtureId],
            'bookmakers' => [[
                'name' => '1xBet',
                'bets' => [[
                    'name' => 'Match Winner',
                    'values' => [
                        ['value' => 'Home', 'odd' => 1.60],
                        ['value' => 'Draw', 'odd' => 3.40],
                        ['value' => 'Away', 'odd' => 5.00],
                    ],
                ]],
            ]],
        ];
    }

    private function fakeEngine(): void
    {
        $this->fakeApi([
            'fetchFixtures' => [$this->fixturePayload([
                'fixture' => ['id' => 555001, 'date' => $this->date.'T15:00:00+00:00', 'status' => ['short' => 'NS', 'elapsed' => null]],
            ])],
            'fetchOddsByDate' => [$this->oddsEntry(555001)],
            'fetchOddsByFixture' => [],
            'fetchTeamForm' => ['W', 'W', 'D'],
            'fetchTeamGoalMetrics' => ['avg_total_goals' => 3.0, 'btts_rate' => 0.6],
            'fetchHeadToHead' => [],
        ]);
    }

    private function seedExisting(int $count): void
    {
        $cats = ['Double Chance', 'Over 1.5 Goals', 'Over 2.5 Goals', 'Both Teams To Score'];
        for ($i = 0; $i < $count; $i++) {
            Prediction::factory()->create([
                'fixture_id' => 555001,
                'slug' => 'cap-test-'.$i,
                'match_date' => $this->date.' 15:00:00',
                'category' => $cats[$i],
                'tip' => '1X',
                'status' => 'pending',
            ]);
        }
    }

    public function test_fixture_below_cap_can_gain_a_prediction(): void
    {
        config(['predictions.max_per_fixture_per_date' => 3]);
        $this->seedExisting(2);
        $this->fakeEngine();

        $this->artisan('prediction:fill-all', [
            '--categories' => 'Home Win',
            '--date' => $this->date,
            '--days' => 1,
            '--limit' => 5,
        ])->assertSuccessful();

        $this->assertSame(3, Prediction::where('fixture_id', 555001)->whereDate('match_date', $this->date)->count());
    }

    public function test_fixture_at_cap_is_skipped(): void
    {
        config(['predictions.max_per_fixture_per_date' => 3]);
        $this->seedExisting(3);
        $this->fakeEngine();

        $this->artisan('prediction:fill-all', [
            '--categories' => 'Home Win',
            '--date' => $this->date,
            '--days' => 1,
            '--limit' => 5,
        ])->assertSuccessful();

        $this->assertSame(3, Prediction::where('fixture_id', 555001)->whereDate('match_date', $this->date)->count());
    }

    public function test_cap_disabled_when_set_to_zero(): void
    {
        config(['predictions.max_per_fixture_per_date' => 0]);
        $this->seedExisting(3);
        $this->fakeEngine();

        $this->artisan('prediction:fill-all', [
            '--categories' => 'Home Win',
            '--date' => $this->date,
            '--days' => 1,
            '--limit' => 5,
        ])->assertSuccessful();

        $this->assertSame(4, Prediction::where('fixture_id', 555001)->whereDate('match_date', $this->date)->count());
    }

    public function test_rescheduled_fixture_moves_existing_records_to_new_date(): void
    {
        config(['predictions.max_per_fixture_per_date' => 3]);

        $old = Prediction::factory()->create([
            'fixture_id' => 555001,
            'slug' => 'reschedule-test',
            'match_date' => '2026-09-01 15:00:00',
            'category' => 'Double Chance',
            'tip' => '1X',
            'status' => 'pending',
        ]);

        $this->fakeEngine();

        $this->artisan('prediction:fill-all', [
            '--categories' => 'Home Win',
            '--date' => $this->date,
            '--days' => 1,
            '--limit' => 5,
        ])->assertSuccessful();

        // The existing record is moved onto the new date in place (id/slug intact).
        $fresh = $old->fresh();
        $this->assertSame($this->date, $fresh->match_date->format('Y-m-d'));
        $this->assertSame('reschedule-test', $fresh->slug);

        // No stale duplicate remains on the old date.
        $this->assertSame(0, Prediction::where('fixture_id', 555001)->whereDate('match_date', '2026-09-01')->count());

        // Moved record + the new market both sit on the new date.
        $this->assertSame(2, Prediction::where('fixture_id', 555001)->whereDate('match_date', $this->date)->count());
    }
}
