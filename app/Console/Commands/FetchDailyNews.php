<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SkySportsNewsService;

class FetchDailyNews extends Command
{
    protected $signature = 'news:fetch {--limit=2 : Number of articles to fetch per run}';
    protected $description = 'Fetch latest football news from Sky Sports and store as articles';

    public function handle(SkySportsNewsService $newsService)
    {
        $this->info('Fetching latest football news...');

        $limit = (int) $this->option('limit');
        $stored = $newsService->fetchAndStore($limit);

        if ($stored > 0) {
            $this->info("Stored {$stored} new article(s).");
        } else {
            $this->warn('No new articles were stored.');
        }

        return Command::SUCCESS;
    }
}
