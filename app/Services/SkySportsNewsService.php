<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SkySportsNewsService
{
    protected string $baseUrl = 'https://api.parse.bot/scraper/d0901a5d-d4d7-47dc-a554-de7c70618bff';

    protected function apiKey(): ?string
    {
        return config('services.parse_bot.key');
    }

    protected function fetch(string $endpoint, array $params = []): ?array
    {
        $key = $this->apiKey();
        if (!$key) {
            Log::warning('SkySportsNewsService: PARSE_BOT_API_KEY not set');
            return null;
        }

        $response = Http::timeout(30)
            ->withHeader('X-API-Key', $key)
            ->get("{$this->baseUrl}/{$endpoint}", $params);

        if (!$response->successful()) {
            Log::warning("SkySportsNewsService: {$endpoint} failed", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json()['data'] ?? null;
    }

    public function getLatestArticles(int $page = 1): array
    {
        $data = $this->fetch('get_football_news', ['page' => $page]);
        return $data['articles'] ?? [];
    }

    public function getArticleDetail(string $url): ?array
    {
        return $this->fetch('get_article_detail', ['url' => $url]);
    }

    public function fetchAndStore(int $limit = 2): int
    {
        $articles = $this->getLatestArticles();
        if (empty($articles)) {
            Log::info('SkySportsNewsService: no articles returned from API');
            return 0;
        }

        $stored = 0;

        foreach ($articles as $item) {
            if ($stored >= $limit) break;

            $headline = $item['headline'] ?? null;
            $articleUrl = $item['url'] ?? null;
            $summary = $item['summary'] ?? '';
            $imageUrl = $item['image_url'] ?? null;

            if (!$headline || !$articleUrl) continue;

            // Live blogs and video pages have no meaningful article body;
            // skip them before hitting the detail API to avoid thin content.
            if (str_contains($articleUrl, '/live-blog/') || str_contains($articleUrl, '/video/')) {
                Log::info('SkySportsNewsService: skipping non-article URL', ['url' => $articleUrl]);
                continue;
            }

            $slug = Str::slug($headline);
            $count = Article::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            if (Article::where('slug', $slug)->exists()) continue;

            $detail = $this->getArticleDetail($articleUrl);

            $body = '';
            if (is_array($detail)) {
                $body = $detail['body'] ?? '';
            }

            // Skip live-blogs or any URL whose scraper returned no body.
            // Otherwise we'd publish title+image only (thin content).
            if (trim(strip_tags((string) $body)) === '') {
                Log::info('SkySportsNewsService: skipping article with empty body', [
                    'url' => $articleUrl,
                ]);
                continue;
            }

            $detailImageUrl = $detail['images'][0]['url'] ?? $imageUrl;

            $localImage = null;
            if ($detailImageUrl) {
                $localImage = $this->downloadImage($detailImageUrl, $slug);
            }

            $metaKeywords = 'football, soccer, ' . strtolower($headline);

            Article::create([
                'title' => $headline,
                'slug' => $slug,
                'content' => $body,
                'image' => $localImage,
                'meta_description' => Str::limit($summary ?: strip_tags($body), 500),
                'meta_keywords' => $metaKeywords,
                'published_at' => now(),
            ]);

            $stored++;
        }

        Log::info("SkySportsNewsService: stored {$stored} articles");

        return $stored;
    }

    protected function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) return null;

            $extension = 'jpg';
            $path = "articles/{$slug}.{$extension}";

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Exception $e) {
            Log::warning("SkySportsNewsService: failed to download image from {$url}: " . $e->getMessage());
            return null;
        }
    }
}
