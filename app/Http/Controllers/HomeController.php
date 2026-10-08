<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Metadata;
use App\Models\Prediction;
use App\Services\ApiFootballService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    protected $apiFootballService;

    public function __construct(ApiFootballService $apiFootballService)
    {
        $this->apiFootballService = $apiFootballService;
    }

    protected function getLeagueStandings()
    {
        return Cache::remember('league_standings', now()->addHours(6), function () {
            // 39: Premier League, 140: La Liga, 78: Bundesliga, 135: Serie A, 61: Ligue 1
            $leagues = [
                'Premier League' => 39,
                'La Liga' => 140,
                'Bundesliga' => 78,
                'Serie A' => 135,
                'Ligue 1' => 61,
            ];

            $standings = [];

            // Determine season: if month >= 7 (July), use current year, else use previous year
            // This handles the European season split (e.g., Aug 2025 starts 2025 season; Jan 2026 is still 2025 season)
            $season = now()->month >= 7 ? now()->year : now()->year - 1;

            foreach ($leagues as $name => $id) {
                $standings[$name] = $this->apiFootballService->fetchLeagueStandings($id, $season);
            }

            return $standings;
        });
    }

    public function index(Request $request, $date = null)
    {
        // Get date from request or default to today
        if (! $date) {
            $date = $request->input('date', now()->format('Y-m-d'));
        }
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
                'label' => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        // Count OUR live predictions purely from DB (today's predictions whose match started 0-120 mins ago)
        $liveCount = Cache::remember('live_predictions_count', now()->addMinutes(1), function () {
            return Prediction::liveWindow()->distinct('fixture_id')->count();
        });

        // Fetch ALL predictions for the displayed date (including Auto-generated)
        $query = Prediction::query();

        if ($isLive) {
            $query->liveWindow();
        } else {
            $query->whereDate('match_date', $date);
        }

        $allPredictions = $query->orderBy('match_date', 'asc')->get()
            ->sort(function ($a, $b) {
                return $this->comparePredictions($a, $b);
            })->unique(function ($item) {
                return $item->fixture_id ?: 'manual_'.$item->id;
            })->values()->take(15);

        $allPredictions = $this->mapLiveScores($allPredictions, $date);

        // Dynamic Market Filters Mapping (Category names to slugs)
        $categoryToSlugMap = [
            'Double Chance' => 'double-chance',
            'Over 1.5 Goals' => 'over-1-5-goals',
            'Over 2.5 Goals' => 'over-2-5-goals',
            'Both Teams To Score' => 'btts-gg',
            'Draw' => 'draws',
            'Home Win' => 'home-wins',
            'Away Win' => 'away-wins',
            'Correct Score' => 'correct-score',
            'Under 2.5 Goals' => 'over-2-5-goals',
        ];

        $selectedMarketSlug = $request->input('market');
        $selectedCategory = $selectedMarketSlug ? array_search($selectedMarketSlug, $categoryToSlugMap) : null;

        if ($selectedCategory) {
            $predictions = $allPredictions->where('category', $selectedCategory)
                ->sort(function ($a, $b) {
                    return $this->comparePredictions($a, $b);
                });
        } else {
            // Show all predictions, maintaining the ranking
            $predictions = $allPredictions;
        }

        // Fetch strictly TODAY's tips (including auto-generated)
        $allTodaysTips = Prediction::whereDate('match_date', now()->format('Y-m-d'))
            ->orderBy('match_date', 'asc')
            ->get()
            ->sort(function ($a, $b) {
                return $this->comparePredictions($a, $b);
            });

        $allTodaysTips = $this->mapLiveScores($allTodaysTips, now()->format('Y-m-d'));

        if ($selectedCategory) {
            $todaysTips = $allTodaysTips->where('category', $selectedCategory)
                ->sort(function ($a, $b) {
                    return $this->comparePredictions($a, $b);
                });
        } else {
            // Show all today's tips, maintaining the ranking
            $todaysTips = $allTodaysTips;
        }

        // Fetch latest articles
        $latestArticles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(4)
            ->get();

        // Fetch upcoming fixtures (top leagues + World Cup only, one per match)
        $topLeagueIds = collect($this->apiFootballService->getPopularLeagues())->pluck('id')->toArray();
        $allUpcoming = Prediction::whereDate('match_date', '>', now()->format('Y-m-d'))
            ->whereIn('league_id', $topLeagueIds)
            ->orderBy('match_date', 'asc')
            ->get()
            ->unique('fixture_id')
            ->values();
        $page = request()->get('page', 1);
        $perPage = 10;
        $upcomingFixtures = new LengthAwarePaginator(
            $allUpcoming->forPage($page, $perPage),
            $allUpcoming->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        // Fetch recent winnings
        $recentWinnings = Cache::remember('recent_winnings_home', now()->addHours(1), function () {
            return Prediction::where('status', 'won')
                ->orderBy('match_date', 'desc')
                ->limit(6)
                ->get();
        });

        $leagueStandings = $this->getLeagueStandings();
        $metadata = Metadata::where('route_name', 'home')->pluck('value', 'key');

        $marketFilters = collect([
            ['name' => 'Tips', 'slug' => null, 'active' => ($selectedMarketSlug === null)],
            ['name' => 'Home Win', 'slug' => 'home-wins', 'active' => ($selectedMarketSlug === 'home-wins')],
            ['name' => 'Draw', 'slug' => 'draws', 'active' => ($selectedMarketSlug === 'draws')],
            ['name' => 'Away Win', 'slug' => 'away-wins', 'active' => ($selectedMarketSlug === 'away-wins')],
            ['name' => 'Correct Score', 'slug' => 'correct-score', 'active' => ($selectedMarketSlug === 'correct-score')],
            ['name' => 'Both teams to score', 'slug' => 'btts-gg', 'active' => ($selectedMarketSlug === 'btts-gg')],
            ['name' => 'Under/Over: 2.5', 'slug' => 'over-2-5-goals', 'active' => ($selectedMarketSlug === 'over-2-5-goals')],
            ['name' => 'Double chance', 'slug' => 'double-chance', 'active' => ($selectedMarketSlug === 'double-chance')],
        ]);

        $availableCategories = $allPredictions->pluck('category')->unique();

        // Add others that have data for this day
        foreach ($availableCategories as $cat) {
            if (isset($categoryToSlugMap[$cat])) {
                $slug = $categoryToSlugMap[$cat];
                if (! $marketFilters->contains('slug', $slug)) {
                    $marketFilters->push(['name' => $cat, 'slug' => $slug, 'active' => ($selectedMarketSlug === $slug)]);
                }
            }
        }

        // Limit to 12 filters max to keep it clean (scrollable anyway)
        $marketFilters = $marketFilters; // Removed take(12) to show all categories

        $bankerPredictions = Prediction::banker()
            ->whereDate('match_date', $date)
            ->orderBy('match_time')
            ->get()
            ->sort(function ($a, $b) {
                return $this->comparePredictions($a, $b);
            });

        // Dated archive URLs (/home/{date}) canonicalise to the base homepage so
        // they never compete with / as duplicate content.
        $canonical = route('home');

        return view('home', compact(
            'predictions',
            'todaysTips',
            'latestArticles',
            'upcomingFixtures',
            'recentWinnings',
            'date',
            'dateRange',
            'isLive',
            'liveCount',
            'leagueStandings',
            'metadata',
            'marketFilters',
            'bankerPredictions',
            'canonical'
        ));
    }

    public function category(Request $request, $categorySlug, $date = null)
    {
        // Convert slug to category name
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

        $category = $categoryMap[$categorySlug] ?? null;

        if (! $category) {
            abort(404);
        }

        // Get date from request or default to today
        if (! $date) {
            $date = $request->input('date', now()->format('Y-m-d'));
        }

        $isLive = $request->has('live');

        // Calculate previous and next dates
        $previousDate = Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        $predictions = Prediction::query()
            ->where('category', $category);

        if ($isLive) {
            $predictions->liveWindow();
        } else {
            $predictions->whereDate('match_date', $date);
        }

        $predictions = $predictions->get()->sort(function ($a, $b) {
            // Softened sorting: all leagues get a minimum positive priority
            $tier1Ids = [1, 39, 140, 135, 78, 61, 2, 3, 848];
            $tier1Names = ['World Cup', 'Premier League', 'La Liga', 'Serie A', 'Bundesliga', 'Ligue 1', 'Champions League', 'Europa League', 'Conference League'];
            $tier2Ids = [45, 40, 253, 31, 94];
            $tier2Names = ['Championship', 'Major League Soccer', 'Eredivisie', 'Primeira Liga', 'FA Cup'];

            $aPri = 0;
            if (in_array($a->league_id, $tier1Ids)) {
                $aPri = 2;
            } elseif (in_array($a->league_id, $tier2Ids)) {
                $aPri = 1;
            } elseif (in_array($a->league_name, $tier1Names)) {
                $aPri = 2;
            } elseif ($a->league_name === 'Championship' && str_contains($a->league_country, 'England')) {
                $aPri = 1;
            } elseif (in_array($a->league_name, $tier2Names)) {
                $aPri = 1;
            } else {
                $aPri = 0;
            } // All other leagues get minimum priority (visible, not buried)

            $bPri = 0;
            if (in_array($b->league_id, $tier1Ids)) {
                $bPri = 2;
            } elseif (in_array($b->league_id, $tier2Ids)) {
                $bPri = 1;
            } elseif (in_array($b->league_name, $tier1Names)) {
                $bPri = 2;
            } elseif ($b->league_name === 'Championship' && str_contains($b->league_country, 'England')) {
                $bPri = 1;
            } elseif (in_array($b->league_name, $tier2Names)) {
                $bPri = 1;
            } else {
                $bPri = 0;
            } // All other leagues get minimum priority

            if ($aPri !== $bPri) {
                return $bPri <=> $aPri;
            }
            if ($a->league_name !== $b->league_name) {
                return strcmp($a->league_name, $b->league_name);
            }

            return strcmp($a->match_time, $b->match_time);
        });

        $predictions = $this->mapLiveScores($predictions, $date);

        $todaysTips = $predictions; // Same data for both sections

        // Fetch latest articles
        $latestArticles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(4)
            ->get();

        // Fetch upcoming fixtures
        $upcomingFixtures = Prediction::whereDate('match_date', '>', now()->format('Y-m-d'))
            ->where('category', $category)
            ->orderBy('match_date', 'asc')
            ->paginate(10);

        // Fetch recent winnings (past predictions that won)
        $recentWinnings = Cache::remember('recent_winnings_home', now()->addHours(1), function () {
            return Prediction::where('status', 'won')
                ->orderBy('match_date', 'desc')
                ->limit(6)
                ->get();
        });

        $leagueStandings = $this->getLeagueStandings();
        $liveCount = Cache::remember('live_predictions_count', now()->addMinutes(1), function () {
            return Prediction::liveWindow()->distinct('fixture_id')->count();
        });

        // Generate date range
        $dateRange = [];
        $start = now()->subDay();
        for ($i = 0; $i < 7; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_today' => $current->isToday(),
                'label' => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        // Fetch SEO Metadata for this category
        $metadata = Metadata::where('route_name', 'category:'.$categorySlug)->pluck('value', 'key');

        // Dynamic Market Filters (mapping categories to slugs)
        $categoryToSlugMap = [
            'Double Chance' => 'double-chance',
            'Over 1.5 Goals' => 'over-1-5-goals',
            'Over 2.5 Goals' => 'over-2-5-goals',
            'Both Teams To Score' => 'btts-gg',
            'Draw' => 'draws',
            'Home Win' => 'home-wins',
            'Away Win' => 'away-wins',
            'Correct Score' => 'correct-score',
            'Under 2.5 Goals' => 'over-2-5-goals', // fallback
        ];

        // Core Market Filters (Always show these from user design)
        $marketFilters = collect([
            ['name' => 'Tips', 'slug' => null, 'active' => false],
            ['name' => 'Home Win', 'slug' => 'home-wins', 'active' => ($categorySlug == 'home-wins')],
            ['name' => 'Draw', 'slug' => 'draws', 'active' => ($categorySlug == 'draws')],
            ['name' => 'Away Win', 'slug' => 'away-wins', 'active' => ($categorySlug == 'away-wins')],
            ['name' => 'Correct Score', 'slug' => 'correct-score', 'active' => ($categorySlug == 'correct-score')],
            ['name' => 'Both teams to score', 'slug' => 'btts-gg', 'active' => ($categorySlug == 'btts-gg')],
            ['name' => 'Under/Over: 2.5', 'slug' => 'over-2-5-goals', 'active' => ($categorySlug == 'over-2-5-goals')],
            ['name' => 'Double chance', 'slug' => 'double-chance', 'active' => ($categorySlug == 'double-chance')],
        ]);

        $availableCategories = Prediction::whereDate('match_date', $date)->distinct()->pluck('category');

        // Add others that have data for this day
        foreach ($availableCategories as $cat) {
            if (isset($categoryToSlugMap[$cat])) {
                $slug = $categoryToSlugMap[$cat];
                if (! $marketFilters->contains('slug', $slug)) {
                    $marketFilters->push([
                        'name' => $cat,
                        'slug' => $slug,
                        'active' => ($categorySlug == $slug),
                    ]);
                }
            }
        }
        $marketFilters = $marketFilters; // Removed take(7) to show all categories

        // Dated archive URLs canonicalise to the base category page.
        $canonical = route('category.show', $categorySlug);

        return view('category.show', compact(
            'predictions',
            'todaysTips',
            'category',
            'latestArticles',
            'date',
            'dateRange',
            'isLive',
            'liveCount',
            'upcomingFixtures',
            'recentWinnings',
            'leagueStandings',
            'metadata',
            'marketFilters',
            'canonical'
        ));
    }

    public function allPredictions(Request $request, $date = null)
    {
        // Get date from request or default to today
        if (! $date) {
            $date = $request->input('date', now()->format('Y-m-d'));
        }
        $isLive = $request->has('live');

        // Calculate previous and next dates
        $previousDate = Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        // Fetch ALL predictions for the displayed date (manual + automated)
        $query = Prediction::query();
        if ($isLive) {
            $query->liveWindow();
        } else {
            $query->whereDate('match_date', $date);
        }
        $predictions = $query->get()
            ->sort(function ($a, $b) {
                // Softened sorting: all leagues get a minimum positive priority
                $tier1Ids = [1, 39, 140, 135, 78, 61, 2, 3, 848];
                $tier1Names = ['World Cup', 'Premier League', 'La Liga', 'Serie A', 'Bundesliga', 'Ligue 1', 'Champions League', 'Europa League', 'Conference League'];
                $tier2Ids = [45, 40, 253, 31, 94];
                $tier2Names = ['Championship', 'Major League Soccer', 'Eredivisie', 'Primeira Liga', 'FA Cup'];

                $aPri = 0;
                if (in_array($a->league_id, $tier1Ids)) {
                    $aPri = 2;
                } elseif (in_array($a->league_id, $tier2Ids)) {
                    $aPri = 1;
                } elseif (in_array($a->league_name, $tier1Names)) {
                    $aPri = 2;
                } elseif ($a->league_name === 'Championship' && str_contains($a->league_country, 'England')) {
                    $aPri = 1;
                } elseif (in_array($a->league_name, $tier2Names)) {
                    $aPri = 1;
                } else {
                    $aPri = 0;
                } // All other leagues get minimum priority (visible, not buried)

                $bPri = 0;
                if (in_array($b->league_id, $tier1Ids)) {
                    $bPri = 2;
                } elseif (in_array($b->league_id, $tier2Ids)) {
                    $bPri = 1;
                } elseif (in_array($b->league_name, $tier1Names)) {
                    $bPri = 2;
                } elseif ($b->league_name === 'Championship' && str_contains($b->league_country, 'England')) {
                    $bPri = 1;
                } elseif (in_array($b->league_name, $tier2Names)) {
                    $bPri = 1;
                } else {
                    $bPri = 0;
                } // All other leagues get minimum priority

                if ($aPri !== $bPri) {
                    return $bPri <=> $aPri;
                }
                if ($a->league_name !== $b->league_name) {
                    return strcmp($a->league_name, $b->league_name);
                }

                return strcmp($a->match_time, $b->match_time);
            })->unique(function ($item) {
                return $item->fixture_id ?: 'manual_'.$item->id;
            })->values();

        $predictions = $this->mapLiveScores($predictions, $date);

        $liveCount = Cache::remember('live_predictions_count', now()->addMinutes(1), function () {
            return Prediction::liveWindow()->distinct('fixture_id')->count();
        });

        // Generate date range
        $dateRange = [];
        $start = now()->subDay();
        for ($i = 0; $i < 7; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_today' => $current->isToday(),
                'label' => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        $metadata = Metadata::where('route_name', 'predictions.all')->pluck('value', 'key');

        // Dated archive URLs canonicalise to the base listing page.
        $canonical = route('predictions.all');

        return view('predictions.all', compact('predictions', 'date', 'dateRange', 'isLive', 'liveCount', 'metadata', 'canonical'));
    }

    public function worldcup(Request $request, $date = null)
    {
        // Default to today if no date specified
        $worldCupFirstMatch = '2026-06-11';
        $worldCupLastMatch = '2026-06-28';

        $today = now()->format('Y-m-d');
        if (! $date) {
            $date = $request->input('date', $today);
        }
        $isLive = $request->has('live');

        // Generate date range: 3 days before to 3 days after selected date
        $dateRange = [];
        $selected = Carbon::parse($date);
        $start = $selected->copy()->subDays(3);
        for ($i = 0; $i < 7; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_today' => $current->isToday(),
                'label' => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        $liveCount = Cache::remember('live_predictions_count', now()->addMinutes(1), function () {
            return Prediction::liveWindow()->distinct('fixture_id')->count();
        });

        // Fetch ALL World Cup predictions for the date
        $wcQuery = Prediction::where('league_id', 1);
        if ($isLive) {
            $wcQuery->liveWindow();
        } else {
            $wcQuery->whereDate('match_date', $date);
        }
        $allPredictions = $wcQuery->get()->sortBy('match_time');

        $allPredictions = $this->mapLiveScores($allPredictions, $date);

        // Category filter logic (like home page)
        $categoryToSlugMap = [
            'Home Win' => 'home-win',
            'Away Win' => 'away-win',
            'Both Teams To Score' => 'btts',
            'Draw' => 'draw',
            'Double Chance' => 'double-chance',
            'Over 1.5 Goals' => 'over-1-5',
            'Over 2.5 Goals' => 'over-2-5',
            'Under 2.5 Goals' => 'under-2-5',
            'Correct Score' => 'correct-score',
        ];

        $selectedCategorySlug = $request->input('market');
        $selectedCategory = $selectedCategorySlug ? array_search($selectedCategorySlug, $categoryToSlugMap) : null;

        if ($selectedCategory) {
            // When a category is selected, show ALL predictions for that category (one per fixture per category)
            $predictions = $allPredictions->where('category', $selectedCategory)
                ->sortBy('match_time')
                ->values();
        } else {
            // Default: show ONE prediction per fixture (the first one found)
            $predictions = $allPredictions
                ->sortBy('match_time')
                ->unique('fixture_id')
                ->values();
        }

        // Build category filter buttons from available categories in the data for this date
        $availableCategories = $allPredictions->pluck('category')->unique()->filter();
        $marketFilters = collect([
            ['name' => 'All Tips', 'slug' => null, 'active' => ($selectedCategorySlug === null)],
        ]);

        foreach ($availableCategories as $cat) {
            if (isset($categoryToSlugMap[$cat])) {
                $slug = $categoryToSlugMap[$cat];
                $marketFilters->push([
                    'name' => $cat,
                    'slug' => $slug,
                    'active' => ($selectedCategorySlug === $slug),
                ]);
            }
        }

        $metadata = Metadata::where('route_name', 'worldcup.index')->pluck('value', 'key');

        // Fallback metadata if not set in admin
        if (! isset($metadata['page_title']) || empty($metadata['page_title'])) {
            $metadata['page_title'] = 'World Cup 2026 Predictions - '.config('app.name');
        }
        if (! isset($metadata['page_description']) || empty($metadata['page_description'])) {
            $metadata['page_description'] = 'FIFA World Cup 2026 predictions, betting tips and match analysis. Expert World Cup predictions for all matches.';
        }
        if (! isset($metadata['page_keywords']) || empty($metadata['page_keywords'])) {
            $metadata['page_keywords'] = 'World Cup 2026, FIFA World Cup, World Cup predictions, World Cup betting tips, World Cup matches';
        }
        if (! isset($metadata['page_subheader']) || empty($metadata['page_subheader'])) {
            $metadata['page_subheader'] = 'All FIFA World Cup 2026 predictions in one place. Predictions generated automatically for every match.';
        }

        // Dated archive URLs canonicalise to the base World Cup page.
        $canonical = route('worldcup.index');

        return view('worldcup.index', compact(
            'predictions',
            'date',
            'dateRange',
            'isLive',
            'liveCount',
            'metadata',
            'marketFilters',
            'canonical'
        ));
    }

    public function about()
    {
        $metadata = Metadata::where('route_name', 'about')->pluck('value', 'key');

        return view('about', compact('metadata'));
    }

    public function contact()
    {
        $metadata = Metadata::where('route_name', 'contact')->pluck('value', 'key');

        return view('contact', compact('metadata'));
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'topic' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            Mail::raw(
                "Name: {$data['name']}\nEmail: {$data['email']}\nTopic: {$data['topic']}\n\n{$data['message']}",
                function ($msg) {
                    $msg->to(config('mail.mailers.smtp.to_address', 'livescorestips@gmail.com'))
                        ->subject('Contact Form: '.request('topic'));
                }
            );
        } catch (\Exception $e) {
            \Log::warning('Contact form mail failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you shortly.');
    }

    public function privacy()
    {
        $metadata = Metadata::where('route_name', 'privacy')->pluck('value', 'key');
        $leagueStandings = $this->getLeagueStandings();

        return view('privacy', compact('metadata', 'leagueStandings'));
    }

    public function terms()
    {
        $metadata = Metadata::where('route_name', 'terms')->pluck('value', 'key');
        $leagueStandings = $this->getLeagueStandings();

        return view('terms', compact('metadata', 'leagueStandings'));
    }

    private function comparePredictions($a, $b)
    {
        // SOFTENED RANKING: Top leagues still get priority display,
        // but ALL other leagues get a minimum positive score so they remain visible.
        $ranks = [
            1 => 105,  // World Cup 2026
            39 => 100, // Premier League (England)
            48 => 95,  // League Cup (England)
            140 => 90, // La Liga (Spain)
            135 => 80, // Serie A (Italy)
            78 => 70,  // Bundesliga (Germany)
            61 => 60,  // Ligue 1 (France)
            2 => 55,   // UEFA Champions League
            3 => 50,   // UEFA Europa League
            848 => 45, // UEFA Conference League
            94 => 40,  // Primeira Liga (Portugal)
            88 => 35,  // Eredivisie (Netherlands)
        ];

        // Tier 2 IDs — still get some boost
        $tier2Ids = [40, 141, 136, 79, 62, 45, 253];

        // ALL leagues get a minimum score of 5 to stay visible
        $aScore = $ranks[(int) $a->league_id] ?? (in_array((int) $a->league_id, $tier2Ids) ? 10 : 5);
        $bScore = $ranks[(int) $b->league_id] ?? (in_array((int) $b->league_id, $tier2Ids) ? 10 : 5);

        // Sorting Logic:
        // 1. Explicit Score (Ranks) — top leagues first, but others still have a positive rank
        if ($aScore !== $bScore) {
            return $bScore <=> $aScore;
        }

        // 2. Alphabetical by League Name (within same score)
        if ($a->league_name !== $b->league_name) {
            return strcmp($a->league_name, $b->league_name);
        }

        // 3. Kick-off Time
        return strcmp($a->match_time, $b->match_time);
    }

    private function mapLiveScores($predictions, $date = null)
    {
        if (! $date) {
            $date = now()->format('Y-m-d');
        }

        $allFixtures = collect($this->apiFootballService->fetchFixtures($date));
        $liveFixtures = collect($this->apiFootballService->fetchLiveFixtures());

        \Illuminate\Support\Facades\Log::info("mapLiveScores: Date={$date}, AllFixturesCount=".$allFixtures->count().', LiveFixturesCount='.$liveFixtures->count());

        $scores = [];

        foreach ($allFixtures as $fixture) {
            if (isset($fixture['fixture']['id'])) {
                $status = $fixture['fixture']['status']['short'];
                $isLive = in_array($status, ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE']);
                $isFinished = in_array($status, ['FT', 'AET', 'PEN']);

                $scores[$fixture['fixture']['id']] = [
                    'home' => $fixture['goals']['home'] ?? null,
                    'away' => $fixture['goals']['away'] ?? null,
                    'elapsed' => $fixture['fixture']['status']['elapsed'] ?? null,
                    'is_live' => $isLive,
                    'is_finished' => $isFinished,
                ];
            }
        }

        foreach ($liveFixtures as $fixture) {
            if (isset($fixture['fixture']['id'])) {
                $scores[$fixture['fixture']['id']] = [
                    'home' => $fixture['goals']['home'] ?? null,
                    'away' => $fixture['goals']['away'] ?? null,
                    'elapsed' => $fixture['fixture']['status']['elapsed'] ?? null,
                    'is_live' => true,
                    'is_finished' => false,
                ];
            }
        }

        if (empty($scores)) {
            return collect($predictions);
        }

        return collect($predictions)->map(function ($pred) use ($scores) {
            if (isset($scores[$pred->fixture_id])) {
                $score = $scores[$pred->fixture_id];

                // Update prediction with live/final data
                $pred->home_score = $score['home'] ?? ($score['is_live'] || $score['is_finished'] ? 0 : null);
                $pred->away_score = $score['away'] ?? ($score['is_live'] || $score['is_finished'] ? 0 : null);
                $pred->is_live = $score['is_live'];

                if ($score['is_live']) {
                    $pred->live_elapsed = $score['elapsed'] ? $score['elapsed']."'" : 'Live';
                } elseif ($score['is_finished']) {
                    $pred->live_elapsed = 'FT';
                }
            }

            return $pred;
        });
    }

    public function bankerPredictions(Request $request, $date = null)
    {
        if (! $date) {
            $date = $request->input('date', now()->format('Y-m-d'));
        }

        $previousDate = Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        $dateRange = [];
        $start = now()->subDay();
        for ($i = 0; $i < 7; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_today' => $current->isToday(),
                'label' => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        $predictions = Prediction::banker()
            ->whereDate('match_date', $date)
            ->orderBy('match_time')
            ->get()
            ->sort(function ($a, $b) {
                return $this->comparePredictions($a, $b);
            });

        $latestArticles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(4)
            ->get();

        $recentWinnings = Cache::remember('recent_winnings_banker', now()->addHours(1), function () {
            return Prediction::banker()
                ->where('status', 'won')
                ->orderBy('match_date', 'desc')
                ->limit(6)
                ->get();
        });

        $metadata = Metadata::where('route_name', 'banker-predictions')->pluck('value', 'key');

        // Dated archive URLs canonicalise to the base banker page.
        $canonical = route('banker.show');

        return view('banker.show', compact(
            'predictions',
            'latestArticles',
            'recentWinnings',
            'date',
            'dateRange',
            'metadata',
            'previousDate',
            'nextDate',
            'canonical'
        ));
    }
}
