<?php

/**
 * COMPREHENSIVE PREDICTION GENERATOR
 * Purpose: Generate at least 10 predictions across ALL categories.
 * Usage: php all_categories_generator.php
 */

use Illuminate\Contracts\Console\Kernel;
use App\Services\PredictionService;
use Carbon\Carbon;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$service = app(PredictionService::class);

// Allow passing date as argument: php all_categories_generator.php 2024-03-26
$dateArg = $_SERVER['argv'][1] ?? null;
$date = $dateArg ?: date('Y-m-d');
$targetCount = 10;

echo "Starting Comprehensive generation for $date (Target: at least $targetCount matches)...\n";

try {
    // Generate 10 predictions
    $results = $service->generateAutoPredictions($date, 10);
    $predictions = $results['predictions'] ?? [];
    
    $count = count($predictions);
    echo "SUCCESS: Generated $count matches for $date.\n\n";

    if ($count > 0) {
        echo str_pad("Match", 40) . " | " . str_pad("Category", 20) . " | Tip | Odds\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($predictions as $p) {
            echo str_pad("{$p->home_team} vs {$p->away_team}", 40) . " | " . 
                 str_pad($p->category, 20) . " | " . 
                 str_pad($p->tip, 5) . " | " . 
                 $p->odds . "\n";
        }
    } else {
        echo "WARNING: No new predictions were generated (they might already exist in your database).\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
