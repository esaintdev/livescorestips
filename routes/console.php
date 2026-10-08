<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule prediction status update every 15 minutes
Illuminate\Support\Facades\Schedule::command('prediction:update-status')->everyFifteenMinutes();

// Schedule auto-generation of predictions daily at 2 AM
// This ensures all 11 categories have 15 games each for the next 3 days
Illuminate\Support\Facades\Schedule::command('prediction:fill-all --limit=15 --days=3')->dailyAt('02:00');

// Mark banker predictions daily at 3 AM (after fill-all completes)
Illuminate\Support\Facades\Schedule::command('prediction:mark-bankers --max-bankers=5')
    ->dailyAt('03:00')
    ->withoutOverlapping(30);

// Schedule World Cup 2026 prediction generation daily at midnight
// Checks all 72 World Cup fixtures (June 11-28) daily and generates predictions
// as odds become available closer to match dates
Illuminate\Support\Facades\Schedule::command('prediction:auto-generate --count=100 --days=30 --league=1')
    ->dailyAt('00:00')
    ->withoutOverlapping(60);

// Sky Sports news import — DISABLED.
// The articles imported by SkySportsNewsService were removed from the site and
// the twice-daily import was stopped so no more Sky Sports content is published.
// The service/command remain in the codebase but are no longer scheduled.

// Prune expired database cache entries hourly
// Most cache entries expire after 1 hour, so hourly cleanup prevents bloat
Illuminate\Support\Facades\Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('cache')
        ->where('expiration', '<', time())
        ->delete();
})->hourly()->description('Prune expired cache');

