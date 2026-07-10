<div class="container tipsbar-card mt-3">
    <div class="p-3 rounded shadow-sm" style="background-color: #f8f9fa;">
        <div class="row mx-n1">
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('home') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->routeIs('home') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Free Tips
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'home-wins') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/home-wins') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Home Win
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'away-wins') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/away-wins') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Away Win
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'draws') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/draws') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Draw
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'correct-score') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/correct-score') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Correct Score
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'btts-gg') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/btts-gg') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    BTTS
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'double-chance') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/double-chance') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Double Chance
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'over-1-5-goals') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/over-1-5-goals') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Over 1.5
                </a>
            </div>
            <div class="col-4 col-md-3 px-1 mb-2">
                <a href="{{ route('category.show', 'over-2-5-goals') }}"
                    class="btn btn-sm w-100 btn-pill {{ request()->is('category/over-2-5-goals') ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="border-radius: 50px; padding: 0.375rem 0.5rem; font-size: 0.8rem;">
                    Over 2.5
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-success {
        background-color: #092337;
        border-color: #092337;
    }

    .btn-outline-secondary {
        border-color: #e0e0e0;
        color: #555;
    }

    .btn-outline-secondary:hover {
        background-color: #e9ecef;
        color: #333;
        border-color: #d0d0d0;
    }
</style>