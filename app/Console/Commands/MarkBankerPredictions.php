<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prediction;
use Carbon\Carbon;

class MarkBankerPredictions extends Command
{
    protected $signature = 'prediction:mark-bankers
                            {--date= : Date (Y-m-d). Defaults to today}
                            {--min-odds=1.50 : Minimum odds threshold}
                            {--max-bankers=5 : Maximum number of banker tips per day}';

    protected $description = 'Fill remaining banker slots for days with too few manually set bankers';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $minOdds = (float) $this->option('min-odds');
        $maxBankers = (int) $this->option('max-bankers');

        $dateStr = $date->format('Y-m-d');
        $this->info("Filling banker slots for {$dateStr} (min odds: {$minOdds})...");

        $manualCount = Prediction::whereDate('match_date', $dateStr)
            ->where('is_banker', true)
            ->count();

        $slotsRemaining = $maxBankers - $manualCount;

        if ($slotsRemaining <= 0) {
            $this->info("Already {$manualCount} banker predictions (max {$maxBankers}). No slots to fill.");
            return 0;
        }

        $takenFixtureIds = Prediction::whereDate('match_date', $dateStr)
            ->where('is_banker', true)
            ->whereNotNull('fixture_id')
            ->pluck('fixture_id')
            ->toArray();

        $candidates = Prediction::whereDate('match_date', $dateStr)
            ->where('is_banker', false)
            ->where('odds', '>=', $minOdds)
            ->where('category', '!=', 'Correct Score')
            ->whereNotNull('fixture_id')
            ->whereNotIn('fixture_id', $takenFixtureIds)
            ->orderBy('probability', 'desc')
            ->orderBy('odds', 'desc')
            ->get()
            ->unique('fixture_id')
            ->take($slotsRemaining);

        if ($candidates->isEmpty()) {
            $this->warn("No predictions found meeting the criteria to fill remaining {$slotsRemaining} slots.");
            return 0;
        }

        $ids = $candidates->pluck('id');
        Prediction::whereIn('id', $ids)->update(['is_banker' => true]);

        $this->info("Auto-marked {$candidates->count()} predictions as banker (had {$manualCount} manual, filled {$slotsRemaining} slots).");
        $this->table(
            ['ID', 'Match', 'League', 'Category', 'Odds', 'Probability'],
            $candidates->map(fn($p) => [
                $p->id,
                "{$p->home_team} vs {$p->away_team}",
                $p->league_name,
                $p->category,
                $p->odds,
                $p->probability . '%',
            ])
        );

        return 0;
    }
}
