<aside class="bm-sidebar-nav">
    <div class="bm-sidebar-logo">
        {{-- Logo --}}
        <a href="{{ route('home') }}" style="text-decoration: none; color: inherit; display: flex; align-items: center;">
            <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-logo-dark" style="max-height: 32px;">
            <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-logo-light" style="max-height: 32px; display:none;">
        </a>
        {{-- X close button (only visible when sidebar is open on mobile) --}}
        <button id="sidebar-close-btn" 
                aria-label="Close sidebar"
                style="display:none; background:none; border:none; color:#fff; font-size:22px; cursor:pointer; width:60px; height:60px; justify-content:center; align-items:center; margin:0; padding:0;">
            <i class="fa fa-times"></i>
        </button>
    </div>

    <div class="bm-sidebar-section mt-3">
        <a href="{{ route('home') }}" class="bm-sidebar-link {{ request()->routeIs('home') && !request()->has('date') ? 'active' : '' }}">
            <i class="fa fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('predictions.all') }}" class="bm-sidebar-link {{ request()->routeIs('predictions.all') ? 'active' : '' }}">
            <i class="fa fa-list-alt"></i>
            <span>All Predictions</span>
        </a>
        <a href="{{ route('blog.index') }}" class="bm-sidebar-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
            <i class="fa fa-newspaper-o"></i>
            <span>Blog</span>
        </a>
        <a href="{{ route('recent-winnings.index') }}" class="bm-sidebar-link {{ request()->routeIs('recent-winnings.*') ? 'active' : '' }}">
            <i class="fa fa-trophy"></i>
            <span>Winnings</span>
        </a>
        <a href="{{ route('vip-results.index') }}" class="bm-sidebar-link {{ request()->routeIs('vip-results.*') ? 'active' : '' }}">
            <i class="fa fa-history"></i>
            <span>VIP Results</span>
        </a>
        @auth('web')
        <a href="{{ route('vip.games') }}" class="bm-sidebar-link {{ request()->routeIs('vip.games') ? 'active' : '' }}">
            <i class="fa fa-gamepad"></i>
            <span>My VIP Games</span>
        </a>
        @endauth
        <a href="{{ route('vip.index') }}" class="bm-sidebar-link {{ request()->routeIs('vip.*') ? 'active' : '' }}" style="color: #ff6b00;">
            <i class="fa fa-star"></i>
            <span style="font-weight: 600;">VIP</span>
        </a>

        <div class="bm-sidebar-divider" style="height: 1px; background: rgba(255,255,255,0.05); margin: 15px 20px;"></div>
        
        <h6 style="color: #666; font-size: 11px; text-transform: uppercase; margin: 0 20px 10px; font-weight: 700;">Categories</h6>
        
        <a href="{{ route('home') }}" class="bm-sidebar-link">
            <i class="fa fa-star-o"></i>
            <span>All tips</span>
        </a>
        <a href="{{ route('category.show', 'home-wins') }}" class="bm-sidebar-link {{ request()->is('home-wins') ? 'active' : '' }}">
            <i class="fa fa-flag"></i>
            <span>Home Win</span>
        </a>
        <a href="{{ route('category.show', 'draws') }}" class="bm-sidebar-link {{ request()->is('draws') ? 'active' : '' }}">
            <i class="fa fa-handshake-o"></i>
            <span>Draw</span>
        </a>
        <a href="{{ route('category.show', 'away-wins') }}" class="bm-sidebar-link {{ request()->is('away-wins') ? 'active' : '' }}">
            <i class="fa fa-globe"></i>
            <span>Away Win</span>
        </a>
        <a href="{{ route('category.show', 'correct-score') }}" class="bm-sidebar-link {{ request()->is('correct-score') ? 'active' : '' }}">
            <i class="fa fa-list-ol"></i>
            <span>Correct Score</span>
        </a>
        <a href="{{ route('category.show', 'btts-gg') }}" class="bm-sidebar-link {{ request()->is('btts-gg') ? 'active' : '' }}">
            <i class="fa fa-exchange"></i>
            <span>Both teams to score</span>
        </a>
        <a href="{{ route('category.show', 'over-2-5-goals') }}" class="bm-sidebar-link {{ request()->is('over-2-5-goals') ? 'active' : '' }}">
            <i class="fa fa-bullseye"></i>
            <span>Under/Over: 2.5</span>
        </a>
        <a href="{{ route('category.show', 'double-chance') }}" class="bm-sidebar-link {{ request()->is('double-chance') ? 'active' : '' }}">
            <i class="fa fa-files-o"></i>
            <span>Double chance</span>
        </a>
    </div>
</aside>
