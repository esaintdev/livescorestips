<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'LiveScores Tips'))</title>
    <meta name="description" content="{{ meta_description($__env->yieldContent('description', 'Betting Tips & Football Predictions')) }}">
    <meta name="keywords" content="@yield('keywords', 'football tips, predictions, betting')">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ config('app.name', 'LiveScores Tips') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="{{ request()->path() === '/' ? 'website' : 'article' }}">
    <meta property="og:title" content="@yield('title', config('app.name', 'LiveScores Tips'))">
    <meta property="og:description" content="@yield('description', 'Betting Tips & Football Predictions')">
    <meta property="og:image" content="{{ asset($ogImage ?? 'images/icon.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset_v('images/icon.png') }}">
    <link rel="manifest" href="{{ asset('webmanifest.json') }}">

    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name', 'LiveScores Tips'))">
    <meta name="twitter:description" content="@yield('description', 'Betting Tips & Football Predictions')">
    <meta name="twitter:image" content="{{ asset($ogImage ?? 'images/icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/livescore-theme.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/light-theme.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/apple.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Sofia+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

    <script type="application/ld+json">
    {!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => config('app.name', 'LiveScores Tips'),
    'alternateName' => ['LiveScoresTips', 'Live Scores Tips'],
    'url' => url('/'),
    'description' => 'Free football predictions, betting tips, live scores, fixtures, results and match analysis.',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => url('/') . '/?s={search_term_string}'
        ],
        'query-input' => 'required name=search_term_string'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => config('app.name', 'LiveScores Tips'),
    'url' => url('/'),
    'logo' => asset('images/logo.png'),
    'description' => 'Free football predictions, betting tips, live scores, fixtures, results and match analysis.'
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @stack('styles')

    @if(isset($headScripts))
        @foreach($headScripts as $script)
            {!! $script->code !!}
        @endforeach
    @endif

    @stack('head')
    <meta name="google-site-verification" content="Kks1fd1gwqXihNHAUeXZWthvugwbVcQyU1EFp1KyAKA" />
</head>

<body class="antialiased livescore-theme">
    {{-- <x-preloader /> --}}

    <div class="bm-layout-wrapper">
        <!-- Top Navbar - Full Width -->
        <x-layout.navbar />

        <!-- Page Body -->
        <div class="bm-page-body">
            <div class="bm-content-body">
                @hasSection('page-header')
                    <div class="bm-page-header">
                        @yield('page-header')
                    </div>
                @endif

                @if(!request()->routeIs('blog.*', 'privacy', 'terms', 'login', 'register', 'account.*', 'vip.checkout', 'vip.crypto', 'vip.index', 'vip.games', 'recent-winnings.*', 'worldcup.*', 'fixture.show', 'about', 'contact', 'league.show', 'country.show', 'vip-results.*', 'seo.show', 'predictions.all', 'predictions.all.*', 'category.*'))
                    <div class="bm-content-with-sidebar">
                        <div class="bm-content-main">
                            @yield('content')
                        </div>
                        {{-- Mobile overlay --}}
                        <div id="bm-sidebar-overlay" class="bm-sidebar-overlay"></div>
                        <aside class="bm-sidebar" id="bm-sidebar">
                            <button id="bm-sidebar-close" class="bm-sidebar-close d-lg-none"
                                aria-label="Close sidebar">&times;</button>
                            <x-layout.sidebar />
                        </aside>
                    </div>
                @else
                    @yield('content')
                @endif

            </div>
            <x-layout.footer />
        </div>
    </div>

    <x-newsletter-popup />
    <x-confirm-modal />

    {{-- Scripts --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>


    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Share Modal Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fab = document.getElementById('bm-share-fab');
            const modal = document.getElementById('bm-share-modal');
            const backdrop = modal?.querySelector('.bm-share-backdrop');
            const closeBtn = document.getElementById('bm-share-close');
            const options = modal?.querySelectorAll('.bm-share-option');
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            const text = encodeURIComponent(document.title + ' - ' + window.location.href);

            function openShareModal() {
                if (!modal) return;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                const nativeBtn = document.getElementById('bm-share-native-btn');
                if (nativeBtn && navigator.share) {
                    nativeBtn.style.display = 'flex';
                } else if (nativeBtn) {
                    nativeBtn.style.display = 'none';
                }
            }

            function closeShareModal() {
                if (!modal) return;
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            if (fab) fab.addEventListener('click', openShareModal);
            if (closeBtn) closeBtn.addEventListener('click', closeShareModal);
            if (backdrop) backdrop.addEventListener('click', closeShareModal);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeShareModal();
            });

            if (options) {
                options.forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const shareType = btn.getAttribute('data-share');
                        let shareUrl = '';

                        switch (shareType) {
                            case 'facebook':
                                shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
                                break;
                            case 'twitter':
                                shareUrl = 'https://twitter.com/intent/tweet?text=' + title + '&url=' + url;
                                break;
                            case 'whatsapp':
                                shareUrl = 'https://api.whatsapp.com/send?text=' + text;
                                break;
                            case 'telegram':
                                shareUrl = 'https://t.me/share/url?url=' + url + '&text=' + title;
                                break;
                            case 'copy':
                                copyToClipboard(window.location.href);
                                return;
                            case 'native':
                                if (navigator.share) {
                                    navigator.share({
                                        title: document.title,
                                        url: window.location.href
                                    }).catch(function () { });
                                }
                                closeShareModal();
                                return;
                        }

                        if (shareUrl) {
                            window.open(shareUrl, '_blank', 'noopener,noreferrer,width=600,height=500');
                        }
                        closeShareModal();
                    });
                });
            }

            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showCopyFeedback();
                    }).catch(function () {
                        fallbackCopy(text);
                    });
                } else {
                    fallbackCopy(text);
                }
            }

            function fallbackCopy(text) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback();
                } catch (e) { }
                document.body.removeChild(textarea);
            }

            function showCopyFeedback() {
                const copyBtn = modal?.querySelector('[data-share="copy"]');
                if (!copyBtn) return;
                const originalText = copyBtn.querySelector('.bm-share-label');
                if (!originalText) return;
                const saved = originalText.textContent;
                originalText.textContent = 'Copied!';
                setTimeout(function () {
                    originalText.textContent = saved;
                }, 2000);
            }
        });
    </script>

    @stack('scripts')

    @if(isset($bannerScripts) && $bannerScripts->count() > 0)
        <style>
            /* Prevent the body from being pushed down by ad-injected styles */
            html,
            body {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            #bottom-right-banner {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 99999;
                width: 330px;
                border-radius: 12px;
                display: none;
            }

            #bottom-right-banner:has(iframe, img, a, :not(script, style, button, .banner-content):not(:empty)) {
                display: block !important;
            }

            #bottom-right-banner .clever-core-ads,
            #bottom-right-banner [class*="clever"],
            #bottom-right-banner [id*="clever"],
            #bottom-right-banner iframe,
            #bottom-right-banner div {
                position: relative !important;
                top: auto !important;
                left: auto !important;
                right: auto !important;
                bottom: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                margin: 0 !important;
            }

            #bottom-right-banner img[alt*="close"],
            #bottom-right-banner [class*="close"],
            #bottom-right-banner [id*="close"],
            #bottom-right-banner [style*="cursor: pointer"][style*="position"] {
                position: absolute !important;
                top: 5px !important;
                right: 5px !important;
                bottom: auto !important;
                left: auto !important;
                width: auto !important;
                max-width: none !important;
                height: auto !important;
                z-index: 100000 !important;
            }

            [id*="pushdown"],
            [class*="pushdown"],
            [id*="topscroll"],
            [class*="topscroll"],
            [id*="leaderboard"],
            [class*="leaderboard"],
            [id*="topboard"],
            [class*="topboard"] {
                position: fixed !important;
                bottom: 20px !important;
                right: 20px !important;
                top: auto !important;
                left: auto !important;
                width: 330px !important;
                max-width: 330px !important;
                height: 180px !important;
                z-index: 99999 !important;
            }

            [id*="pushdown"] iframe,
            [class*="pushdown"] iframe,
            [id*="topscroll"] iframe,
            [class*="topscroll"] iframe,
            [id*="leaderboard"] iframe,
            [class*="leaderboard"] iframe,
            [id*="topboard"] iframe,
            [class*="topboard"] iframe {
                width: 100% !important;
                height: 100% !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
            }
        </style>

        <button onclick="
                        this.style.display='none';
                        document.getElementById('bottom-right-banner').style.display='none';
                        document.querySelectorAll('[id*=\'pushdown\'], [class*=\'pushdown\'], [id*=\'topscroll\'], [class*=\'topscroll\'], [id*=\'leaderboard\'], [class*=\'leaderboard\'], [id*=\'topboard\'], [class*=\'topboard\']').forEach(el => el.style.display = 'none');
                    "
            style="position: fixed; bottom: 185px; right: 10px; width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff; background: #222; color: #fff; font-size: 16px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.5); z-index: 2147483647; line-height: 1;">&times;</button>

        <div id="bottom-right-banner">
            <div class="banner-content" style="overflow: hidden; display: flex; flex-direction: column; gap: 8px;">
                @foreach($bannerScripts as $script)
                    {!! $script->code !!}
                @endforeach
            </div>
        </div>
    @endif

    <x-floating-share-button />
    <x-share-modal />

    <x-cookie-consent />

    <x-layout.mobile-bottom-nav />

    @if(isset($bodyScripts))
        @foreach($bodyScripts as $script)
            {!! $script->code !!}
        @endforeach
    @endif

    {{-- Settings Modal --}}
    <div id="bm-settings-modal" class="bm-settings-modal" style="display:none;">
        <div class="bm-settings-backdrop"></div>
        <div class="bm-settings-panel">
            <div class="bm-settings-header">
                <h3 class="bm-settings-title">Settings</h3>
                <button id="bm-settings-close" class="bm-settings-close">&times;</button>
            </div>
            <div class="bm-settings-body">
                <div class="bm-settings-item">
                    <div class="bm-settings-item-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5" />
                            <path
                                d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                        </svg>
                        <div>
                            <div class="bm-settings-item-label">Theme</div>
                            <div class="bm-settings-item-desc">Switch between dark and light mode</div>
                        </div>
                    </div>
                    <button id="bm-settings-theme-toggle" class="bm-settings-toggle-btn">
                        <span id="bm-settings-theme-icon">
                            <i class="fa fa-moon-o"></i>
                        </span>
                        <span id="bm-settings-theme-text">Dark</span>
                    </button>
                </div>
                <div class="bm-settings-item" id="bm-settings-filter-row" style="display:none;">
                    <div class="bm-settings-item-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" />
                        </svg>
                        <div>
                            <div class="bm-settings-item-label">Filters</div>
                            <div class="bm-settings-item-desc">Show/hide prediction filters</div>
                        </div>
                    </div>
                    <button id="bm-settings-filter-toggle" class="bm-settings-toggle-btn bm-settings-toggle-off">
                        <span>Show Filters</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Modal --}}
    <div id="bm-filter-modal" class="bm-settings-modal" style="display:none;">
        <div class="bm-settings-backdrop"></div>
        <div class="bm-settings-panel bm-filter-modal-panel">
            <div class="bm-settings-header">
                <h3 class="bm-settings-title"><i class="fa fa-filter"
                        style="margin-right: 6px; color: #ff6b00;"></i>Filters</h3>
                <button id="bm-filter-close" class="bm-settings-close">&times;</button>
            </div>
            <div class="bm-settings-body bm-fm-body">
                <div class="bm-fm-row" id="bm-filter-item-markets">
                    <div class="bm-settings-item">
                        <div class="bm-settings-item-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" />
                            </svg>
                            <div>
                                <div class="bm-settings-item-label">Matches</div>
                                <div class="bm-settings-item-desc" id="bm-filter-val-markets">All Tips</div>
                            </div>
                        </div>
                        <i class="fa fa-chevron-down" style="color: #888; font-size: 10px;"></i>
                    </div>
                    <ul class="bm-fm-dropdown" data-for="markets"></ul>
                </div>
                <div class="bm-fm-row" id="bm-filter-item-sort">
                    <div class="bm-settings-item">
                        <div class="bm-settings-item-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l4-4 4 4M7 5v14" />
                                <path d="M21 15l-4 4-4-4M17 19V5" />
                            </svg>
                            <div>
                                <div class="bm-settings-item-label">Sort by</div>
                                <div class="bm-settings-item-desc" id="bm-filter-val-sort">Time</div>
                            </div>
                        </div>
                        <i class="fa fa-chevron-down" style="color: #888; font-size: 10px;"></i>
                    </div>
                    <ul class="bm-fm-dropdown" data-for="sort">
                        <li data-value="time" class="active">Time</li>
                        <li data-value="league">League</li>
                        <li data-value="predictions">Probability</li>
                        <li data-value="odds">Odds</li>
                    </ul>
                </div>
                <div class="bm-fm-row" id="bm-filter-item-prob">
                    <div class="bm-settings-item">
                        <div class="bm-settings-item-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20V10M18 20V4M6 20v-4" />
                            </svg>
                            <div>
                                <div class="bm-settings-item-label">Probability</div>
                                <div class="bm-settings-item-desc" id="bm-filter-val-prob">All</div>
                            </div>
                        </div>
                        <i class="fa fa-chevron-down" style="color: #888; font-size: 10px;"></i>
                    </div>
                    <ul class="bm-fm-dropdown" data-for="probability">
                        <li data-value="0" class="active">All Matches</li>
                        <li data-value="50">50% – 100%</li>
                        <li data-value="55">55% – 100%</li>
                        <li data-value="60">60% – 100%</li>
                        <li data-value="65">65% – 100%</li>
                        <li data-value="70">70% – 100%</li>
                        <li data-value="75">75% – 100%</li>
                        <li data-value="80">80% – 100%</li>
                    </ul>
                </div>
                <div class="bm-fm-row" id="bm-filter-item-odds">
                    <div class="bm-settings-item">
                        <div class="bm-settings-item-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <div>
                                <div class="bm-settings-item-label">Min. Odds</div>
                                <div class="bm-settings-item-desc" id="bm-filter-val-odds">Any</div>
                            </div>
                        </div>
                        <i class="fa fa-chevron-down" style="color: #888; font-size: 10px;"></i>
                    </div>
                    <ul class="bm-fm-dropdown" data-for="odds">
                        <li data-value="0" class="active">Any</li>
                        <li data-value="1.20">&gt; 1.20</li>
                        <li data-value="1.30">&gt; 1.30</li>
                        <li data-value="1.40">&gt; 1.40</li>
                        <li data-value="1.50">&gt; 1.50</li>
                        <li data-value="1.60">&gt; 1.60</li>
                        <li data-value="1.70">&gt; 1.70</li>
                        <li data-value="1.80">&gt; 1.80</li>
                        <li data-value="1.90">&gt; 1.90</li>
                        <li data-value="2.00">&gt; 2.00</li>
                    </ul>
                </div>
                <div class="bm-fm-row" id="bm-filter-item-date">
                    <div class="bm-settings-item">
                        <div class="bm-settings-item-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <div>
                                <div class="bm-settings-item-label">Date</div>
                                <div class="bm-settings-item-desc" id="bm-filter-val-date">Today</div>
                            </div>
                        </div>
                        <div class="bm-filter-date-btns">
                            <button class="bm-filter-date-btn" id="bm-filter-date-prev" title="Previous day">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button class="bm-filter-date-btn bm-filter-date-btn-active" id="bm-filter-date-picker-btn"
                                title="Select date">
                                <i class="fa fa-calendar-o"></i>
                            </button>
                            <button class="bm-filter-date-btn" id="bm-filter-date-next" title="Next day">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bm-filter-clear-wrapper">
                    <button id="bm-filter-modal-clear" class="bm-filter-modal-clear-btn">
                        <i class="fa fa-times"></i> Clear All Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Login Modal --}}
    <div id="bm-login-modal" class="bm-settings-modal" style="display:none;">
        <div class="bm-settings-backdrop"></div>
        <div class="bm-settings-panel bm-login-modal-panel">
            <div class="bm-login-card bm-login-modal-card">
                <button id="bm-login-close" class="bm-settings-close"
                    style="position:absolute;top:16px;right:16px;z-index:1;">&times;</button>
                <div class="bm-login-header">
                    <div class="bm-login-icon">
                        <i class="fa fa-user-circle"></i>
                    </div>
                    <h3>Welcome back</h3>
                    <p>Sign in to access your account</p>
                </div>
                <form id="bm-login-form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-login-email">Email</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-envelope bm-input-icon"></i>
                            <input id="bm-login-email" type="email" class="bm-login-input" name="email" required
                                autocomplete="email" placeholder="you@example.com">
                        </div>
                        <div id="bm-login-email-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-login-password">Password</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-lock bm-input-icon"></i>
                            <input id="bm-login-password" type="password" class="bm-login-input" name="password"
                                required autocomplete="current-password" placeholder="Enter your password">
                        </div>
                        <div id="bm-login-password-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-options">
                        <label class="bm-login-remember">
                            <input type="checkbox" name="remember" id="bm-login-remember">
                            <span class="bm-login-checkbox"><i class="fa fa-check"></i></span>
                            <span>Remember me</span>
                        </label>
                    </div>
                    <div id="bm-login-general-error" class="bm-login-error"
                        style="display:none;margin-bottom:20px;padding:0;"></div>
                    <button type="submit" class="bm-login-submit">
                        Sign In <i class="fa fa-arrow-right"></i>
                    </button>
                </form>
                <div class="bm-login-footer">
                    <p>Don't have an account? <button id="bm-to-register-btn"
                            style="background:none;border:none;color:#ff6b00;font-weight:600;cursor:pointer;font-size:13px;font-family:inherit;padding:0;text-decoration:underline;">Create
                            one</button></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Register Modal --}}
    <div id="bm-register-modal" class="bm-settings-modal" style="display:none;">
        <div class="bm-settings-backdrop"></div>
        <div class="bm-settings-panel bm-login-modal-panel">
            <div class="bm-login-card bm-login-modal-card">
                <button id="bm-register-close" class="bm-settings-close"
                    style="position:absolute;top:16px;right:16px;z-index:1;">&times;</button>
                <div class="bm-login-header bm-login-header-compact">
                    <div class="bm-login-icon">
                        <i class="fa fa-user-plus"></i>
                    </div>
                    <h3>Create your account</h3>
                    <p>Join our community to access exclusive VIP predictions</p>
                </div>
                <form id="bm-register-form" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-reg-name">Full Name</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-user bm-input-icon"></i>
                            <input id="bm-reg-name" type="text" class="bm-login-input" name="name" required
                                autocomplete="name" placeholder="Your full name">
                        </div>
                        <div id="bm-reg-name-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-reg-email">Email</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-envelope bm-input-icon"></i>
                            <input id="bm-reg-email" type="email" class="bm-login-input" name="email" required
                                autocomplete="email" placeholder="you@example.com">
                        </div>
                        <div id="bm-reg-email-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-reg-phone">Phone (WhatsApp)</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-phone bm-input-icon"></i>
                            <input id="bm-reg-phone" type="text" class="bm-login-input" name="phone" required
                                autocomplete="tel" placeholder="+234...">
                        </div>
                        <div id="bm-reg-phone-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-group">
                        <label class="bm-login-label" for="bm-reg-password">Password</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-lock bm-input-icon"></i>
                            <input id="bm-reg-password" type="password" class="bm-login-input" name="password" required
                                autocomplete="new-password" placeholder="Create a password">
                        </div>
                        <div id="bm-reg-password-error" class="bm-login-error" style="display:none;"></div>
                    </div>
                    <div class="bm-login-group bm-login-group-last">
                        <label class="bm-login-label" for="bm-reg-password-confirm">Confirm Password</label>
                        <div class="bm-login-input-wrap">
                            <i class="fa fa-lock bm-input-icon"></i>
                            <input id="bm-reg-password-confirm" type="password" class="bm-login-input"
                                name="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirm your password">
                        </div>
                    </div>
                    <div id="bm-reg-general-error" class="bm-login-error"
                        style="display:none;margin-bottom:20px;padding:0;"></div>
                    <button type="submit" class="bm-login-submit">
                        Create Account <i class="fa fa-arrow-right"></i>
                    </button>
                </form>
                <div class="bm-login-footer">
                    <p>Already have an account? <button id="bm-to-login-btn"
                            style="background:none;border:none;color:#ff6b00;font-weight:600;cursor:pointer;font-size:13px;font-family:inherit;padding:0;text-decoration:underline;">Sign
                            in</button></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bm-login-modal-panel {
            max-width: 460px;
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        .bm-login-modal-card {
            background: #1a1a1a;
            border-radius: 20px;
            padding: 40px 32px;
            position: relative;
        }

        .light-theme .bm-login-modal-card {
            background: #ffffff;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.04);
        }

        .bm-login-modal-card .bm-login-header {
            margin-bottom: 28px;
        }

        .bm-login-modal-card .bm-login-options {
            margin-bottom: 24px;
        }

        #bm-login-btn,
        #bm-login-btn-mobile,
        #bm-register-btn {
            font-family: inherit;
        }
    </style>
</body>

</html>