<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule prediction status update every 15 minutes
Illuminate\Support\Facades\Schedule::command('prediction:update-status')->everyFifteenMinutes();

// Fill every category (15 games each) for the next 3 days. Runs every 6 hours:
// the command skips categories that are already full, so repeat runs are cheap
// and backfill any day a previous run could not complete.
Illuminate\Support\Facades\Schedule::command('prediction:fill-all --limit=15 --days=3')
    ->cron('0 2,8,14,20 * * *')
    ->withoutOverlapping(120);

// Pre-build match page data (form, H2H, standings, stats) so visitors and
// crawlers hit a warm cache instead of 7-9 sequential API calls. Short runs
// (~2 min budget) every 30 minutes: the host kills long-running processes.
Illuminate\Support\Facades\Schedule::command('prediction:warm-pages')
    ->cron('15,45 * * * *')
    ->withoutOverlapping(10)
    ->runInBackground();

// Mark banker predictions daily at 3 AM (after fill-all completes)
Illuminate\Support\Facades\Schedule::command('prediction:mark-bankers --max-bankers=5')
    ->dailyAt('03:00')
    ->withoutOverlapping(30);

// World Cup 2026 generation (league 1) — DISABLED: the tournament ended in
// July 2026, so the nightly run only spent API calls. Re-add for the next one.

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

