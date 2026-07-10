<?php
/**
 * CATEGORY GENERATOR: BTTS
 * Usage: php btts_generator.php [optional-date]
 */
use Illuminate\Contracts\Console\Kernel;
use App\Services\PredictionService;
use App\Services\ApiFootballService;
use App\Models\Prediction;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$service = app(PredictionService::class);
$api = app(ApiFootballService::class);

$dateArg = $_SERVER['argv'][1] ?? null;
$date = $dateArg ?: date('Y-m-d');
echo "Generating BTTS tips for $date...\n";

$bulkOdds = $api->fetchOddsByDate($date, 11);
// Fetch ALL fixtures (no league filtering)
$fixtures = $api->fetchFixturesByRange($date, $date, []);

$count = 0; $limit = 5;
foreach ($fixtures as $fixture) {
    if ($count >= $limit) break;
    $fId = $fixture['fixture']['id'];
    if (Prediction::where('fixture_id', $fId)->exists()) continue;

    $singleOdds = $api->fetchOddsByFixture($fId);
    if (empty($singleOdds)) continue;
    $oddsEntry = $singleOdds[0];

    $reflection = new ReflectionClass($service);
    $analyzeMethod = $reflection->getMethod('analyzeMatch');
    $analyzeMethod->setAccessible(true);
    $potential = $analyzeMethod->invoke($service, $fixture);
    
    $filtered = array_filter($potential, function($p) {
        return $p['category'] === 'Both Teams To Score';
    });

    if (empty($filtered)) continue;
    $predictionData = reset($filtered); 
    $odds = $service->extractOddsPublic($oddsEntry, $predictionData['market_type'], $predictionData['tip']);

    if ($odds) {
        $createMethod = $reflection->getMethod('createPredictionRecord');
        $createMethod->setAccessible(true);
        $createMethod->invoke($service, $fixture, $predictionData, $odds, $predictionData['home_form'] ?? [], $predictionData['away_form'] ?? []);
        echo "SUCCESS: Saved BTTS for {$fixture['teams']['home']['name']} vs {$fixture['teams']['away']['name']}\n";
        $count++;
    }
}
echo "Done. Generated $count BTTS predictions.\n";
