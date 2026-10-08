<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function prediction(string $tip = 'Over 1.5'): Prediction
    {
        return Prediction::factory()->create([
            'fixture_id' => 555001,
            'tip' => $tip,
            'category' => 'Over 1.5 Goals',
            'status' => 'pending',
            'home_score' => null,
            'away_score' => null,
            'content_updated_at' => now()->subDay(),
        ]);
    }

    private function fixtureWith(string $status, ?int $home, ?int $away): array
    {
        return $this->fixturePayload([
            'fixture' => ['id' => 555001, 'status' => ['short' => $status, 'elapsed' => null]],
            'goals' => ['home' => $home, 'away' => $away],
        ]);
    }

    public function test_live_match_stays_pending(): void
    {
        $prediction = $this->prediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('2H', 1, 0)]);

        $status = app(PredictionService::class)->checkStatus($prediction);

        $this->assertSame('pending', $status);
        $this->assertSame('pending', $prediction->fresh()->status);
    }

    public function test_finished_match_settles_and_persists_score(): void
    {
        $prediction = $this->prediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('FT', 1, 1)]);

        $status = app(PredictionService::class)->checkStatus($prediction);

        $fresh = $prediction->fresh();
        $this->assertSame('won', $status);
        $this->assertSame('won', $fresh->status);
        $this->assertSame(1, $fresh->home_score);
        $this->assertSame(1, $fresh->away_score);
        $this->assertTrue($fresh->content_updated_at->greaterThan(now()->subMinutes(5)));
    }

    public function test_finished_match_can_be_lost(): void
    {
        $prediction = $this->prediction('Over 2.5');
        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('FT', 1, 1)]);

        $this->assertSame('lost', app(PredictionService::class)->checkStatus($prediction));
        $this->assertSame('lost', $prediction->fresh()->status);
    }

    public function test_cancelled_match_becomes_void(): void
    {
        $prediction = $this->prediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('CANC', null, null)]);

        $this->assertSame('void', app(PredictionService::class)->checkStatus($prediction));
        $this->assertSame('void', $prediction->fresh()->status);
    }

    public function test_postponed_match_is_not_voided_or_lost(): void
    {
        $prediction = $this->prediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('PST', null, null)]);

        $this->assertSame('pending', app(PredictionService::class)->checkStatus($prediction));
        $this->assertSame('pending', $prediction->fresh()->status);
    }

    public function test_api_failure_leaves_status_untouched(): void
    {
        $prediction = $this->prediction();
        $this->fakeApi(['fetchFixtureDetails' => null]);

        $this->assertSame('pending', app(PredictionService::class)->checkStatus($prediction));
        $this->assertSame('pending', $prediction->fresh()->status);
    }

    public function test_recheck_does_not_churn_when_result_unchanged(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555001,
            'tip' => 'Over 1.5',
            'category' => 'Over 1.5 Goals',
            'status' => 'won',
            'home_score' => 1,
            'away_score' => 1,
            'content_updated_at' => now()->subHours(3),
        ]);

        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('FT', 1, 1)]);

        $before = $prediction->content_updated_at;

        $this->assertSame('won', app(PredictionService::class)->checkStatus($prediction));

        $fresh = $prediction->fresh();
        $this->assertSame('won', $fresh->status);
        $this->assertTrue(
            $fresh->content_updated_at->equalTo($before),
            'content_updated_at must not change when nothing changed'
        );
    }

    public function test_recheck_corrects_result_after_extra_time(): void
    {
        // Settled too early at 90 minutes (1-1), but the match finished 2-2 after
        // extra time (PEN) — the re-check must flip it to LOST.
        $prediction = Prediction::factory()->create([
            'fixture_id' => 1643811,
            'tip' => '1-1',
            'category' => 'Correct Score',
            'status' => 'won',
            'home_score' => 1,
            'away_score' => 1,
        ]);

        $this->fakeApi(['fetchFixtureDetails' => $this->fixtureWith('PEN', 2, 2)]);

        $this->assertSame('lost', app(PredictionService::class)->checkStatus($prediction));

        $fresh = $prediction->fresh();
        $this->assertSame('lost', $fresh->status);
        $this->assertSame(2, (int) $fresh->home_score);
        $this->assertSame(2, (int) $fresh->away_score);
    }
}
