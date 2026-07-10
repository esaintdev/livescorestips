<?php

/**
 * TEMPORARY PREDICTION GENERATOR
 * Purpose: Generate ONLY Over 2.5 and Under 2.5 tips.
 * Usage: php over_under_generator.php
 */

use Illuminate\Contracts\Console\Kernel;
use App\Services\PredictionService;
use App\Services\ApiFootballService;
use App\Models\Prediction;
use Carbon\Carbon;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$service = app(PredictionService::class);
$api = app(ApiFootballService::class);

// Allow passing date as argument: php over_under_generator.php 2024-03-26
$dateArg = $_SERVER['argv'][1] ?? null;
$date = $dateArg ?: date('Y-m-d');

echo "Starting Over/Under 2.5 generation for $date...\n";

// 1. Fetch Bulk Odds (1xBet)
$bulkOdds = $api->fetchOddsByDate($date, 11);
$oddsMap = [];
foreach ($bulkOdds as $entry) {
    if ($fId = ($entry['fixture']['id'] ?? null)) $oddsMap[$fId] = $entry;
}

// 2. Fetch ALL fixtures (no league filtering)
$fixtures = $api->fetchFixturesByRange($date, $date, []);

$count = 0;
$limit = 10;
foreach ($fixtures as $fixture) {
    if ($count >= $limit) break;
    
    $fId = $fixture['fixture']['id'];
    $matchName = "{$fixture['teams']['home']['name']} vs {$fixture['teams']['away']['name']}";

    if (Prediction::where('fixture_id', $fId)->exists()) continue;

    echo "Analyzing $matchName...\n";
    
    // Fetch individual odds for reliability
    $singleOdds = $api->fetchOddsByFixture($fId);
    if (empty($singleOdds)) continue;
    $oddsEntry = $singleOdds[0];

    // Use Reflection to call protected analyzeMatch()
    $reflection = new ReflectionClass($service);
    $analyzeMethod = $reflection->getMethod('analyzeMatch');
    $analyzeMethod->setAccessible(true);
    $potential = $analyzeMethod->invoke($service, $fixture);
    
    // Filter for Over/Under 2.5 ONLY
    $ouTips = array_filter($potential, function($p) {
        return in_array($p['category'], ['Over 2.5 Goals', 'Under 2.5 Goals']);
    });

    if (empty($ouTips)) continue;

    // Pick one (randomly or prioritize Over)
    $predictionData = reset($ouTips); 
    
    // Extract specific odds
    $odds = $service->extractOddsPublic($oddsEntry, $predictionData['market_type'], $predictionData['tip']);

    if ($odds) {
        // Use Reflection to call protected createPredictionRecord()
        $createMethod = $reflection->getMethod('createPredictionRecord');
        $createMethod->setAccessible(true);
        $createMethod->invoke($service, 
            $fixture, 
            $predictionData, 
            $odds, 
            $predictionData['home_form'] ?? [], 
            $predictionData['away_form'] ?? []
        );

        echo "SUCCESS: Saved {$predictionData['category']} for $matchName\n";
        $count++;
    }
}

echo "\nFinished. Generated $count Over/Under 2.5 predictions.\n";
