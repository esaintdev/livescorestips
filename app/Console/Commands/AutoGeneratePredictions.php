<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PredictionService;
use Carbon\Carbon;

class AutoGeneratePredictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prediction:auto-generate 
                            {--count=5 : Number of predictions per day} 
                            {--date= : Start date (Y-m-d). Defaults to today} 
                            {--days=1 : Number of consecutive days to generate for}
                            {--retry=1 : Number of times to retry each day (to catch delayed odds)}
                            {--league= : Filter by specific league ID (comma-separated for multiple)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and generate data-driven match predictions for a range of days';

    protected $predictionService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PredictionService $predictionService)
    {
        parent::__construct();
        $this->predictionService = $predictionService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $dateInput = $this->option('date');
        $days = (int) $this->option('days');
        $retries = (int) $this->option('retry');
        $leagueOption = $this->option('league');

        // Parse league IDs
        $leagueIds = [];
        if ($leagueOption) {
            $leagueIds = array_map('intval', array_map('trim', explode(',', $leagueOption)));
            $this->info("Filtering by league IDs: " . implode(', ', $leagueIds));
        }

        $startDate = $dateInput ? Carbon::parse($dateInput) : Carbon::today();

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            $this->info("\n--- Day " . ($i + 1) . " of $days: Generating for $currentDate ---");
            
            for ($attempt = 1; $attempt <= $retries; $attempt++) {
                if ($retries > 1) {
                    $this->info("Attempt $attempt of $retries...");
                }
                
                $this->generateForDate($currentDate, $count, $leagueIds);

                if ($attempt < $retries) {
                    $this->info("Waiting 10 seconds before next attempt...");
                    sleep(10);
                }
            }
        }

        $this->info("\nBatch process completed successfully.");
        return 0;
    }

    protected function generateForDate($date, $count, $leagueIds = [])
    {
        $leagueSuffix = !empty($leagueIds) ? ' (league: ' . implode(',', $leagueIds) . ')' : '';
        $this->info("Starting auto-generation of up to {$count} predictions for {$date}{$leagueSuffix}...");

        try {
            $results = $this->predictionService->generateAutoPredictions($date, $count, $leagueIds);
            $predictions = $results['predictions'] ?? [];
            $summary = $results['summary'] ?? [];

            if (empty($predictions)) {
                $this->warn("No predictions were generated for $date.");
                if (!empty($summary)) {
                    $this->warn("Summary: Skipped (Duplicates: {$summary['duplicate']}, No Analysis: {$summary['no_analysis']}, No Odds: {$summary['no_odds']})");
                }
                return;
            }

            $this->table(
                ['Date', 'Match', 'League', 'Tip', 'Odds'],
                collect($predictions)->map(function ($p) {
                    return [
                        $p->match_date,
                        "{$p->home_team} vs {$p->away_team}",
                        $p->league_name,
                        $p->tip,
                        $p->odds
                    ];
                })
            );

            $this->info("Successfully generated " . count($predictions) . " predictions for $date.");
            if (!empty($summary)) {
                $this->info("Total fixtures screened: {$summary['total_checked']}");
                $this->info("Skip Summary: Duplicates: {$summary['duplicate']}, No Analysis: {$summary['no_analysis']}, No Odds: {$summary['no_odds']}");
            }
        } catch (\Exception $e) {
            $this->error("An error occurred for $date: " . $e->getMessage());
        }
    }
}
