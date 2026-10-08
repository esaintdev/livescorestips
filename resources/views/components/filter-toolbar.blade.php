@props([
    'date',
    'dateRange',
    'isLive' => false,
    'liveCount' => 0,
    'route' => 'home',
    'routeParams' => [],
    'marketFilters' => [],
    'selectedMarket' => null,
])

@php
    $previousDate = \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d');
    $nextDate = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d');
    $isToday = $date == now()->format('Y-m-d');
    $prevIsToday = $previousDate == now()->format('Y-m-d');
    $nextIsToday = $nextDate == now()->format('Y-m-d');

    // Build prev/next route
    $prevParams = $routeParams;
    $nextParams = $routeParams;
    if (in_array($route, ['home', 'home.date'])) {
        $prevRoute = 'home.date';
        $prevParams['date'] = $previousDate;
        $nextRoute = 'home.date';
        $nextParams['date'] = $nextDate;
    } elseif (in_array($route, ['category.show', 'category.date'])) {
        $prevRoute = 'category.date';
        $nextRoute = 'category.date';
        $prevParams['date'] = $previousDate;
        $nextParams['date'] = $nextDate;
    } elseif (in_array($route, ['predictions.all', 'predictions.all.date'])) {
        $prevRoute = 'predictions.all.date';
        $nextRoute = 'predictions.all.date';
        $prevParams['date'] = $previousDate;
        $nextParams['date'] = $nextDate;
    } elseif (in_array($route, ['worldcup.index', 'worldcup.date'])) {
        $prevRoute = 'worldcup.date';
        $nextRoute = 'worldcup.date';
        $prevParams['date'] = $previousDate;
        $nextParams['date'] = $nextDate;
    } else {
        $prevRoute = $route;
        $nextRoute = $route;
        $prevParams['date'] = $previousDate;
        $nextParams['date'] = $nextDate;
    }

    $formattedDate = \Carbon\Carbon::parse($date)->format('d-m-Y');

    // Normalize market filters
    $normalizedMarketFilters = collect($marketFilters);
    if ($normalizedMarketFilters->isEmpty()) {
        $normalizedMarketFilters = collect([
            ['name' => 'All Tips', 'slug' => null, 'active' => ($route === 'home')],
            ['name' => 'Banker Tips', 'slug' => 'banker-tips', 'active' => in_array($route, ['banker.show', 'banker.date'])],
            ['name' => 'Home Win', 'slug' => 'home-wins', 'active' => false],
            ['name' => 'Draw', 'slug' => 'draws', 'active' => false],
            ['name' => 'Away Win', 'slug' => 'away-wins', 'active' => false],
            ['name' => 'Both Teams To Score', 'slug' => 'btts-gg', 'active' => false],
            ['name' => 'Over 2.5 Goals', 'slug' => 'over-2-5-goals', 'active' => false],
            ['name' => 'Over 1.5 Goals', 'slug' => 'over-1-5-goals', 'active' => false],
            ['name' => 'Double Chance', 'slug' => 'double-chance', 'active' => false],
            ['name' => 'Correct Score', 'slug' => 'correct-score', 'active' => false],
        ]);
    }

    $selectedMarketSlug = $selectedMarket ?? request()->route('categorySlug') ?? request()->input('market');
    $activeMarketName = 'All Tips';

    if (in_array($route, ['banker.show', 'banker.date'])) {
        $activeMarketName = 'Banker Tips';
    } else {
        foreach ($normalizedMarketFilters as $mf) {
            $slug = is_array($mf) ? ($mf['slug'] ?? null) : ($mf->slug ?? null);
            $name = is_array($mf) ? ($mf['name'] ?? 'All Tips') : ($mf->name ?? 'All Tips');
            $isActive = is_array($mf) ? (!empty($mf['active'])) : (!empty($mf->active));

            if (($selectedMarketSlug && $slug === $selectedMarketSlug) || ($isActive && $selectedMarketSlug === $slug)) {
                $activeMarketName = $name;
                break;
            }
        }
    }

    $sortOptions = [
        ['name' => 'Time', 'slug' => 'time'],
        ['name' => 'League', 'slug' => 'league'],
        ['name' => 'Probability', 'slug' => 'predictions'],
        ['name' => 'Odds', 'slug' => 'odds'],
    ];

    $probOptions = [
        ['name' => 'All Matches', 'slug' => '0'],
        ['name' => '50% – 100%', 'slug' => '50'],
        ['name' => '55% – 100%', 'slug' => '55'],
        ['name' => '60% – 100%', 'slug' => '60'],
        ['name' => '65% – 100%', 'slug' => '65'],
        ['name' => '70% – 100%', 'slug' => '70'],
        ['name' => '75% – 100%', 'slug' => '75'],
        ['name' => '80% – 100%', 'slug' => '80'],
    ];

    $oddsOptions = [
        ['name' => 'Any', 'slug' => '0'],
        ['name' => '> 1.20', 'slug' => '1.20'],
        ['name' => '> 1.30', 'slug' => '1.30'],
        ['name' => '> 1.40', 'slug' => '1.40'],
        ['name' => '> 1.50', 'slug' => '1.50'],
        ['name' => '> 1.60', 'slug' => '1.60'],
        ['name' => '> 1.70', 'slug' => '1.70'],
        ['name' => '> 1.80', 'slug' => '1.80'],
        ['name' => '> 1.90', 'slug' => '1.90'],
        ['name' => '> 2.00', 'slug' => '2.00'],
    ];
@endphp

<div id="bm-filter-bar" class="bm-filter-bar">
    <div class="bm-filter-groups">
        {{-- Live Button --}}
        <a href="{{ request()->fullUrlWithQuery(['live' => $isLive ? null : 1]) }}" 
           class="bm-filter-live-btn {{ $isLive ? 'active' : '' }}" title="Live Matches">
            <span class="bm-live-dot"></span>
            <span class="bm-live-text">Live</span>
            @if($liveCount > 0) <span class="bm-live-count">{{ $liveCount }}</span> @endif
        </a>

        {{-- MATCHES / Market Dropdown with real crawlable HTML <a> links --}}
        <div id="bm-dd-markets" class="bm-dd-wrap"
             data-options='@json($normalizedMarketFilters->map(fn($f) => ['name' => is_array($f) ? $f['name'] : $f->name, 'slug' => (is_array($f) ? ($f['slug'] ?? '') : ($f->slug ?? ''))])->values())'>
            <button id="bm-dd-btn-markets" class="bm-dd-btn" type="button" aria-haspopup="listbox">
                <span class="bm-dd-label">Matches</span>
                <span class="bm-dd-value">{{ $activeMarketName }}</span>
                <i class="fa fa-caret-down bm-dd-arrow"></i>
            </button>
            <ul class="bm-dd-menu" role="listbox">
                @foreach($normalizedMarketFilters as $f)
                    @php
                        $slug = is_array($f) ? ($f['slug'] ?? null) : ($f->slug ?? null);
                        $name = is_array($f) ? ($f['name'] ?? '') : ($f->name ?? '');
                        $isItemActive = ($selectedMarketSlug && $slug === $selectedMarketSlug)
                            || (!$selectedMarketSlug && empty($slug) && !in_array($route, ['banker.show', 'banker.date']))
                            || (in_array($route, ['banker.show', 'banker.date']) && $slug === 'banker-tips');

                        if (in_array($route, ['worldcup.index', 'worldcup.date'])) {
                            $targetUrl = $slug
                                ? ($isToday ? route('worldcup.index', ['market' => $slug]) : route('worldcup.date', ['market' => $slug, 'date' => $date]))
                                : ($isToday ? route('worldcup.index') : route('worldcup.date', ['date' => $date]));
                        } elseif ($slug === 'banker-tips') {
                            $targetUrl = $isToday ? route('banker.show') : route('banker.date', ['date' => $date]);
                        } elseif ($slug) {
                            $targetUrl = $isToday ? url('/' . $slug) : url('/' . $slug . '/' . $date);
                        } else {
                            $targetUrl = $isToday ? url('/') : url('/home/' . $date);
                        }
                    @endphp
                    <li class="bm-dd-item bm-dd-item-link {{ $isItemActive ? 'active' : '' }}" role="option" data-value="{{ $slug ?? '' }}">
                        <a href="{{ $targetUrl }}" class="bm-dd-link" @if(!$isToday) rel="nofollow" @endif>
                            {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- SORT BY Dropdown --}}
        <div id="bm-dd-sort" class="bm-dd-wrap"
             data-options='@json($sortOptions)'>
            <button id="bm-dd-btn-sort" class="bm-dd-btn" type="button" aria-haspopup="listbox">
                <span class="bm-dd-label">Sort by</span>
                <span class="bm-dd-value">Time</span>
                <i class="fa fa-caret-down bm-dd-arrow"></i>
            </button>
            <ul class="bm-dd-menu" role="listbox">
                @foreach($sortOptions as $idx => $sOpt)
                    <li class="bm-dd-item {{ $idx === 0 ? 'active' : '' }}" role="option" data-value="{{ $sOpt['slug'] }}">
                        {{ $sOpt['name'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- PREDICTION / Probability Dropdown --}}
        <div id="bm-dd-prediction" class="bm-dd-wrap"
             data-options='@json($probOptions)'>
            <button id="bm-dd-btn-prediction" class="bm-dd-btn" type="button" aria-haspopup="listbox">
                <span class="bm-dd-label">Probability</span>
                <span class="bm-dd-value">All</span>
                <i class="fa fa-caret-down bm-dd-arrow"></i>
            </button>
            <ul class="bm-dd-menu" role="listbox">
                @foreach($probOptions as $idx => $pOpt)
                    <li class="bm-dd-item {{ $idx === 0 ? 'active' : '' }}" role="option" data-value="{{ $pOpt['slug'] }}">
                        {{ $pOpt['name'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- MIN. ODDS Dropdown --}}
        <div id="bm-dd-odds" class="bm-dd-wrap"
             data-options='@json($oddsOptions)'>
            <button id="bm-dd-btn-odds" class="bm-dd-btn" type="button" aria-haspopup="listbox">
                <span class="bm-dd-label">Min. Odds</span>
                <span class="bm-dd-value">Any</span>
                <i class="fa fa-caret-down bm-dd-arrow"></i>
            </button>
            <ul class="bm-dd-menu" role="listbox">
                @foreach($oddsOptions as $idx => $oOpt)
                    <li class="bm-dd-item {{ $idx === 0 ? 'active' : '' }}" role="option" data-value="{{ $oOpt['slug'] }}">
                        {{ $oOpt['name'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Clear Filters Button --}}
        <button id="bm-clear-filters" class="bm-clear-btn" title="Clear all filters">
            <i class="fa fa-times"></i>
        </button>
    </div>

    {{-- Date Navigation --}}
    <div class="bm-inline-date-nav">
        <a href="{{ route($prevRoute, $prevParams) }}" @if(!$prevIsToday) rel="nofollow" @endif class="bm-date-arrow" title="Previous day">
            <i class="fa fa-chevron-left"></i>
        </a>
        <button id="bm-date-picker-btn" class="bm-date-current" title="Select date">
            <i class="fa fa-calendar-o"></i>
            <span>{{ $formattedDate }}</span>
        </button>
        <a href="{{ route($nextRoute, $nextParams) }}" @if(!$nextIsToday) rel="nofollow" @endif class="bm-date-arrow" title="Next day">
            <i class="fa fa-chevron-right"></i>
        </a>
    </div>
</div>

@push('styles')
<style>
/* ========================================
   FILTER TOOLBAR
   ======================================== */
.bm-filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 6px 10px;
    background: #1a1a1a;
    border-radius: 12px;
    margin-bottom: 20px;
    flex-wrap: nowrap;
    position: relative;
    z-index: 100;
}

.bm-filter-groups {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
}

/* Dropdown Wrapper */
.bm-dd-wrap {
    position: relative;
    flex-shrink: 0;
}

/* Dropdown Button */
.bm-dd-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #2a2a2a;
    border: 1px solid #333;
    border-radius: 8px;
    padding: 5px 10px;
    color: #ccc;
    font-size: 11px;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
    white-space: nowrap;
    min-width: 0;
}

.bm-dd-btn:hover {
    background: #333;
    border-color: #555;
    color: #fff;
}

.bm-dd-btn .bm-dd-label {
    color: #888;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.bm-dd-btn .bm-dd-value {
    color: #fff;
    font-weight: 600;
    font-size: 12px;
}

.bm-dd-btn .bm-dd-arrow {
    color: #666;
    font-size: 10px;
    margin-left: 2px;
}

/* Dropdown Menu */
.bm-dd-menu {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    min-width: 180px;
    background: #1e1e1e;
    border: 1px solid #333;
    border-radius: 8px;
    padding: 4px 0;
    margin: 0;
    list-style: none;
    z-index: 1000;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    max-height: 260px;
    overflow-y: auto;
}

.bm-dd-menu.open {
    display: block;
}

.bm-dd-item {
    padding: 8px 14px;
    font-size: 13px;
    color: #bbb;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}

.bm-dd-item.bm-dd-item-link {
    padding: 0;
}

.bm-dd-item.bm-dd-item-link .bm-dd-link {
    display: block;
    padding: 8px 14px;
    color: inherit;
    text-decoration: none;
    width: 100%;
}

.bm-dd-item:hover {
    background: #2a2a2a;
    color: #fff;
}

.bm-dd-item.bm-dd-item-link:hover .bm-dd-link,
.bm-dd-item.bm-dd-item-link .bm-dd-link:hover {
    color: #fff;
    text-decoration: none;
}

.bm-dd-item.active {
    color: #ff6b00;
    background: rgba(255, 107, 0, 0.08);
    font-weight: 600;
}

/* Inline Date Nav */
.bm-inline-date-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.bm-date-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #2a2a2a;
    border: 1px solid #333;
    border-radius: 8px;
    color: #ccc;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    font-size: 11px;
}

.bm-date-arrow:hover {
    background: #333;
    border-color: #555;
    color: #fff;
    text-decoration: none;
}

.bm-date-current {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #2a2a2a;
    border: 1px solid #333;
    border-radius: 8px;
    padding: 5px 10px;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
    white-space: nowrap;
}

.bm-date-current:hover {
    background: #333;
    border-color: #555;
}

.bm-date-current i {
    color: #ff6b00;
    font-size: 13px;
}

/* Hide labels earlier to prevent wrapping on medium screens */
@media (max-width: 1200px) {
    .bm-dd-btn .bm-dd-label {
        display: none;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .bm-filter-bar {
        padding: 4px 6px;
        gap: 4px;
        border-radius: 10px;
        flex-wrap: wrap; /* On very small screens, it has to wrap if it doesn't fit, but it's usually inside a toggled menu */
    }

    .bm-dd-btn {
        padding: 5px 8px;
        font-size: 11px;
    }

    .bm-dd-btn .bm-dd-label {
        display: none;
    }

    .bm-dd-menu {
        min-width: 150px;
        left: 0;
        right: auto;
    }

    .bm-inline-date-nav {
        margin-left: 0;
        width: 100%;
        justify-content: center;
        padding-top: 4px;
        border-top: 1px solid #2a2a2a;
        margin-top: 2px;
    }

    .bm-date-current span {
        font-size: 11px;
    }
}

@media (max-width: 400px) {
    .bm-filter-bar {
        gap: 4px;
        padding: 4px 6px;
    }

    .bm-dd-btn {
        padding: 4px 6px;
        font-size: 10px;
    }

    .bm-dd-btn .bm-dd-value {
        font-size: 10px;
    }

    .bm-date-arrow {
        width: 26px;
        height: 26px;
    }

    .bm-date-current {
        padding: 4px 8px;
        font-size: 10px;
    }
}

/* Clear Filters Button */
.bm-clear-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #ff4444;
    border: 1px solid #ff4444;
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 2px;
}

.bm-clear-btn:hover {
    background: #cc3333;
    color: #fff;
}

.bm-clear-btn.has-filters {
    display: flex;
}

/* Mobile filter bar toggle */
#bm-mobile-filter-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #e0e0e0;
    font-size: 18px;
    position: relative;
}

#bm-mobile-filter-toggle:hover {
    color: #fff;
}

#bm-mobile-filter-toggle .bm-filter-badge {
    display: none;
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 8px;
    background: #ff6b00;
    border-radius: 50%;
}

#bm-mobile-filter-toggle .bm-filter-badge.show {
    display: block;
}

/* Light theme filter toggle */
.light-theme #bm-mobile-filter-toggle {
    color: #555;
}

.light-theme #bm-mobile-filter-toggle:hover {
    color: #222;
}

@media (max-width: 768px) {
    .bm-filter-bar {
        display: none;
    }
    
    .bm-filter-bar.bm-filter-bar-visible {
        display: flex;
        margin-top: 12px;
        animation: bm-filter-slide-down 0.25s ease;
        flex-wrap: wrap; /* Allow wrap ONLY on mobile when toggled visible */
    }
}

@keyframes bm-filter-slide-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
        max-height: 0;
    }
    to {
        opacity: 1;
        transform: translateY(0);
        max-height: 300px;
    }
}

/* Live Button */
.bm-filter-live-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    height: 28px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    color: #ff4444;
    border: 1px solid #ff4444;
    background: rgba(255, 68, 68, 0.08);
    transition: all 0.2s;
}

.bm-filter-live-btn:hover {
    background: rgba(255, 68, 68, 0.15);
    color: #ff4444;
    text-decoration: none;
}

.bm-filter-live-btn.active {
    background: #ff4444;
    color: #fff;
    border-color: #ff4444;
}

.bm-filter-live-btn.active:hover {
    background: #cc3333;
}

.bm-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ff4444;
    animation: bm-live-pulse 1.5s infinite;
}

.bm-filter-live-btn.active .bm-live-dot {
    background: #fff;
}

@keyframes bm-live-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.bm-live-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    background: #ff4444;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    border-radius: 50%;
    padding: 0 4px;
}

.bm-filter-live-btn.active .bm-live-count {
    background: #fff;
    color: #ff4444;
}

@media (max-width: 768px) {
    .bm-live-text {
        display: none;
    }
    .bm-filter-live-btn {
        padding: 6px 10px;
    }
}

/* Flatpickr overrides for date picker */
.bm-flatpickr-fix .flatpickr-calendar {
    background: #1e1e1e !important;
    border: 1px solid #333 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate dropdowns from data-options (fallback if not pre-rendered)
    document.querySelectorAll('.bm-dd-wrap[data-options]').forEach(function(wrap) {
        var menu = wrap.querySelector('.bm-dd-menu');
        if (!menu || menu.children.length > 0) return;
        var options = JSON.parse(wrap.getAttribute('data-options') || '[]');
        if (!options.length) return;
        var activeFound = false;
        options.forEach(function(opt, idx) {
            var li = document.createElement('li');
            li.className = 'bm-dd-item' + (idx === 0 ? ' active' : '');
            li.setAttribute('role', 'option');
            li.setAttribute('data-value', opt.slug);
            li.textContent = opt.name;
            menu.appendChild(li);
        });
    });

    const filterBar = document.getElementById('bm-filter-bar');
    if (!filterBar) return;

    // ========================================
    // FILTER STATE VARIABLES
    // ========================================
    let currentProbMin = 0;
    let currentOddsMin = 0;
    let currentSort = 'time';

    // ========================================
    // DROPDOWN TOGGLE LOGIC
    // ========================================
    const dropdownWraps = filterBar.querySelectorAll('.bm-dd-wrap');
    
    dropdownWraps.forEach(function(wrap) {
        const btn = wrap.querySelector('.bm-dd-btn');
        const menu = wrap.querySelector('.bm-dd-menu');
        
        if (!btn || !menu) return;

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close all other dropdowns
            dropdownWraps.forEach(function(other) {
                if (other !== wrap) {
                    other.querySelector('.bm-dd-menu')?.classList.remove('open');
                }
            });
            
            menu.classList.toggle('open');
        });

        // Select item
        const items = menu.querySelectorAll('.bm-dd-item');
        items.forEach(function(item) {
            const link = item.querySelector('a.bm-dd-link');
            if (link) {
                item.addEventListener('click', function(e) {
                    if (e.target !== link && !link.contains(e.target)) {
                        link.click();
                    }
                });
                return;
            }

            item.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Update active state
                items.forEach(function(i) { i.classList.remove('active'); });
                item.classList.add('active');
                
                // Update button value text
                const valueEl = btn.querySelector('.bm-dd-value');
                if (valueEl) {
                    valueEl.textContent = item.textContent.trim();
                }
                
                // Close menu
                menu.classList.remove('open');
                
                // Trigger filter
                const ddId = wrap.id;
                const val = item.getAttribute('data-value');
                handleFilterChange(ddId, val, item.textContent.trim());
            });
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        dropdownWraps.forEach(function(wrap) {
            wrap.querySelector('.bm-dd-menu')?.classList.remove('open');
        });
    });

    // Close dropdowns on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdownWraps.forEach(function(wrap) {
                wrap.querySelector('.bm-dd-menu')?.classList.remove('open');
            });
        }
    });

    // ========================================
    // FILTER STATE TRACKING (runs even without match list)
    // ========================================
    function updateFilterUI() {
        const hasFilters = currentProbMin > 0 || currentOddsMin > 0 || currentSort !== 'time';
        
        const clearBtn2 = document.getElementById('bm-clear-filters');
        if (clearBtn2) {
            if (hasFilters) {
                clearBtn2.classList.add('has-filters');
            } else {
                clearBtn2.classList.remove('has-filters');
            }
        }
        
        const badge = document.querySelector('.bm-filter-badge');
        if (badge) {
            if (hasFilters) {
                badge.classList.add('show');
            } else {
                badge.classList.remove('show');
            }
        }
        
        // Dispatch update event for filter modal
        window.dispatchEvent(new CustomEvent('bm-filter-update', {
            detail: {
                hasFilters: hasFilters,
                sortVal: currentSort,
                probVal: currentProbMin.toString(),
                oddsVal: currentOddsMin.toString()
            }
        }));
    }

    // ========================================
    // CLEAR FILTERS BUTTON
    // ========================================
    const clearBtn = document.getElementById('bm-clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            currentProbMin = 0;
            currentOddsMin = 0;
            currentSort = 'time';
            
            // Reset prediction dropdown
            const predBtn = document.querySelector('#bm-dd-btn-prediction .bm-dd-value');
            if (predBtn) predBtn.textContent = 'All';
            const predItems = document.querySelectorAll('#bm-dd-prediction .bm-dd-item');
            predItems.forEach(function(i) { i.classList.remove('active'); });
            if (predItems.length > 0) predItems[0].classList.add('active');
            
            // Reset odds dropdown
            const oddsBtn = document.querySelector('#bm-dd-btn-odds .bm-dd-value');
            if (oddsBtn) oddsBtn.textContent = 'Any';
            const oddsItems = document.querySelectorAll('#bm-dd-odds .bm-dd-item');
            oddsItems.forEach(function(i) { i.classList.remove('active'); });
            if (oddsItems.length > 0) oddsItems[0].classList.add('active');
            
            // Reset sort dropdown
            const sortBtn = document.querySelector('#bm-dd-btn-sort .bm-dd-value');
            if (sortBtn) sortBtn.textContent = 'Time';
            const sortItems = document.querySelectorAll('#bm-dd-sort .bm-dd-item');
            sortItems.forEach(function(i) { i.classList.remove('active'); });
            const firstSort = document.querySelector('#bm-dd-sort .bm-dd-item[data-value="time"]');
            if (firstSort) firstSort.classList.add('active');
            
            // Re-sort by time and show all cards
            applySort('time');
            
            updateFilterUI();
        });
    }

    // ========================================
    // MOBILE FILTER TOGGLE
    // ========================================
    const mobileToggle = document.getElementById('bm-mobile-filter-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            filterBar.classList.toggle('bm-filter-bar-visible');
        });
    }
    
    updateFilterUI();

    // ========================================
    // GET MAIN MATCH LIST (only filter the first/predictions list)
    // ========================================
    const matchList = document.querySelector('.bm-match-list:not(:has(.bm-section-title))');
    if (!matchList) return;
    matchList.setAttribute('data-filter-scope', 'true');

    // ========================================
    // FILTER & SORT HANDLER
    // ========================================
    function handleFilterChange(ddId, value, label) {
        switch (ddId) {
            case 'bm-dd-markets':
                @if(in_array($route, ['worldcup.index', 'worldcup.date']))
                    // World Cup uses different slugs, keep ?market= param
                    if (value) {
                        let url = new URL(window.location.href);
                        url.searchParams.set('market', value);
                        window.location.href = url.href;
                    } else {
                        let url = new URL(window.location.href);
                        url.searchParams.delete('market');
                        window.location.href = url.href;
                    }
                @else
                    // Main site: navigate to proper category URL for SEO
                    var currentDate = '{{ $date }}';
                    var isToday = currentDate === '{{ now()->format('Y-m-d') }}';
                    if (value) {
                        // Navigate to category page: /{slug} or /{slug}/{date}
                        window.location.href = isToday ? '/' + value : '/' + value + '/' + currentDate;
                    } else {
                        // All Tips - back to home
                        window.location.href = isToday ? '/' : '/home/' + currentDate;
                    }
                @endif
                break;
                
            case 'bm-dd-sort':
                currentSort = value;
                applySort(value);
                updateFilterUI();
                break;
                
            case 'bm-dd-prediction':
                currentProbMin = parseFloat(value) || 0;
                applyFilters();
                updateFilterUI();
                break;
                
            case 'bm-dd-odds':
                currentOddsMin = parseFloat(value) || 0;
                applyFilters();
                updateFilterUI();
                break;
        }
    }

    function applyFilters() {
        const scope = matchList;
        if (!scope) return;
        const cards = scope.querySelectorAll('.bm-match-row-premium');
        
        cards.forEach(function(card) {
            let show = true;
            
            // Probability filter
            if (currentProbMin > 0) {
                const prob = parseFloat(card.getAttribute('data-probability'));
                if (isNaN(prob) || prob < currentProbMin) {
                    show = false;
                }
            }
            
            // Min odds filter
            if (show && currentOddsMin > 0) {
                const odds = parseFloat(card.getAttribute('data-odds'));
                if (isNaN(odds) || odds < currentOddsMin) {
                    show = false;
                }
            }
            
            card.style.display = show ? '' : 'none';
        });
        
        // Update league headers - hide empty groups (within scope only)
        scope.querySelectorAll('.bm-match-group').forEach(function(group) {
            const visibleCards = Array.from(group.querySelectorAll('.bm-match-row-premium'))
                .filter(function(c) { return c.style.display !== 'none'; });
            const header = group.querySelector('.bm-league-header');
            if (header) {
                header.style.display = visibleCards.length > 0 ? '' : 'none';
            }
        });
        
        // Also handle non-grouped cards (within scope only)
        scope.querySelectorAll('.bm-league-header').forEach(function(header) {
            if (!header.closest('.bm-match-group')) {
                let next = header.nextElementSibling;
                let hasVisible = false;
                while (next && scope.contains(next) && !next.classList.contains('bm-league-header')) {
                    if (next.classList.contains('bm-match-row-premium') && next.style.display !== 'none') {
                        hasVisible = true;
                        break;
                    }
                    next = next.nextElementSibling;
                }
                header.style.display = hasVisible ? '' : 'none';
            }
        });
    }

    function applySort(sortBy) {
        const scope = matchList;
        if (!scope) return;
        
        switch (sortBy) {
            case 'time': {
                const groups = scope.querySelectorAll('.bm-match-group');
                if (groups.length > 0) {
                    groups.forEach(function(group) {
                        const groupCards = Array.from(group.querySelectorAll('.bm-match-row-premium'));
                        groupCards.sort(function(a, b) {
                            const timeA = a.querySelector('.bm-match-time')?.textContent.trim() || '';
                            const timeB = b.querySelector('.bm-match-time')?.textContent.trim() || '';
                            return timeA.localeCompare(timeB);
                        });
                        groupCards.forEach(function(c) { group.appendChild(c); });
                    });
                } else {
                    let allCards = Array.from(scope.querySelectorAll('.bm-match-row-premium'));
                    allCards.sort(function(a, b) {
                        const timeA = a.querySelector('.bm-match-time')?.textContent.trim() || '';
                        const timeB = b.querySelector('.bm-match-time')?.textContent.trim() || '';
                        return timeA.localeCompare(timeB);
                    });
                    allCards.forEach(function(c) { scope.appendChild(c); });
                }
                break;
            }
            case 'league': {
                const groups = scope.querySelectorAll('.bm-match-group');
                if (groups.length > 0) {
                    const sortedGroups = Array.from(groups).sort(function(a, b) {
                        const leagueA = a.getAttribute('data-league') || '';
                        const leagueB = b.getAttribute('data-league') || '';
                        return leagueA.localeCompare(leagueB);
                    });
                    sortedGroups.forEach(function(g) { scope.appendChild(g); });
                }
                break;
            }
            case 'predictions': {
                // Sort by probability (highest first)
                const groups = scope.querySelectorAll('.bm-match-group');
                if (groups.length > 0) {
                    let allCards = [];
                    groups.forEach(function(group) {
                        const headerHTML = group.querySelector('.bm-league-header')?.outerHTML || '';
                        const league = group.getAttribute('data-league') || '';
                        group.querySelectorAll('.bm-match-row-premium').forEach(function(card) {
                            allCards.push({
                                element: card,
                                prob: parseFloat(card.getAttribute('data-probability')) || 0,
                                league: league,
                                headerHTML: headerHTML
                            });
                        });
                    });
                    allCards.sort(function(a, b) { return b.prob - a.prob; });
                    rebuildGroupedList(scope, allCards);
                } else {
                    // Flat layout
                    let flatCards = Array.from(scope.querySelectorAll('.bm-match-row-premium'));
                    flatCards.sort(function(a, b) {
                        return (parseFloat(b.getAttribute('data-probability')) || 0) - (parseFloat(a.getAttribute('data-probability')) || 0);
                    });
                    flatCards.forEach(function(c) { scope.appendChild(c); });
                }
                break;
            }
            case 'odds': {
                // Sort by odds (highest first)
                const groups = scope.querySelectorAll('.bm-match-group');
                if (groups.length > 0) {
                    let allCards = [];
                    groups.forEach(function(group) {
                        const headerHTML = group.querySelector('.bm-league-header')?.outerHTML || '';
                        const league = group.getAttribute('data-league') || '';
                        group.querySelectorAll('.bm-match-row-premium').forEach(function(card) {
                            allCards.push({
                                element: card,
                                odds: parseFloat(card.getAttribute('data-odds')) || 0,
                                league: league,
                                headerHTML: headerHTML
                            });
                        });
                    });
                    allCards.sort(function(a, b) { return b.odds - a.odds; });
                    rebuildGroupedList(scope, allCards);
                } else {
                    // Flat layout
                    let flatCards = Array.from(scope.querySelectorAll('.bm-match-row-premium'));
                    flatCards.sort(function(a, b) {
                        return (parseFloat(b.getAttribute('data-odds')) || 0) - (parseFloat(a.getAttribute('data-odds')) || 0);
                    });
                    flatCards.forEach(function(c) { scope.appendChild(c); });
                }
                break;
            }
        }
        
        // Re-apply probability/odds filters after sorting
        applyFilters();
    }

    function rebuildGroupedList(container, cards) {
        container.innerHTML = '';
        const leagueGroups = {};
        cards.forEach(function(item) {
            if (!leagueGroups[item.league]) {
                leagueGroups[item.league] = {
                    headerHTML: item.headerHTML,
                    cards: []
                };
            }
            leagueGroups[item.league].cards.push(item.element);
        });
        Object.keys(leagueGroups).forEach(function(league) {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'bm-match-group';
            groupDiv.setAttribute('data-league', league);
            if (leagueGroups[league].headerHTML) {
                const temp = document.createElement('div');
                temp.innerHTML = leagueGroups[league].headerHTML;
                groupDiv.appendChild(temp.firstElementChild || temp);
            }
            leagueGroups[league].cards.forEach(function(c) { groupDiv.appendChild(c); });
            container.appendChild(groupDiv);
        });
    }

    // ========================================
    // FILTER MODAL EVENT BRIDGE
    // ========================================
    // Cycle states for the filter modal
    var sortCycle = ['time', 'league', 'predictions', 'odds'];
    var probCycle = ['0', '50', '55', '60', '65', '70', '75', '80'];
    var oddsCycle = ['0', '1.20', '1.30', '1.40', '1.50', '1.60', '1.70', '1.80', '1.90', '2.00'];
    
    // Listen for filter actions from the filter modal
    window.addEventListener('bm-filter-action', function(e) {
        var type = e.detail.type;
        
        switch (type) {
            case 'markets':
                // Direct selection from filter modal dropdown
                var marketVal = e.detail.value;
                if (marketVal !== undefined) {
                    // Find the matching market item (handle empty string for "All Tips")
                    var marketItems = document.querySelectorAll('#bm-dd-markets .bm-dd-item');
                    marketItems.forEach(function(it) {
                        if (it.getAttribute('data-value') === marketVal) {
                            var link = it.querySelector('a');
                            if (link) {
                                link.click();
                            } else {
                                it.click();
                            }
                        }
                    });
                } else {
                    // Fallback: cycle to next
                    var marketItems = document.querySelectorAll('#bm-dd-markets .bm-dd-item');
                    if (marketItems.length === 0) break;
                    var currentActive = document.querySelector('#bm-dd-markets .bm-dd-item.active');
                    var currentIdx = -1;
                    marketItems.forEach(function(it, i) {
                        if (it === currentActive) currentIdx = i;
                    });
                    var nextIdx = (currentIdx + 1) % marketItems.length;
                    var link = marketItems[nextIdx].querySelector('a');
                    if (link) {
                        link.click();
                    } else {
                        marketItems[nextIdx].click();
                    }
                }
                break;
            case 'sort':
                // Direct selection from filter modal dropdown
                var sortVal = e.detail.value;
                if (sortVal) {
                    var sortItem = document.querySelector('#bm-dd-sort .bm-dd-item[data-value="' + sortVal + '"]');
                    if (sortItem) sortItem.click();
                } else {
                    // Fallback: cycle to next
                    var sortIdx = sortCycle.indexOf(currentSort);
                    sortIdx = (sortIdx + 1) % sortCycle.length;
                    sortVal = sortCycle[sortIdx];
                    var sortItem = document.querySelector('#bm-dd-sort .bm-dd-item[data-value="' + sortVal + '"]');
                    if (sortItem) sortItem.click();
                }
                break;
            case 'probability':
                // Direct selection from filter modal dropdown
                var probVal = e.detail.value;
                if (probVal) {
                    var probItem = document.querySelector('#bm-dd-prediction .bm-dd-item[data-value="' + probVal + '"]');
                    if (probItem) probItem.click();
                } else {
                    // Fallback: cycle to next
                    var probStr = currentProbMin.toString();
                    var probIdx = probCycle.indexOf(probStr);
                    probIdx = (probIdx + 1) % probCycle.length;
                    probVal = probCycle[probIdx];
                    var probItem = document.querySelector('#bm-dd-prediction .bm-dd-item[data-value="' + probVal + '"]');
                    if (probItem) probItem.click();
                }
                break;
            case 'odds':
                // Direct selection from filter modal dropdown
                var oddsVal = e.detail.value;
                if (oddsVal) {
                    var oddsItem = document.querySelector('#bm-dd-odds .bm-dd-item[data-value="' + oddsVal + '"]');
                    if (oddsItem) oddsItem.click();
                } else {
                    // Fallback: cycle to next
                    var oddsIdx = -1;
                    for (var i = 0; i < oddsCycle.length; i++) {
                        if (Math.abs(parseFloat(oddsCycle[i]) - currentOddsMin) < 0.001) {
                            oddsIdx = i;
                            break;
                        }
                    }
                    if (oddsIdx === -1) oddsIdx = 0;
                    oddsIdx = (oddsIdx + 1) % oddsCycle.length;
                    var nextOdds = oddsCycle[oddsIdx];
                    var oddsItem = document.querySelector('#bm-dd-odds .bm-dd-item[data-value="' + nextOdds + '"]');
                    if (oddsItem) oddsItem.click();
                }
                break;
            case 'date-prev':
                var prevArrow = document.querySelector('.bm-inline-date-nav .bm-date-arrow:first-child');
                if (prevArrow && prevArrow.tagName === 'A') {
                    window.location.href = prevArrow.href;
                }
                break;
            case 'date-next':
                var nextArrow = document.querySelector('.bm-inline-date-nav .bm-date-arrow:last-child');
                if (nextArrow && nextArrow.tagName === 'A') {
                    window.location.href = nextArrow.href;
                }
                break;
            case 'date-picker':
                var dateBtn = document.getElementById('bm-date-picker-btn');
                if (dateBtn) dateBtn.click();
                break;
            case 'clear':
                var clearBtn3 = document.getElementById('bm-clear-filters');
                if (clearBtn3) clearBtn3.click();
                break;
        }
    });

    // ========================================
    // DATE PICKER (Flatpickr)
    // ========================================
    const datePickerBtn = document.getElementById('bm-date-picker-btn');
    if (datePickerBtn && typeof flatpickr !== 'undefined') {
        flatpickr(datePickerBtn, {
            defaultDate: "{{ $date }}",
            dateFormat: "Y-m-d",
            disableMobile: "true",
            minDate: "{{ now()->subDays(7)->format('Y-m-d') }}",
            maxDate: "{{ now()->addDays(14)->format('Y-m-d') }}",
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    let isToday = dateStr === "{{ now()->format('Y-m-d') }}";
                    @if(in_array($route, ['home', 'home.date']))
                        window.location.href = isToday ? "{{ url('/') }}" : "{{ url('/home') }}/" + dateStr;
                    @elseif(in_array($route, ['category.show', 'category.date']))
                        let base = "{{ url('/') }}/{{ $routeParams['categorySlug'] ?? '' }}";
                        window.location.href = isToday ? base : base + "/" + dateStr;
                    @elseif(in_array($route, ['predictions.all', 'predictions.all.date']))
                        window.location.href = isToday ? "{{ url('/predictions/all') }}" : "{{ url('/predictions/all') }}/" + dateStr;
                    @elseif(in_array($route, ['worldcup.index', 'worldcup.date']))
                        window.location.href = isToday ? "{{ url('/worldcup-2026') }}" : "{{ url('/worldcup-2026') }}/" + dateStr;
                    @else
                        let url = new URL("{{ route($route, $routeParams) }}");
                        url.searchParams.set('date', dateStr);
                        window.location.href = url.href;
                    @endif
                }
            }
        });
    }

    // ========================================
    // RESTORE FILTER STATE FROM URL
    // ========================================
    // Check if there's a market in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const marketSlug = urlParams.get('market');
    if (marketSlug) {
        const marketItems = document.querySelectorAll('#bm-dd-markets .bm-dd-item');
        marketItems.forEach(function(item) {
            if (item.getAttribute('data-value') === marketSlug) {
                // Mark as active
                marketItems.forEach(function(i) { i.classList.remove('active'); });
                item.classList.add('active');
                const valueEl = document.querySelector('#bm-dd-btn-markets .bm-dd-value');
                if (valueEl) valueEl.textContent = item.textContent.trim();
            }
        });
    }
});
</script>
@endpush
