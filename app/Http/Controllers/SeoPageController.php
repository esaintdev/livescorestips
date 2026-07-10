<?php

namespace App\Http\Controllers;

use App\Models\SeoPage;
use App\Models\Prediction;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ApiFootballService;

class SeoPageController extends Controller
{
    protected $apiFootballService;

    public function __construct(ApiFootballService $apiFootballService)
    {
        $this->apiFootballService = $apiFootballService;
    }

    public function show(Request $request, $slug, $date = null)
    {
        $seoPage = SeoPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Standard Page Data Logic (Mirrors HomeController)
        if (!$date) $date = $request->input('date', now()->format('Y-m-d'));
        $isLive = $request->has('live');

        // Generate date range (Yesterday to +5 days)
        $dateRange = [];
        $start = now()->subDay();
        for ($i = 0; $i < 7; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_today' => $current->isToday(),
                'label' => $current->isToday() ? 'TODAY' : $current->format('D')
            ];
        }

        $liveCount = Cache::remember('live_predictions_count', now()->addMinutes(1), function () {
            return Prediction::liveWindow()->distinct('fixture_id')->count();
        });

        // Fetch Predictions for the layout
        $query = Prediction::query();
        if ($isLive) {
            $query->liveWindow();
        } else {
            $query->whereDate('match_date', $date);
        }

        $predictions = $query->orderBy('match_time', 'asc')->take(20)->get();

        // Fetch latest articles
        $latestArticles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(4)
            ->get();

        // Fetch recent winnings
        $recentWinnings = Cache::remember('recent_winnings_seo', now()->addHours(1), function () {
            return Prediction::where('status', 'won')
                ->orderBy('match_date', 'desc')
                ->limit(6)
                ->get();
        });

        // Prepare metadata
        $metadata = [
            'page_title' => $seoPage->meta_title ?: $seoPage->title,
            'page_description' => $seoPage->meta_description,
            'page_keywords' => $seoPage->meta_keywords,
        ];

        return view('seo-pages.show', compact(
            'seoPage',
            'predictions',
            'latestArticles',
            'recentWinnings',
            'metadata',
            'date',
            'dateRange',
            'isLive',
            'liveCount'
        ));
    }
}
