<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prediction;

class GeneratePredictionSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'predictions:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing predictions that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating slugs for predictions...');

        $predictions = Prediction::whereNull('slug')->orWhere('slug', '')->get();

        if ($predictions->isEmpty()) {
            $this->info('No predictions need slug generation.');
            return 0;
        }

        $count = 0;
        foreach ($predictions as $prediction) {
            $slug = Prediction::generateSlug(
                $prediction->home_team,
                $prediction->away_team,
                $prediction->fixture_id
            );

            $prediction->update(['slug' => $slug]);
            $count++;

            $this->line("Generated slug for: {$prediction->home_team} vs {$prediction->away_team} -> {$slug}");
        }

        $this->info("Successfully generated {$count} slugs!");

        return 0;
    }
}
