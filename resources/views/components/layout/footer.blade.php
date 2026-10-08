@php
    $siteName = config('app.name', 'LiveScores Tips');

    // Navigation
    $navLinks = [
        ['name' => 'Home', 'route' => 'home'],
        ['name' => 'VIP Plans', 'route' => 'vip.index'],
        ['name' => 'Recent Winnings', 'route' => 'recent-winnings.index'],
        ['name' => 'All Predictions', 'route' => 'predictions.all'],
        ['name' => 'About Us', 'route' => 'about'],
        ['name' => 'Contact Us', 'route' => 'contact'],
    ];

    // Prediction categories
    $categories = [
        ['name' => 'Banker Tips', 'slug' => 'banker.show', 'route' => 'banker.show'],
        ['name' => 'Over 2.5 Goals', 'slug' => 'over-2-5-goals', 'route' => 'category.show'],
        ['name' => 'Over 1.5 Goals', 'slug' => 'over-1-5-goals', 'route' => 'category.show'],
        ['name' => 'BTTS/GG', 'slug' => 'btts-gg', 'route' => 'category.show'],
        ['name' => 'Double Chance', 'slug' => 'double-chance', 'route' => 'category.show'],
        ['name' => 'Draws', 'slug' => 'draws', 'route' => 'category.show'],
        ['name' => 'Home Wins', 'slug' => 'home-wins', 'route' => 'category.show'],
        ['name' => 'Away Wins', 'slug' => 'away-wins', 'route' => 'category.show'],
        ['name' => 'Correct Score', 'slug' => 'correct-score', 'route' => 'category.show'],
    ];

    // Top leagues for Popular Competitions
    $leagues = [
        ['id' => 39,  'name' => 'Premier League',   'slug' => 'premier-league'],
        ['id' => 140, 'name' => 'La Liga',          'slug' => 'la-liga'],
        ['id' => 78,  'name' => 'Bundesliga',       'slug' => 'bundesliga'],
        ['id' => 135, 'name' => 'Serie A',          'slug' => 'serie-a'],
        ['id' => 61,  'name' => 'Ligue 1',          'slug' => 'ligue-1'],
    ];

    // Popular Matches — fill 24 slots, one link per match (no duplicate fixture_ids)
    $popularLeagueIds = array_column($leagues, 'id');
    $popularMatches = collect([]);
    try {
        $popularMatches = \App\Models\Prediction::whereDate('match_date', '>=', now()->format('Y-m-d'))
            ->whereIn('league_id', $popularLeagueIds)
            ->orderBy('match_date', 'asc')
            ->get()
            ->unique('fixture_id')
            ->take(24);

        $count = $popularMatches->count();
        if ($count < 24) {
            $extra = \App\Models\Prediction::whereDate('match_date', '>=', now()->format('Y-m-d'))
                ->where(function ($q) use ($popularLeagueIds) {
                    $q->whereNotIn('league_id', $popularLeagueIds)->orWhereNull('league_id');
                })
                ->orderBy('match_date', 'asc')
                ->get()
                ->unique('fixture_id')
                ->take(24 - $count);
            $popularMatches = $popularMatches->concat($extra)->unique('fixture_id')->take(24);
        }
    } catch (\Exception $e) {
        $popularMatches = collect([]);
    }

    $todayMatches = $popularMatches->filter(function ($m) {
        return $m->match_date->format('Y-m-d') === now()->format('Y-m-d');
    })->sortByDesc(function ($m) {
        return $m->league_id == 1 ? 1 : 0;
    })->take(7);

    $matchColumns = [
        $popularMatches->slice(0, 8),
        $popularMatches->slice(8, 8),
        $popularMatches->slice(16, 8),
    ];

    // Try to fetch latest blog articles
    $latestArticles = [];
    try {
        if (class_exists('App\Models\Article')) {
            $latestArticles = \App\Models\Article::whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->take(5)
                ->get();
        }
    } catch (\Exception $e) {
        $latestArticles = [];
    }
@endphp

<footer class="bm-footer">
    {{-- ROW 1: Main content (About + Popular Matches + Competitions + Nav + Categories) --}}
    <div class="bm-footer-row-main">
        <div class="container">
            <div class="row">
                {{-- LEFT: About + Live Matches --}}
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="bm-footer-title">About {{ $siteName }}</h4>
                    <p class="bm-footer-about-text">
                        {{ $siteName }} provides AI-powered football predictions, expert betting tips, and match analysis across 850+ leagues worldwide. Our advanced algorithms analyze team form, head-to-head records, and statistical models to deliver accurate predictions.
                    </p>

                    @if($todayMatches->isNotEmpty())
                        <h4 class="bm-footer-title">Today's Matches</h4>
                        <div class="bm-footer-inline-links">
                            @foreach($todayMatches as $match)
                                <a href="{{ route('fixture.show', [$match->slug, $match->match_date->format('Y-m-d')]) }}" class="bm-footer-inline-link">{{ $match->home_team }} vs {{ $match->away_team }}</a>
                                @if(!$loop->last)
                                    <span class="bm-footer-inline-sep">•</span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <h4 class="bm-footer-title" style="margin-top:16px;">Contact</h4>
                    <a href="mailto:livescorestips@gmail.com" class="bm-footer-link" style="font-size:13px;">livescorestips@gmail.com</a>

                    @if(($footerLinks ?? collect())->isNotEmpty())
                        <h4 class="bm-footer-title" style="margin-top:16px;">Useful Links</h4>
                        <ul class="bm-footer-list">
                            @foreach($footerLinks as $link)
                                <li><a href="{{ $link->url }}" class="bm-footer-link" target="_blank" rel="noopener">{{ $link->title }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- RIGHT: Link groups grid --}}
                <div class="col-lg-8">
                    <div class="row">
                        {{-- Popular Matches col 1 --}}
                        <div class="col-6 col-md-4 mb-4">
                            <h4 class="bm-footer-title">Popular Matches</h4>
                            <ul class="bm-footer-list">
                                @forelse($matchColumns[0] as $match)
                                    <li><a href="{{ route('fixture.show', [$match->slug, $match->match_date->format('Y-m-d')]) }}" class="bm-footer-link">{{ $match->home_team }} vs {{ $match->away_team }}</a></li>
                                @empty
                                    <li><a href="{{ route('home') }}" class="bm-footer-link">View today's matches</a></li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Popular Matches col 2 (no heading) --}}
                        @if($matchColumns[1]->isNotEmpty())
                            <div class="col-6 col-md-4 mb-4">
                                <ul class="bm-footer-list bm-footer-list-no-title">
                                    @foreach($matchColumns[1] as $match)
                                        <li><a href="{{ route('fixture.show', [$match->slug, $match->match_date->format('Y-m-d')]) }}" class="bm-footer-link">{{ $match->home_team }} vs {{ $match->away_team }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Latest Articles col 3 --}}
                        <div class="col-6 col-md-4 mb-4">
                            <h4 class="bm-footer-title">Latest Articles</h4>
                            <ul class="bm-footer-list">
                                @forelse($latestArticles as $article)
                                    <li><a href="{{ route('blog.show', $article->slug) }}" class="bm-footer-link bm-footer-link-title" title="{{ $article->title }}">{{ $article->title }}</a></li>
                                @empty
                                    <li><a href="{{ route('blog.index') }}" class="bm-footer-link">Visit our blog</a></li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Popular Competitions --}}
                        <div class="col-6 col-md-4 mb-4">
                            <h4 class="bm-footer-title">Popular Competitions</h4>
                            <ul class="bm-footer-list">
                                @foreach($leagues as $league)
                                    <li><a href="{{ route('league.show', ['id' => $league['id'], 'slug' => $league['slug']]) }}" class="bm-footer-link">{{ $league['name'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Navigation --}}
                        <div class="col-6 col-md-4 mb-4">
                            <h4 class="bm-footer-title">Navigation</h4>
                            <ul class="bm-footer-list">
                                @foreach($navLinks as $link)
                                    <li><a href="{{ route($link['route']) }}" class="bm-footer-link">{{ $link['name'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Categories --}}
                        <div class="col-6 col-md-4 mb-4">
                            <h4 class="bm-footer-title">Categories</h4>
                            <ul class="bm-footer-list">
                                @foreach($categories as $cat)
                                    @if($cat['route'] === 'banker.show' || in_array($cat['slug'], $activeCategorySlugs ?? []))
                                        <li><a href="{{ $cat['route'] === 'banker.show' ? route('banker.show') : route('category.show', $cat['slug']) }}" class="bm-footer-link">{{ $cat['name'] }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        {{-- Useful Links moved to left column (after Contact) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 2: Logo + Social --}}
    <div class="bm-footer-row-middle">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between bm-footer-middle-inner">
                <div>
                    <a href="{{ route('home') }}">
                        <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-footer-logo bm-logo-dark" style="max-height: 40px;">
                        <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-footer-logo bm-logo-light" style="max-height: 40px; display: none;">
                    </a>
                </div>
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center;">
                    <img src="{{ asset('images/gamble.png') }}" alt="Gamble Responsibly" style="height:36px;width:auto;">
                    <img src="{{ asset('images/18plus-white-text.svg') }}" alt="18+" style="height:36px;width:auto;">
                    <img src="{{ asset('images/gamcare-logo.svg') }}" alt="GamCare" style="height:36px;width:auto;">
                    <img src="{{ asset('images/gamstop.svg') }}" alt="GamStop" style="height:36px;width:auto;">
                </div>
                <div class="bm-footer-social">
                    <a href="{{ $telegramLink ?? '#' }}" target="_blank" title="Telegram"><i class="fa fa-paper-plane"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: Copyright + Legal --}}
    <div class="bm-footer-row-bottom">
        <div class="container">
            <div class="d-flex flex-column-reverse flex-md-row align-items-center justify-content-between bm-footer-bottom-inner">
                <div class="bm-copyright">
                    &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved. 18+ Gamble Responsibly.
                </div>
                <div class="bm-footer-legal">
                    <a href="{{ route('terms') }}" class="bm-footer-legal-link">Terms of Service</a>
                    <a href="{{ route('privacy') }}" class="bm-footer-legal-link">Privacy Policy</a>
                    <a href="{{ route('contact') }}" class="bm-footer-legal-link">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</footer>

@if(isset($bodyScripts))
    @foreach($bodyScripts as $script)
        {!! $script->code !!}
    @endforeach
@endif
