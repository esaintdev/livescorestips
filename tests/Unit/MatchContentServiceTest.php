<?php

namespace Tests\Unit;

use App\Models\Prediction;
use App\Services\MatchContentService;
use PHPUnit\Framework\TestCase;

class MatchContentServiceTest extends TestCase
{
    private MatchContentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchContentService;
    }

    private function prediction(array $attributes = []): Prediction
    {
        $prediction = new Prediction;
        $prediction->forceFill(array_merge([
            'home_team' => 'Home City',
            'away_team' => 'Away Town',
            'tip' => 'Over 1.5',
            'category' => 'Over 1.5 Goals',
            'probability' => 80,
            'odds' => 1.25,
        ], $attributes));

        return $prediction;
    }

    public function test_analysis_is_market_aware_for_over(): void
    {
        $analysis = $this->service->analysis($this->prediction(), [
            'home_form' => ['W', 'W', 'D'],
            'away_form' => ['L', 'D', 'L'],
            'h2h' => [
                ['goals' => ['home' => 2, 'away' => 1]],
                ['goals' => ['home' => 1, 'away' => 1]],
            ],
        ]);

        $this->assertStringContainsString('Over 1.5', $analysis);
        $this->assertStringContainsString('Home City', $analysis);
        $this->assertStringContainsString('Away Town', $analysis);
    }

    public function test_analysis_adapts_to_btts_market(): void
    {
        $analysis = $this->service->analysis($this->prediction([
            'tip' => 'BTTS',
            'category' => 'Both Teams To Score',
        ]), [
            'h2h' => [
                ['goals' => ['home' => 1, 'away' => 1]],
                ['goals' => ['home' => 2, 'away' => 0]],
            ],
        ]);

        $this->assertStringContainsString('BTTS', $analysis);
    }

    public function test_analysis_without_context_still_uses_real_probability(): void
    {
        $analysis = $this->service->analysis($this->prediction());

        $this->assertStringContainsString('80', $analysis);
        $this->assertStringContainsString('Over 1.5', $analysis);
    }

    public function test_factors_do_not_fabricate_stats_when_data_missing(): void
    {
        $factors = $this->service->factors($this->prediction());
        $joined = strtolower(implode(' ', $factors));

        // Only the market rationale is available; no stats/form/h2h claims.
        $this->assertNotEmpty($factors);
        $this->assertStringNotContainsString('have scored', $joined);
        $this->assertStringNotContainsString('league positions', $joined);
        $this->assertStringNotContainsString('head-to-head', $joined);
        $this->assertStringNotContainsString('home matches', $joined);
    }

    public function test_factors_use_form_and_h2h_when_available(): void
    {
        $factors = $this->service->factors($this->prediction(), [
            'home_form' => ['W', 'W', 'W'],
            'away_form' => ['L', 'L', 'L'],
            'h2h' => [
                ['goals' => ['home' => 3, 'away' => 2]],
                ['goals' => ['home' => 2, 'away' => 2]],
            ],
        ]);

        $this->assertGreaterThanOrEqual(3, count($factors));
        $this->assertLessThanOrEqual(6, count($factors));
        $this->assertStringContainsString('better recent form', implode(' ', $factors));
        $this->assertStringContainsString('H2H', implode(' ', $factors));
    }

    public function test_category_slug_mapping(): void
    {
        $this->assertSame('double-chance', $this->service->categorySlug($this->prediction(['category' => 'Double Chance'])));
        $this->assertSame('over-1-5-goals', $this->service->categorySlug($this->prediction(['category' => 'Over 1.5 Goals'])));
        $this->assertNull($this->service->categorySlug($this->prediction(['category' => 'Made Up Market'])));
    }
}
