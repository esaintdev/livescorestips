<?php

namespace App\Listeners;

use App\Models\Article;
use App\Models\ArticleCategory;
use Calmify\Laravel\Events\PostReceived;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CopyCalmifyPost
{
    /**
     * Handle the event.
     */
    public function handle(PostReceived $event): void
    {
        $p = $event->post;

        try {
            $categoryId = null;
            if (! empty($p->category)) {
                $category = ArticleCategory::firstOrCreate(
                    ['slug' => Str::slug($p->category)],
                    ['name' => trim($p->category)]
                );
                $categoryId = $category->id;
            }

            $article = Article::updateOrCreate(
                ['slug' => $p->slug],
                [
                    'category_id' => $categoryId,
                    'title' => $p->title,
                    'content' => $p->content_html,
                    'image' => $p->image_path,
                    'meta_description' => $p->meta_description ?: null,
                    'meta_keywords' => $p->meta_keywords ?: null,
                    'published_at' => ! empty($p->published_at) ? Carbon::parse($p->published_at) : now(),
                ]
            );

            $action = ! empty($event->updated) ? 'updated' : 'synced';
            Log::info("Calmify post '{$p->slug}' successfully {$action} to Article #{$article->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to sync Calmify post '{$p->slug}': " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
