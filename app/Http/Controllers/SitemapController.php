<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Prediction;
use App\Models\SeoPage;
use App\Services\ApiFootballService;
use Carbon\Carbon;

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
            'predictions' => Prediction::max('content_updated_at') ?? Prediction::max('updated_at'),
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
                'lastmod' => $lastmod ? Carbon::parse($lastmod)->toAtomString() : null,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        // Skip admin-added placeholder leagues ("League #123") which have no real name
        $leagues = collect(app(ApiFootballService::class)->getPopularLeagues())
            ->reject(fn ($league) => str_starts_with($league['name'], 'League #'));
        foreach ($leagues as $league) {
            $lastmod = Prediction::where('league_id', $league['id'])->max('updated_at');

            $urls[] = [
                'loc' => route('league.show', ['id' => $league['id'], 'slug' => $league['slug']]),
                'lastmod' => $lastmod ? Carbon::parse($lastmod)->toAtomString() : null,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $blogCategories = ArticleCategory::where('slug', '!=', 'blog')->get();
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

        // Only records that can be represented by a real, directly-resolving
        // canonical URL are emitted. A record without a slug would resolve via
        // the legacy fixture-id redirect (301), so it must not be listed.
        $canonicalPredictions = Prediction::whereNotNull('slug')
            ->whereNotNull('match_date')
            ->where('match_date', '>=', now()->subDays(30)->startOfDay())
            ->where('match_date', '<=', now()->addDays(14)->endOfDay())
            ->get()
            ->groupBy(function ($prediction) {
                return $prediction->fixture_id ?: 'manual_'.$prediction->id;
            })
            ->map(function ($group) {
                // One canonical URL per provider fixture: prefer a real tip,
                // then the earliest record (stable identity).
                return $group->sortBy(function ($prediction) {
                    return ($prediction->hasRealTip() ? '0' : '1').str_pad((string) $prediction->id, 12, '0', STR_PAD_LEFT);
                })->first();
            })
            ->filter()
            // Only leagues people search for; other match pages are noindex
            ->filter(fn ($prediction) => league_is_indexable($prediction->league_id))
            ->values()
            ->sortByDesc('match_date');

        foreach ($canonicalPredictions as $prediction) {
            $date = $prediction->match_date->format('Y-m-d');

            $lastmod = $prediction->content_updated_at ?: $prediction->updated_at;

            $urls[] = [
                'loc' => route('fixture.show', ['slug' => $prediction->slug, 'date' => $date]),
                'lastmod' => $lastmod ? $lastmod->toAtomString() : null,
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        return response()->view('sitemaps.predictions', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}
