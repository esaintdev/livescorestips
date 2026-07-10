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
            ['value' => 'Welcome to LiveScoresTips — your daily source for well-researched football predictions, match analysis, and betting tips across major leagues worldwide.']
        );
    }
}
