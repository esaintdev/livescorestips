<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KeywordArticleService;

class GenerateKeywordArticles extends Command
{
    protected $signature = 'articles:generate-keywords';
    protected $description = 'Generate/refresh keyword-targeted blog articles from live prediction data';

    public function handle(KeywordArticleService $service)
    {
        $this->warn('Keyword article generation is disabled.');

        return Command::SUCCESS;
    }
}
