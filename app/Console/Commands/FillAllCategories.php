<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FillAllCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prediction:fill-all
                            {--categories=Double Chance,Over 1.5 Goals,Over 2.5 Goals,Both Teams To Score,Home Win,Away Win,Correct Score,Under 2.5 Goals : Comma-separated category names to fill}
                            {--date= : Date to generate for (Y-m-d). Defaults to today}
                            {--days=1 : Number of consecutive days to generate for}
                            {--limit=15 : Number of games per category}
                            {--force : Overwrite existing automatically generated predictions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure every category has at least N games for a specific date.';

    protected $predictionService;

    protected $apiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PredictionService $predictionService, ApiFootballService $apiService)
    {
        parent::__construct();
        $this->predictionService = $predictionService;
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateInput = $this->option('date');
        $days = (int) ($this->option('days') ?? 1);
        $limitPerCategory = (int) $this->option('limit');

        $startDate = $dateInput ? Carbon::parse($dateInput) : Carbon::today();

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            $this->info("\n--- Filling all categories for $currentDate (Target: $limitPerCategory games per category) ---");

            try {
                $categories = explode(',', $this->option('categories'));

                // 1. Fetch Fixtures and Odds ONCE
                $this->info('Fetching eligible fixtures and bulk odds...');
                $fixtures = $this->fetchEligibleFixtures($currentDate);

                if (empty($fixtures)) {
                    $this->error("No eligible fixtures found for $currentDate.");

                    continue;
                }

                // Move any existing records for these fixtures onto this date instead
                // of creating duplicate date-variant URLs when a fixture is rescheduled.
                $this->syncRescheduledFixtures($fixtures, $currentDate);

                $bulkOdds = $this->apiService->fetchOddsByDate($currentDate, 11);
                $oddsMap = [];
                foreach ($bulkOdds as $entry) {
                    $fId = $entry['fixture']['id'] ?? null;
                    if ($fId) {
                        $oddsMap[$fId] = $entry;
                    }
                }

                // Indexable (searched-for) leagues first, so big matches get a
                // prediction before slots go to minor leagues; shuffled within
                // each group so no single league dominates.
                $priority = array_values(array_filter($fixtures, fn ($f) => league_is_indexable($f['league']['id'] ?? null)));
                $others = array_values(array_filter($fixtures, fn ($f) => ! league_is_indexable($f['league']['id'] ?? null)));
                shuffle($priority);
                shuffle($others);
                $fixtures = array_merge($priority, $others);
                $this->info(count($priority).' fixtures from indexable leagues, '.count($others).' others.');

                $this->info('Processing '.count($fixtures).' fixtures across '.count($categories).' categories.');

                foreach ($categories as $category) {
                    $this->fillCategory($currentDate, trim($category), $limitPerCategory, $fixtures, $oddsMap);
                }
            } catch (\Throwable $e) {
                // Keep going so one bad date never leaves the following dates empty
                $this->error("Failed filling $currentDate: ".$e->getMessage());
                report($e);
            }
        }

        $this->info("\nAll requested dates processed.");

        return 0;
    }

    protected function fillCategory($date, $category, $limit, $fixtures, $oddsMap)
    {
        // Count existing for this category and date
        $existingCount = Prediction::whereDate('match_date', $date)
            ->where('category', $category)
            ->count();

        if ($existingCount >= $limit) {
            $this->line("  [$category] Already has $existingCount games. Skipping.");

            return;
        }

        $needed = $limit - $existingCount;
        $this->info("  [$category] $existingCount existing. Need $needed more...");

        $created = 0;
        foreach ($fixtures as $fixture) {
            if ($created >= $needed) {
                break;
            }

            $fixtureId = $fixture['fixture']['id'];
            $matchName = "{$fixture['teams']['home']['name']} vs {$fixture['teams']['away']['name']}";

            // Check if THIS SPECIFIC CATEGORY already exists for this fixture
            if (Prediction::where('fixture_id', $fixtureId)->where('category', $category)->exists()) {
                continue;
            }

            // Cap near-duplicate pages: at most N prediction records per fixture
            // on a single date, regardless of market. Keeps the URL space from
            // growing faster than search engines can crawl it.
            $maxPerFixture = (int) config('predictions.max_per_fixture_per_date', 3);
            if ($maxPerFixture > 0 && Prediction::where('fixture_id', $fixtureId)
                ->whereDate('match_date', $date)
                ->count() >= $maxPerFixture) {
                continue;
            }

            // One failing fixture (API error, bad data) must not abort the
            // whole run and leave later dates empty.
            try {
                // Get odds — bulk first, individual fallback for EVERY league
                $bulkEntry = $oddsMap[$fixtureId] ?? null;
                if (! $bulkEntry) {
                    $singleOdds = $this->apiService->fetchOddsByFixture($fixtureId);
                    $bulkEntry = $singleOdds[0] ?? null;
                }

                if (! $bulkEntry) {
                    continue;
                }

                // Analyze match to see if it fits the category
                $potentialPredictions = $this->predictionService->analyzeMatch($fixture);

                // Find the prediction for the current category
                $predictionData = null;
                foreach ($potentialPredictions as $pp) {
                    if ($pp['category'] === $category) {
                        $predictionData = $pp;
                        break;
                    }
                }

                if ($predictionData) {
                    // Extract odds
                    $odds = $this->predictionService->extractOddsPublic($bulkEntry, $predictionData['market_type'], $predictionData['tip']);

                    if ($odds && $odds['value'] >= 1.05) {
                        $this->predictionService->createPredictionRecord(
                            $fixture,
                            $predictionData,
                            $odds,
                            $predictionData['home_form'] ?? [],
                            $predictionData['away_form'] ?? []
                        );
                        $created++;
                        $this->line("    ✓ Created [$category] $matchName (".($existingCount + $created)."/$limit)");
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("    ! Skipped $matchName: ".$e->getMessage());
                report($e);
            }
        }

        if ($created < $needed) {
            $this->warn("  [$category] Could only find $created more games. Total: ".($existingCount + $created));
        }
    }

    /**
     * A provider fixture_id is the stable identity for a match. When a fixture
     * is rescheduled between generation runs, move its existing prediction
     * records onto the new date instead of creating a second dated URL for the
     * same fixture. (Existing settled records are never touched — only fixtures
     * currently eligible for prediction generation are passed in.)
     */
    protected function syncRescheduledFixtures(array $fixtures, string $date): void
    {
        foreach ($fixtures as $fixture) {
            $fixtureId = $fixture['fixture']['id'] ?? null;
            if (! $fixtureId) {
                continue;
            }

            $stale = Prediction::where('fixture_id', $fixtureId)
                ->whereDate('match_date', '!=', $date)
                ->get();

            if ($stale->isEmpty()) {
                continue;
            }

            $time = date('H:i', strtotime($fixture['fixture']['date']));

            foreach ($stale as $prediction) {
                $prediction->update([
                    'match_date' => $date,
                    'match_time' => $time,
                    'content_updated_at' => now(),
                ]);
            }

            $this->line("    ↻ Rescheduled fixture {$fixtureId}: moved ".$stale->count()." record(s) to $date");
        }
    }

    protected function fetchEligibleFixtures($date)
    {
        $fixtures = $this->apiService->fetchFixtures($date);

        return array_filter($fixtures, function ($fixture) {
            $statusShort = $fixture['fixture']['status']['short'];

            // 1. Must be Not Started or TBD
            if (! in_array($statusShort, ['NS', 'TBD'])) {
                return false;
            }

            // 2. Exclude women's, youth, and reserve games
            if ($this->isFixtureExcluded($fixture)) {
                return false;
            }

            return true;
        });
    }

    protected function isFixtureExcluded($fixture): bool
    {
        $leagueName = $fixture['league']['name'] ?? '';
        $homeTeam = $fixture['teams']['home']['name'] ?? '';
        $awayTeam = $fixture['teams']['away']['name'] ?? '';

        // Check league name patterns
        if (preg_match('/\bU(19|20|21|22|23)\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bReserve\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bWomen\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bWomens\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bFeminin\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bFemminile\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bFemenino\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bLadies\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bJunior\b/i', $leagueName)) {
            return true;
        }
        if (preg_match('/\bYouth\b/i', $leagueName)) {
            return true;
        }

        // Check team names
        foreach ([$homeTeam, $awayTeam] as $team) {
            if (preg_match('/\bU(19|20|21|22|23)\b/i', $team)) {
                return true;
            }
            if (preg_match('/\bWomen\b/i', $team)) {
                return true;
            }
            if (preg_match('/\bWFC\b/i', $team)) {
                return true;
            }
            if (preg_match('/\bLadies\b/i', $team)) {
                return true;
            }
            if (preg_match('/\bRes(\.|erve)\b/i', $team)) {
                return true;
            }
            if (preg_match('/\sII$/', $team)) {
                return true;
            }
            if (preg_match('/[- ]B$/', $team)) {
                return true;
            }
        }

        return false;
    }
}
