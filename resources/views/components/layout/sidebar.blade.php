<section class="bm-sidebar-inner">
    @php
        $topLeagues = $topLeagues ?? [];
        $countries = $countries ?? [];
    @endphp

    <div class="bm-sidebar-logo d-lg-none">
        <a href="{{ route('home') }}">
            <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-logo-dark"
                style="max-height:36px;">
            <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}"
                class="bm-logo-light" style="max-height:36px; display:none;">
        </a>
    </div>

    <div class="bm-sidebar-widget">
        <h3 class="bm-widget-title">Categories</h3>
        <div class="bm-widget-list">
            <a href="{{ route('category.show', 'over-2-5-goals') }}" class="bm-widget-item">
                <span>Over 2.5 Goals</span>
            </a>
            <a href="{{ route('category.show', 'over-1-5-goals') }}" class="bm-widget-item">
                <span>Over 1.5 Goals</span>
            </a>
            <a href="{{ route('category.show', 'btts-gg') }}" class="bm-widget-item">
                <span>BTTS/GG</span>
            </a>
            <a href="{{ route('category.show', 'double-chance') }}" class="bm-widget-item">
                <span>Double Chance</span>
            </a>
            <a href="{{ route('category.show', 'draws') }}" class="bm-widget-item">
                <span>Draws</span>
            </a>
            <a href="{{ route('category.show', 'home-wins') }}" class="bm-widget-item">
                <span>Home Wins</span>
            </a>
            <a href="{{ route('category.show', 'away-wins') }}" class="bm-widget-item">
                <span>Away Wins</span>
            </a>
            <a href="{{ route('category.show', 'correct-score') }}" class="bm-widget-item">
                <span>Correct Score</span>
            </a>
        </div>
    </div>

    <div class="bm-sidebar-inner">
        <div class="bm-sidebar-widget">
            <h3 class="bm-widget-title">Top Leagues</h3>
            <div class="bm-widget-list">
                @foreach($topLeagues as $league)
                    <a href="{{ route('league.show', ['id' => $league['id'], 'slug' => $league['slug'] ?? \Illuminate\Support\Str::slug($league['name'])]) }}"
                        class="bm-widget-item">
                        <img src="{{ $league['logo'] }}" alt="{{ $league['name'] }}" class="bm-widget-icon">
                        <span>{{ $league['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>



        <div class="bm-sidebar-widget">
            <h3 class="bm-widget-title">Countries</h3>
            <div class="bm-sidebar-search">
                <i class="fa fa-search"></i>
                <input type="text" id="countrySearch" placeholder="Country or league" class="bm-sidebar-search-input">
            </div>
            <div class="bm-countries-list custom-scrollbar">
                @foreach(array_slice($countries, 0, 30) as $country)
                    <div class="bm-country-item" data-country-name="{{ strtolower($country['name']) }}">
                        <a href="{{ route('country.show', ['name' => $country['name']]) }}"
                            class="bm-widget-item bm-country-link">
                            <img src="{{ $country['flag'] ?? asset('images/default-flag.svg') }}"
                                alt="{{ $country['name'] }}" class="bm-widget-flag">
                            <span>{{ $country['name'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<style>
    .bm-sidebar-logo {
        padding: 20px 48px 16px 20px;
        /* Right padding ensures it doesn't hit close button */
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 8px;
        text-align: left;
    }

    .bm-sidebar-logo a {
        display: inline-block;
    }

    .bm-sidebar-logo img {
        max-height: 36px;
        width: auto;
    }

    .light-theme .bm-sidebar-logo {
        border-bottom-color: rgba(0, 0, 0, 0.08);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('countrySearch');
        if (input) {
            input.addEventListener('input', function (e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.bm-country-item').forEach(function (item) {
                    var country = item.getAttribute('data-country-name');
                    item.style.display = country && country.includes(term) ? 'block' : 'none';
                });
            });
        }
    });
</script>