<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Metadata;

// Unique H1 (page_subheader) + meta description per route.
// No two routes should share the same description; titles remain unique too.
$config = [
    'home' => [
        'page_title'       => 'LiveScores Tips | Live Football Tips Today & Fixtures',
        'page_subheader'   => 'Free Football Predictions & Betting Tips Today',
        'page_description' => 'Get today\'s football predictions and betting tips covering fixtures, odds, match analysis and markets like Over 2.5 goals, BTTS and correct score from LiveScores Tips.',
    ],
    'predictions.all' => [
        'page_title'       => 'All Football Predictions Today | Best Betting Tips',
        'page_subheader'   => 'All Football Predictions & Betting Tips',
        'page_description' => 'Browse every football prediction for today in one place, including home wins, draws, over 2.5 goals, BTTS and correct score tips with odds and probabilities.',
    ],
    'blog.index' => [
        'page_title'       => 'Football Betting Blog | Tips, News & Analysis | LiveScores Tips',
        'page_subheader'   => 'Football Betting Blog & Match Insights',
        'page_description' => 'Read the LiveScores Tips blog for football betting guides, news, match analysis and predictions to help you bet smarter on today\'s football.',
    ],
    'recent-winnings.index' => [
        'page_title'       => 'Recent Winnings & Prediction Results | LiveScores Tips',
        'page_subheader'   => 'Recent Winnings & Prediction Results',
        'page_description' => 'Review LiveScores Tips recent winning tips, successful predictions and betting results with odds to track our performance across football markets.',
    ],
    'banker-predictions' => [
        'page_title'       => 'Banker Predictions Today - High Confidence Football Tips | LiveScores Tips',
        'page_subheader'   => 'Banker Predictions Today',
        'page_description' => 'Today\'s safest banker predictions selected from strong football betting angles, with high-confidence tips, match analysis and odds from LiveScores Tips.',
    ],
    'category:over-2-5-goals' => [
        'page_title'       => 'Over 2.5 Goals Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Over 2.5 Goals Predictions Today',
        'page_description' => 'Today\'s Over 2.5 goals football predictions with team stats, match analysis and betting odds to find games likely to produce 3 or more goals.',
    ],
    'category:over-1-5-goals' => [
        'page_title'       => 'Over 1.5 Goals Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Over 1.5 Goals Predictions Today',
        'page_description' => 'Today\'s Over 1.5 goals tips backed by scoring stats, head-to-head records and odds, highlighting matches expected to feature two or more goals.',
    ],
    'category:btts-gg' => [
        'page_title'       => 'BTTS Predictions Today (Both Teams To Score) | LiveScores Tips',
        'page_subheader'   => 'BTTS / Both Teams To Score Predictions Today',
        'page_description' => 'Today\'s Both Teams To Score (BTTS) tips with attacking and defensive analysis to spot matches where both sides should score.',
    ],
    'category:double-chance' => [
        'page_title'       => 'Double Chance Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Double Chance Predictions Today',
        'page_description' => 'Today\'s Double Chance football predictions covering two outcomes per match to reduce betting risk, with analysis and odds.',
    ],
    'category:draws' => [
        'page_title'       => 'Draw Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Draw Predictions Today',
        'page_description' => 'Today\'s draw football predictions based on team form, head-to-head history and competitive balance, with odds and match analysis.',
    ],
    'category:home-wins' => [
        'page_title'       => 'Home Win Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Home Win Predictions Today',
        'page_description' => 'Today\'s home win football predictions with home advantage, form and odds analysis to identify strong host sides.',
    ],
    'category:away-wins' => [
        'page_title'       => 'Away Win Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Away Win Predictions Today',
        'page_description' => 'Today\'s away win football predictions with defensive and travel form analysis to spot strong away sides, plus betting odds.',
    ],
    'category:correct-score' => [
        'page_title'       => 'Correct Score Predictions Today | LiveScores Tips',
        'page_subheader'   => 'Correct Score Predictions Today',
        'page_description' => 'Today\'s correct score predictions combining goal expectations and match stats to forecast exact final scorelines, with odds.',
    ],
];

$updated = 0;
foreach ($config as $routeName => $fields) {
    foreach ($fields as $key => $value) {
        $row = Metadata::where('route_name', $routeName)->where('key', $key)->first();
        if (!$row) {
            $row = new Metadata();
            $row->route_name = $routeName;
            $row->key = $key;
            $row->value = $value;
            $row->save();
            $updated++;
        } elseif ($row->value !== $value) {
            $row->value = $value;
            $row->save();
            $updated++;
        }
    }
}

echo "Updated $updated metadata fields.\n";