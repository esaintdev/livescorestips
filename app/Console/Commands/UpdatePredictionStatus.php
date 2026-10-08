<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdatePredictionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prediction:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status for pending predictions of past matches';

    /**
     * Execute the console command.
     */
    public function handle(PredictionService $predictionService)
    {
        $this->info('Starting prediction status check...');

        // Fetch pending predictions where match time has passed
        // Adding a buffer (e.g., 2 hours) to ensure match is likely finished
        $predictions = Prediction::where('status', 'pending')
            ->where('match_date', '<=', Carbon::now()->subHours(2))
            ->get();

        if ($predictions->isEmpty()) {
            $this->info('No pending past predictions found.');

            return 0;
        }

        $this->info("Found {$predictions->count()} pending predictions. Processing...");

        $updated = 0;
        $failed = 0;

        foreach ($predictions as $prediction) {
            try {
                $this->line("Checking fixture ID: {$prediction->fixture_id}...");
                $status = $predictionService->checkStatus($prediction);

                if ($status !== 'pending') {
                    $this->info("-> Updated to: {$status}");
                    $updated++;
                } else {
                    $this->warn('-> Still pending (match might not be finished or API delay)');
                }

                // Avoid API rate limits
                usleep(200000); // 0.2s pause
            } catch (\Exception $e) {
                $this->error('-> Error: '.$e->getMessage());
                $failed++;
            }
        }

        $this->info("Completed. Updated: {$updated}, Failed: {$failed}");

        // Re-verify recently finished matches. Knockout games can be settled at
        // 90 minutes before extra time / penalties are played, and providers
        // occasionally correct scores — re-checking a short window keeps stored
        // results accurate. checkStatus only writes when something changed.
        $settled = Prediction::whereIn('status', ['won', 'lost'])
            ->where('match_date', '<=', Carbon::now()->subHours(2))
            ->where('match_date', '>=', Carbon::now()->subHours(8))
            ->get();

        if ($settled->isNotEmpty()) {
            $this->info("Re-verifying {$settled->count()} recently settled predictions...");
            $corrected = 0;

            foreach ($settled as $prediction) {
                try {
                    $before = [$prediction->status, $prediction->home_score, $prediction->away_score];
                    $predictionService->checkStatus($prediction);
                    $prediction->refresh();

                    if ($before !== [$prediction->status, $prediction->home_score, $prediction->away_score]) {
                        $corrected++;
                        $this->warn("-> Corrected fixture {$prediction->fixture_id}: {$before[0]} {$before[1]}-{$before[2]} => {$prediction->status} {$prediction->home_score}-{$prediction->away_score}");
                    }

                    usleep(200000);
                } catch (\Exception $e) {
                    $this->error("-> Error re-checking {$prediction->fixture_id}: ".$e->getMessage());
                }
            }

            $this->info("Re-verification complete. Corrected: {$corrected}");
        }

        return 0;
    }
}
