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
        $lastModified = [
            'main' => collect([
                Prediction::max('updated_at'),
                ArticleCategory::max('updated_at'),
            ])->filter()->max(),
            'blog' => Article::whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->max('updated_at'),
            'seo' => SeoPage::where('is_active', true)->max('updated_at'),
            'predictions' => Prediction::max('updated_at'),
        ];

        return response()->view('sitemaps.index', compact('lastModified'))
            ->header('Content-Type', 'text/xml');
    }

    public function main()
    {
        $urls = [];

        $staticPages = [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'blog.index', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['route' => 'vip.index', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'vip-results.index', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'recent-winnings.index', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'predictions.all', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['route' => 'about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'privacy', 'priority' => '0.6', 'changefreq' => 'yearly'],
            ['route' => 'terms', 'priority' => '0.6', 'changefreq' => 'yearly'],
            ['route' => 'worldcup.index', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'banker.show', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        $categoryMap = [
            'double-chance' => 'Double Chance',
            'over-1-5-goals' => 'Over 1.5 Goals',
            'over-2-5-goals' => 'Over 2.5 Goals',
            'btts-gg' => 'Both Teams To Score',
            'draws' => 'Draw',
            'home-wins' => 'Home Win',
            'away-wins' => 'Away Win',
            'correct-score' => 'Correct Score',
        ];

        foreach ($categoryMap as $slug => $category) {
            $lastmod = Prediction::where('category', $category)->max('updated_at');

            $urls[] = [
                'loc' => route('category.show', $slug),
                'lastmod' => $lastmod ? \Carbon\Carbon::parse($lastmod)->toAtomString() : null,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $blogCategories = ArticleCategory::all();
        foreach ($blogCategories as $category) {
            $urls[] = [
                'loc' => route('blog.show', $category->slug),
                'lastmod' => optional($category->updated_at)->toAtomString(),
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

        $predictions = Prediction::whereNotNull('match_date')
            ->where(function ($query) {
                $query->whereNotNull('slug')
                    ->orWhereNotNull('fixture_id');
            })
            ->orderBy('match_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique(function ($prediction) {
                return $prediction->fixture_id ?: 'manual_' . $prediction->id;
            });

        foreach ($predictions as $prediction) {
            $date = $prediction->match_date->format('Y-m-d');
            $slug = $prediction->slug ?: $prediction->fixture_id;

            $urls[] = [
                'loc' => route('fixture.show', ['slug' => $slug, 'date' => $date]),
                'lastmod' => $prediction->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        return response()->view('sitemaps.predictions', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}
