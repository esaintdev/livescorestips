<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Metadata;

class FlushMetadata extends Command
{
    protected $signature = 'metadata:flush';
    protected $description = 'Flush all SEO metadata from the database';

    public function handle()
    {
        if ($this->confirm('Are you sure you want to delete ALL SEO metadata? This cannot be undone.')) {
            Metadata::truncate();
            $this->info('All metadata has been flushed.');
        }
    }
}
