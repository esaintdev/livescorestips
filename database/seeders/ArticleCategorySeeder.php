<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ArticleCategory;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Predictions',
                'slug' => 'predictions',
                'description' => 'Football match predictions and betting tips',
            ],
            [
                'name' => 'Guides',
                'slug' => 'guides',
                'description' => 'Betting guides and tutorials',
            ],
            [
                'name' => 'News',
                'slug' => 'news',
                'description' => 'Latest football news and updates',
            ],
            [
                'name' => 'Tips & Strategies',
                'slug' => 'tips-strategies',
                'description' => 'Betting tips and winning strategies',
            ],
            [
                'name' => 'Analysis',
                'slug' => 'analysis',
                'description' => 'In-depth match and team analysis',
            ],
        ];

        foreach ($categories as $category) {
            ArticleCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
