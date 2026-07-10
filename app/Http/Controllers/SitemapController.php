<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Prediction;
use App\Models\SeoPage;

class SitemapController extends Controller
{
    public function index()
    {
        return response()->view('sitemaps.index')
            ->header('Content-Type', 'text/xml');
    }

    public function main()
    {
        $urls = [];

        $staticPages = [
            ['route' => 'home', 'priority' => '1.0'],
            ['route' => 'blog.index', 'priority' => '0.9'],
            ['route' => 'vip.index', 'priority' => '0.9'],
            ['route' => 'vip-results.index', 'priority' => '0.8'],
            ['route' => 'recent-winnings.index', 'priority' => '0.8'],
            ['route' => 'predictions.all', 'priority' => '0.9'],
            ['route' => 'about', 'priority' => '0.7'],
            ['route' => 'contact', 'priority' => '0.7'],
            ['route' => 'privacy', 'priority' => '0.6'],
            ['route' => 'terms', 'priority' => '0.6'],
            ['route' => 'worldcup.index', 'priority' => '0.8'],
            ['route' => 'banker.show', 'priority' => '0.9'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => $page['priority'],
            ];
        }

        $categorySlugs = [
            'double-chance', 'over-1-5-goals', 'over-2-5-goals',
            'btts-gg', 'draws', 'home-wins', 'away-wins', 'correct-score',
        ];

        foreach ($categorySlugs as $slug) {
            $urls[] = [
                'loc' => route('category.show', $slug),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $blogCategories = ArticleCategory::all();
        foreach ($blogCategories as $category) {
            $urls[] = [
                'loc' => route('blog.show', $category->slug),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return response()->view('sitemaps.main', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }

    public function blog()
    {
        $urls = [];

        $articles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($articles as $article) {
            $urls[] = [
                'loc' => route('blog.show', $article->slug),
                'lastmod' => $article->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return response()->view('sitemaps.blog', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }

    public function seo()
    {
        $urls = [];

        $seoPages = SeoPage::where('is_active', true)->get();
        foreach ($seoPages as $seoPage) {
            $urls[] = [
                'loc' => route('seo.show', $seoPage->slug),
                'lastmod' => $seoPage->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        return response()->view('sitemaps.seo', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }

    public function predictions()
    {
        $urls = [];

        $predictions = Prediction::orderBy('match_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('fixture_id');

        foreach ($predictions as $prediction) {
            $date = $prediction->match_date ? $prediction->match_date->format('Y-m-d') : 'unknown';
            $slug = $prediction->slug ?: $prediction->fixture_id;
            $urls[] = [
                'loc' => url("/match/$slug/$date"),
                'lastmod' => $prediction->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        return response()->view('sitemaps.predictions', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}
