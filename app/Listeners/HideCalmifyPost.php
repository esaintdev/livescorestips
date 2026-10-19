<?php

namespace App\Listeners;

use App\Models\Article;
use Calmify\Laravel\Events\PostUnpublished;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HideCalmifyPost
{
    /**
     * Handle the event.
     */
    public function handle(PostUnpublished $event): void
    {
        try {
            $slug = $event->post->slug ?? null;
            if (! $slug) {
                return;
            }

            $count = Article::where('slug', $slug)->update(['published_at' => null]);

            // Flush guest page cache so it hides or 404s immediately
            Cache::forget('pagecache:' . sha1(url('/blog/' . $slug)));
            Cache::forget('pagecache:' . sha1(url('/blog')));
            Cache::forget('pagecache:' . sha1(url('/')));

            Log::info("Calmify post '{$slug}' unpublished ({$count} article(s) hidden)");
        } catch (\Throwable $e) {
            Log::error("Failed to unpublish Calmify post: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
