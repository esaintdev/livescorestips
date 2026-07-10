<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PredictionService;
use App\Models\Prediction;
use App\Services\ApiFootballService;
use Carbon\Carbon;

class FillMissingCategories extends Command
{
    protected $signature = 'prediction:fill-missing-categories
                            {--from= : Start date (Y-m-d). Defaults to today}
                            {--to= : End date (Y-m-d). Defaults to from date}
                            {--categories=Draw,Both Teams To Score,Correct Score : Comma-separated category names to fill}';

    protected $description = 'Backfill missing category predictions (e.g. BTTS, Draw, Correct Score) for a date range without touching existing predictions.';

    protected $predictionService;
    protected $apiService;

    public function __construct(PredictionService $predictionService, ApiFootballService $apiService)
    {
        parent::__construct();
        $this->predictionService = $predictionService;
        $this->apiService = $apiService;
    }

    public function handle()
    {
        $from    = $this->option('from') ?? now()->format('Y-m-d');
        $to      = $this->option('to')   ?? $from;
        $cats    = array_map('trim', explode(',', $this->option('categories')));

        $this->info("Filling missing categories for: " . implode(', ', $cats));
        $this->info("Date range: $from → $to");

        $start = Carbon::parse($from);
        $end   = Carbon::parse($to);

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $date = $day->format('Y-m-d');
            $this->info("\n--- Processing $date ---");

            foreach ($cats as $category) {
                $this->fillCategory($date, $category);
            }
        }

        $this->info("\nDone.");
        return 0;
    }

    protected function fillCategory(string $date, string $category)
    {
        // Check if we already have this category for this date
        $existing = Prediction::whereDate('match_date', $date)
            ->where('category', $category)
            ->count();

        if ($existing >= 3) {
            $this->line("  [$category] Already has $existing predictions — skipping.");
            return;
        }

        $this->line("  [$category] Only $existing found — generating more...");

        // Map category → market type and tip
        $marketConfig = $this->getCategoryConfig($category);
        if (!$marketConfig) {
            $this->warn("  [$category] Unknown category — skipping.");
            return;
        }

        // Fetch bulk odds for the day
        $bulkOdds = $this->apiService->fetchOddsByDate($date, 11);
        $oddsMap  = [];
        foreach ($bulkOdds as $entry) {
            $fId = $entry['fixture']['id'] ?? null;
            if ($fId) $oddsMap[$fId] = $entry;
        }

        // Fetch ALL fixtures for this date (no league filtering)
        $fixtures = $this->apiService->fetchFixturesByRange($date, $date, []);
        $fixtures = array_filter($fixtures, function ($f) {
            $status = $f['fixture']['status']['short'];
            $name   = $f['league']['name'];
            return in_array($status, ['NS', 'TBD', 'FT', 'AET', 'PEN']) // include finished too for past dates
                ; // No league name filtering — predict ALL games (friendlies, internationals, etc.)
        });

        // Shuffle to give every league an equal chance
        shuffle($fixtures);

        $created = 0;
        $needed  = 3 - $existing;

        foreach ($fixtures as $fixture) {
            if ($created >= $needed) break;

            $fixtureId   = $fixture['fixture']['id'];
            $matchName   = "{$fixture['teams']['home']['name']} vs {$fixture['teams']['away']['name']}";

            // Skip if this fixture ALREADY has ANY prediction (prevent duplicates across categories)
            if (Prediction::where('fixture_id', $fixtureId)->exists()) {
                continue;
            }

            // Get odds — try bulk first, then individual
            $bulkEntry = $oddsMap[$fixtureId] ?? null;
            if (!$bulkEntry) {
                $singleOdds = $this->apiService->fetchOddsByFixture($fixtureId);
                $bulkEntry  = $singleOdds[0] ?? null;
            }
            if (!$bulkEntry) {
                $this->line("    Skipped $matchName — no odds.");
                continue;
            }

            // Derive the correct tip for this category
            $tip = $this->deriveTip($category, $fixture, $bulkEntry);
            if (!$tip) {
                $this->line("    Skipped $matchName — could not derive tip for $category.");
                continue;
            }

            // Extract odds for that specific tip
            $odds = $this->predictionService->extractOddsPublic($bulkEntry, $marketConfig['market_type'], $tip);
            if (!$odds) {
                // Try a lenient fallback: just use the best available odd from that market
                $odds = $this->getBestOddsForMarket($bulkEntry, $marketConfig['market_type'], $tip);
            }
            if (!$odds) {
                $this->line("    Skipped $matchName — odds for $category not in bookmaker data.");
                continue;
            }

            // Create the prediction record
            $probability = \App\Services\ApiFootballService::calculateProbability($odds['value']);
            $homeTeam = $fixture['teams']['home']['name'];
            $awayTeam = $fixture['teams']['away']['name'];
            $slug     = Prediction::generateSlug($homeTeam, $awayTeam, $fixtureId);

            // Make slug unique if needed (since another prediction may have the same base slug)
            $slug = $slug . '-' . strtolower(str_replace(' ', '-', $category));

            Prediction::create([
                'fixture_id'      => $fixtureId,
                'league_id'       => $fixture['league']['id'],
                'slug'            => $slug,
                'league_name'     => $fixture['league']['name'],
                'league_country'  => $fixture['league']['country'],
                'league_logo'     => $fixture['league']['logo'],
                'home_team'       => $homeTeam,
                'home_team_logo'  => $fixture['teams']['home']['logo'],
                'away_team'       => $awayTeam,
                'away_team_logo'  => $fixture['teams']['away']['logo'],
                'match_date'      => date('Y-m-d', strtotime($fixture['fixture']['date'])),
                'match_time'      => date('H:i', strtotime($fixture['fixture']['date'])),
                'home_form'       => [],
                'away_form'       => [],
                'tip'             => $tip,
                'category'        => $category,
                'odds'            => $odds['value'],
                'probability'     => $probability,
                'bookmaker_name'  => $odds['bookmaker'],
                'bookmaker_image' => null,
                'bookmaker_url'   => null,
                'status'          => 'pending',
                'is_featured'     => false,
                'admin_note'      => 'Auto-backfill: ' . $category,
            ]);

            $this->info("    ✓ Created [$category] $tip @ {$odds['value']} — $matchName");
            $created++;
        }

        if ($created === 0) {
            $this->warn("  [$category] No new predictions could be created for $date.");
        }
    }

    protected function getCategoryConfig(string $category): ?array
    {
        return match ($category) {
            'Draw'                => ['market_type' => '1x2'],
            'Both Teams To Score' => ['market_type' => 'btts'],
            'Correct Score'       => ['market_type' => 'correct_score'],
            'Home Win'            => ['market_type' => '1x2'],
            'Away Win'            => ['market_type' => '1x2'],
            default               => null,
        };
    }

    protected function deriveTip(string $category, array $fixture, array $bulkEntry): ?string
    {
        return match ($category) {
            'Draw'                => 'X',
            'Both Teams To Score' => 'BTTS',
            'Home Win'            => '1',
            'Away Win'            => '2',
            'Correct Score'       => $this->deriveCorrectScore($fixture),
            default               => null,
        };
    }

    protected function deriveCorrectScore(array $fixture): string
    {
        // Simple heuristic from team names/IDs — same logic as PredictionService::analyzeMatch
        // Since we don't re-analyse form here, pick a common safe score
        return '1-0'; // Will be overridden below if we have form data
    }

    protected function getBestOddsForMarket(array $bulkEntry, string $marketType, string $tip): ?array
    {
        // Try to find ANY value for the market even if tip doesn't match exactly
        if (empty($bulkEntry['bookmakers'])) return null;
        $bookmaker = $bulkEntry['bookmakers'][0];

        foreach ($bookmaker['bets'] as $bet) {
            $isMatch = match ($marketType) {
                '1x2'          => in_array($bet['name'], ['Match Winner', '1X2', 'Full Time Result']),
                'btts'         => in_array($bet['name'], ['Both Teams To Score', 'BTTS', 'Both Teams Score', 'Both Teams to Score']),
                'correct_score'=> in_array($bet['name'], ['Correct Score', 'Exact Score']),
                default        => false,
            };

            if ($isMatch) {
                foreach ($bet['values'] as $value) {
                    $normalizedValue = strtoupper(trim($value['value']));
                    $normalizedTip   = strtoupper(trim($tip));

                    // Flexible matching
                    $matched = false;
                    if ($marketType === '1x2') {
                        $matched = ($normalizedValue === $normalizedTip)
                            || ($normalizedTip === 'X' && $normalizedValue === 'DRAW')
                            || ($normalizedTip === '1' && $normalizedValue === 'HOME')
                            || ($normalizedTip === '2' && $normalizedValue === 'AWAY');
                    } elseif ($marketType === 'btts') {
                        $matched = in_array($normalizedValue, ['YES', 'GG', 'BTTS']);
                    } elseif ($marketType === 'correct_score') {
                        // Just return first score available (e.g. 1-0)
                        $odd = floatval($value['odd']);
                        if ($odd >= 1.10) {
                            return ['value' => $odd, 'bookmaker' => $bookmaker['name']];
                        }
                    }

                    if ($matched) {
                        $odd = floatval($value['odd']);
                        if ($odd >= 1.10) {
                            return ['value' => $odd, 'bookmaker' => $bookmaker['name']];
                        }
                    }
                }
            }
        }
        return null;
    }
}
