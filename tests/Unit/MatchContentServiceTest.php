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

    private function match(int $homeId, int $awayId, int $hg, int $ag, string $date = '2026-09-01T18:00:00+00:00'): array
    {
        return [
            'fixture' => ['id' => rand(1, 99999), 'date' => $date],
            'league' => ['id' => 39, 'name' => 'Premier League'],
            'teams' => ['home' => ['id' => $homeId, 'name' => "Team {$homeId}"], 'away' => ['id' => $awayId, 'name' => "Team {$awayId}"]],
            'goals' => ['home' => $hg, 'away' => $ag],
        ];
    }

    public function test_h2h_summary_attributes_wins_by_team_id_across_venues(): void
    {
        $h2h = [
            $this->match(1, 2, 2, 0, '2026-05-01T18:00:00+00:00'),
            $this->match(2, 1, 3, 1),   // team 2 wins at home
            $this->match(2, 1, 0, 1),   // team 1 wins away
            $this->match(1, 2, 1, 1),
        ];

        $text = $this->service->h2hSummary($h2h, 1, 2, 'Home City', 'Away Town');

        $this->assertStringContainsString('last 4 meetings, Home City have won 2, Away Town have won 1 and 1 ended level', $text);
        $this->assertStringContainsString('1 May 2026', $text);
        $this->assertNull($this->service->h2hSummary([], 1, 2, 'Home City', 'Away Town'));
    }

    public function test_odds_value_compares_implied_probability(): void
    {
        $this->assertStringContainsString('generous', $this->service->oddsValue($this->prediction(['odds' => 2.0, 'probability' => 60])));
        $this->assertStringContainsString('slightly more likely', $this->service->oddsValue($this->prediction(['odds' => 1.25, 'probability' => 70])));
        $this->assertStringContainsString('in line', $this->service->oddsValue($this->prediction(['odds' => 1.25, 'probability' => 80])));
        $this->assertNull($this->service->oddsValue($this->prediction(['odds' => null])));
    }

    public function test_recent_rows_and_why_this_tip_use_real_scores(): void
    {
        $recent = [$this->match(1, 3, 3, 1), $this->match(4, 1, 2, 2), $this->match(1, 5, 0, 1)];

        $rows = $this->service->recentRows($recent, 1);
        $this->assertSame(['W', 'D', 'L'], array_column($rows, 'result'));
        $this->assertSame('A', $rows[1]['venue']);

        $points = $this->service->whyThisTip($this->prediction(['tip' => 'Over 2.5', 'category' => 'Over 2.5 Goals']), [
            'home_recent' => $recent, 'home_team_id' => 1,
        ]);
        $this->assertContains("2 of Home City's last 3 matches went over 2.5 goals.", $points);
    }

    public function test_why_this_tip_and_faq_without_data_do_not_fabricate(): void
    {
        $this->assertSame([], $this->service->whyThisTip($this->prediction()));

        $faq = $this->service->faq($this->prediction(), []);
        $this->assertCount(1, $faq);
        $this->assertSame('What is the Home City vs Away Town prediction?', $faq[0]['q']);
        $this->assertStringContainsString('Over 1.5 (Over 1.5 Goals), with a probability of 80% at odds of 1.25', $faq[0]['a']);
    }
}
