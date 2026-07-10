<nav class="bm-bottom-nav d-lg-none">
    <a href="{{ route('home') }}" class="bm-bottom-item {{ request()->routeIs('home') && !request()->has('live') ? 'active' : '' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Home</span>
    </a>

    <a href="{{ route('predictions.all') }}" class="bm-bottom-item {{ request()->routeIs('predictions.all*') ? 'active' : '' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="8" y1="9" x2="16" y2="9"/>
            <line x1="8" y1="13" x2="16" y2="13"/>
            <line x1="8" y1="17" x2="12" y2="17"/>
        </svg>
        <span>Tips</span>
    </a>

    <a href="{{ route('blog.index') }}" class="bm-bottom-item {{ request()->routeIs('blog.*') ? 'active' : '' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16v16H4z"/>
            <line x1="8" y1="8" x2="16" y2="8"/>
            <line x1="8" y1="12" x2="14" y2="12"/>
            <line x1="8" y1="16" x2="12" y2="16"/>
        </svg>
        <span>Blog</span>
    </a>

    <a href="{{ route('worldcup.index') }}" class="bm-bottom-item {{ request()->routeIs('worldcup.*') ? 'active' : '' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <ellipse cx="12" cy="12" rx="4" ry="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
        </svg>
        <span>World</span>
    </a>

    <a href="{{ route('vip.index') }}" class="bm-bottom-item {{ request()->routeIs('vip.*') ? 'active' : '' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
        </svg>
        <span>VIP</span>
    </a>
</nav>

<style>
    .bm-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        align-items: center;
        justify-content: space-around;
        background: #1a1a1a;
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 6px 0 env(safe-area-inset-bottom, 6px);
        z-index: 1050;
    }
    .bm-bottom-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 4px 12px;
        text-decoration: none;
        color: rgba(255,255,255,0.4);
        transition: color 0.2s;
        -webkit-tap-highlight-color: transparent;
    }
    .bm-bottom-item svg {
        display: block;
    }
    .bm-bottom-item span {
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.02em;
    }
    .bm-bottom-item.active,
    .bm-bottom-item:hover {
        color: #ff6b00;
    }
    .bm-bottom-item.active span,
    .bm-bottom-item:hover span {
        color: #ff6b00;
    }

    .light-theme .bm-bottom-nav {
        background: #f8f9fa;
        border-top-color: rgba(0,0,0,0.08);
    }
    .light-theme .bm-bottom-item {
        color: rgba(0,0,0,0.3);
    }
    .light-theme .bm-bottom-item.active,
    .light-theme .bm-bottom-item:hover {
        color: #ff6b00;
    }
    .light-theme .bm-bottom-item.active span,
    .light-theme .bm-bottom-item:hover span {
        color: #ff6b00;
    }

    body.livescore-theme {
        padding-bottom: 56px;
    }
    @media (min-width: 992px) {
        body.livescore-theme {
            padding-bottom: 0;
        }
    }
</style>