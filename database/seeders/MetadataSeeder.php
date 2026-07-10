<?php

namespace Database\Seeders;

use App\Models\Metadata;
use Illuminate\Database\Seeder;

class MetadataSeeder extends Seeder
{
    public function run(): void
    {
        Metadata::updateOrCreate(
            ['route_name' => 'home', 'key' => 'header_intro'],
            ['value' => 'Welcome to LiveScores Tips — your daily source for well-researched football predictions, match analysis, and betting tips across major leagues worldwide.']
        );

        Metadata::updateOrCreate(
            ['route_name' => 'banker-predictions', 'key' => 'page_title'],
            ['value' => 'Banker Predictions Today - High Confidence Football Tips | LiveScores Tips']
        );

        Metadata::updateOrCreate(
            ['route_name' => 'banker-predictions', 'key' => 'page_subheader'],
            ['value' => 'Banker Predictions Today']
        );

        Metadata::updateOrCreate(
            ['route_name' => 'banker-predictions', 'key' => 'page_description'],
            ['value' => 'Our team\'s highest-confidence football picks for today. Banker predictions backed by strong statistical analysis, team form, and expert research.']
        );

        Metadata::updateOrCreate(
            ['route_name' => 'banker-predictions', 'key' => 'header_intro'],
            ['value' => 'Banker predictions are our most confident football picks of the day. Each selection is carefully analyzed based on team form, head-to-head records, goal trends, and statistical probability to identify the safest betting opportunities.']
        );
    }
}
