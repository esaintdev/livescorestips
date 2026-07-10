<nav class="navbar navbar-expand-md navbar-dark bm-floating-navbar"
    style="background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%); padding: 0.8rem 1rem;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid bm-logo-dark" style="max-height: 40px;">
            <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid bm-logo-light" style="max-height: 40px; display:none;">
        </a>
        <button class="navbar-toggler border-0" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }} mx-2">
                    <a class="nav-link font-weight-bold text-white nav-link-custom" href="{{ route('home') }}"
                        style="font-size: 15px; letter-spacing: 0.5px;">HOME</a>
                </li>

                <li class="nav-item dropdown mx-2">
                    <a class="nav-link dropdown-toggle text-white font-weight-bold nav-link-custom"
                        href="{{ route('category.show', 'double-chance') }}" id="predictionsDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        style="font-size: 15px; letter-spacing: 0.5px;">
                        PREDICTIONS
                    </a>
                    <div class="dropdown-menu border-0 shadow-lg" aria-labelledby="predictionsDropdown"
                        style="background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%); border-radius: 8px; margin-top: 10px; min-width: 200px;">
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'double-chance') }}"
                            style="color: white;">Double
                            Chance</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'over-1-5-goals') }}"
                            style="color: white;">Over 1.5
                            Goals</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'over-2-5-goals') }}"
                            style="color: white;">Over 2.5
                            Goals</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'btts-gg') }}"
                            style="color: white;">BTTS/GG</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'draws') }}"
                            style="color: white;">Draws</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'home-wins') }}"
                            style="color: white;">Home Wins</a>
                        <a class="dropdown-item py-2" href="{{ route('category.show', 'away-wins') }}"
                            style="color: white;">Away Wins</a>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('predictions.all') ? 'active' : '' }} mx-2">
                    <a class="nav-link font-weight-bold text-white px-3 py-2 btn btn-outline-light rounded-pill nav-btn-custom"
                        href="{{ route('predictions.all') }}" style="font-size: 14px; border-width: 2px;">
                        <i class="fa fa-list-alt mr-1"></i>ALL PREDICTIONS
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('blog.*') ? 'active' : '' }} mx-2">
                    <a class="nav-link font-weight-bold text-white px-3 py-2 btn btn-outline-light rounded-pill nav-btn-custom"
                        href="{{ route('blog.index') }}" style="font-size: 14px; border-width: 2px;">BLOG</a>
                </li>

                <li class="nav-item {{ request()->routeIs('recent-winnings.*') ? 'active' : '' }} mx-2">
                    <a class="nav-link font-weight-bold text-white px-3 py-2 btn btn-outline-light rounded-pill nav-btn-custom"
                        href="{{ route('recent-winnings.index') }}" style="font-size: 14px; border-width: 2px;"><i
                            class="fa fa-trophy mr-1"></i>WINNINGS</a>
                </li>

                <li class="nav-item {{ request()->routeIs('vip.*') ? 'active' : '' }} mx-2">
                    <a class="nav-link font-weight-bold text-dark px-3 py-2 btn rounded-pill vip-btn-custom"
                        href="{{ route('vip.index') }}"
                        style="font-size: 14px; background: linear-gradient(135deg, #ff6b00 0%, #e65e00 100%); border: none; box-shadow: 0 4px 8px rgba(255, 107, 0, 0.3);">
                        <i class="fa fa-star mr-1"></i>VIP
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<style>
    /* Floating Navbar */
    .bm-floating-navbar {
        position: sticky;
        top: 16px;
        z-index: 1001;
        width: calc(100% - 32px);
        margin: 16px auto 25px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    /* Navigation Link Hover Effects */
    .nav-link-custom {
        position: relative;
        transition: all 0.3s ease;
    }

    .nav-link-custom::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: #ff6b00;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-link-custom:hover::after {
        width: 80%;
    }

    .nav-link-custom:hover {
        color: #ff6b00 !important;
        transform: translateY(-2px);
    }

    /* Dropdown Menu Hover */
    .dropdown-menu .dropdown-item {
        transition: all 0.3s ease;
        border-radius: 4px;
        margin: 2px 8px;
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.95) !important;
        color: #04145dff !important;
        transform: translateX(5px);
    }

    /* Button Navigation Links */
    .nav-btn-custom {
        transition: all 0.3s ease;
        border-color: rgba(255, 255, 255, 0.5) !important;
    }

    .nav-btn-custom:hover {
        background-color: #fff !important;
        color: #04145dff !important;
        border-color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* VIP Button Hover */
    .vip-btn-custom {
        transition: all 0.3s ease;
    }

    .vip-btn-custom:hover {
        background: linear-gradient(135deg, #ff8800 0%, #ff6b00 100%) !important;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 12px rgba(255, 107, 0, 0.5) !important;
    }

    /* Active State */
    .nav-item.active .nav-link {
        color: #ff6b00 !important;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .navbar-nav .nav-item {
            margin: 5px 0 !important;
        }

        .nav-btn-custom {
            display: block;
            text-align: center;
        }
    }
</style>