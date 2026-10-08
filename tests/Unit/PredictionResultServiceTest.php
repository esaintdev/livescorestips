<?php

namespace Tests\Unit;

use App\Services\PredictionResultService;
use Tests\TestCase;

class PredictionResultServiceTest extends TestCase
{
    private PredictionResultService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PredictionResultService;
    }

    /**
     * @dataProvider outcomeProvider
     */
    public function test_it_determines_outcomes_for_every_supported_market(string $tip, int $home, int $away, string $expected): void
    {
        $this->assertSame($expected, $this->service->determineOutcome($tip, $home, $away));
    }

    public static function outcomeProvider(): array
    {
        return [
            // 1x2
            ['1', 2, 1, 'won'],
            ['1', 1, 1, 'lost'],
            ['2', 0, 1, 'won'],
            ['2', 2, 0, 'lost'],
            ['X', 1, 1, 'won'],
            ['X', 1, 0, 'lost'],
            ['HOME', 1, 0, 'won'],
            ['AWAY', 0, 1, 'won'],
            ['DRAW', 0, 0, 'won'],
            // Double chance
            ['1X', 1, 1, 'won'],
            ['1X', 0, 1, 'lost'],
            ['X2', 1, 1, 'won'],
            ['X2', 1, 0, 'lost'],
            ['12', 1, 0, 'won'],
            ['12', 1, 1, 'lost'],
            // Over / Under
            ['Over 1.5', 1, 1, 'won'],
            ['Over 1.5', 1, 0, 'lost'],
            ['Over 2.5', 2, 1, 'won'],
            ['Over 2.5', 1, 1, 'lost'],
            ['Under 2.5', 1, 1, 'won'],
            ['Under 2.5', 2, 1, 'lost'],
            ['Under 3.5', 1, 1, 'won'],
            // BTTS / NG
            ['BTTS', 1, 1, 'won'],
            ['BTTS', 1, 0, 'lost'],
            ['GG', 2, 1, 'won'],
            ['NG', 1, 0, 'won'],
            ['NG', 1, 1, 'lost'],
            // Correct score
            ['2-1', 2, 1, 'won'],
            ['2-1', 1, 1, 'lost'],
        ];
    }

    public function test_finished_status_settles(): void
    {
        $this->assertSame('won', $this->service->resolveStatus('Over 1.5', 1, 1, 'FT'));
        $this->assertSame('lost', $this->service->resolveStatus('Over 2.5', 1, 1, 'FT'));
    }

    public function test_cancelled_and_abandoned_become_void(): void
    {
        foreach (['CANC', 'ABD', 'AWD', 'WO'] as $status) {
            $this->assertSame('void', $this->service->resolveStatus('1X', 0, 0, $status));
        }
    }

    public function test_postponed_and_suspended_stay_pending(): void
    {
        foreach (['PST', 'SUSP', 'INT'] as $status) {
            $this->assertSame('pending', $this->service->resolveStatus('1X', 1, 1, $status));
        }
    }

    public function test_live_and_not_started_stay_pending(): void
    {
        foreach (['NS', 'TBD', '1H', 'HT', '2H', 'ET', 'P'] as $status) {
            $this->assertSame('pending', $this->service->resolveStatus('1X', 1, 1, $status));
        }
    }

    public function test_unknown_tip_is_pending_when_finished(): void
    {
        $this->assertSame('pending', $this->service->resolveStatus('Some Unknown Market', 1, 1, 'FT'));
    }

    public function test_missing_scores_stay_pending(): void
    {
        $this->assertSame('pending', $this->service->resolveStatus('1X', null, null, 'FT'));
    }
}
