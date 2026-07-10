@props([
    'categories' => [
        ['name' => 'Double Chance', 'slug' => 'double-chance', 'icon' => 'fa-shield'],
        ['name' => 'Over 1.5 Goals', 'slug' => 'over-1-5-goals', 'icon' => 'fa-futbol-o'],
        ['name' => 'Over 2.5 Goals', 'slug' => 'over-2-5-goals', 'icon' => 'fa-futbol-o'],
        ['name' => 'BTTS', 'slug' => 'btts-gg', 'icon' => 'fa-fire'],
        ['name' => 'Draw', 'slug' => 'draws', 'icon' => 'fa-handshake-o'],
        ['name' => 'Home Win', 'slug' => 'home-wins', 'icon' => 'fa-flag'],
        ['name' => 'Away Win', 'slug' => 'away-wins', 'icon' => 'fa-globe'],
        ['name' => 'Correct Score', 'slug' => 'correct-score', 'icon' => 'fa-list-ol'],

    ]
])

{{-- Category Tabs Section --}}
<div class="category-section py-5">
    <div class="container">
        <h3 class="category-section-title mb-4 text-center">Browse by Category</h3>
        <div class="row g-3 justify-content-center">
            @foreach($categories as $index => $category)
                <div class="col-lg-2 col-md-4 col-6 category-wrapper {{ $index >= 4 ? 'd-none d-md-block mobile-hidden-category' : '' }}">
                    <a href="{{ route('category.show', $category['slug']) }}"
                       class="category-tab d-flex flex-column align-items-center justify-content-center h-100 {{ request()->route('categorySlug') === $category['slug'] ? 'active' : 'inactive' }}">
                        <i class="fa {{ $category['icon'] }} category-icon mb-2"></i>
                        <span class="category-name">{{ $category['name'] }}</span>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- View More Button (Mobile Only) --}}
        <div class="text-center mt-3 d-md-none">
            <button id="view-more-categories" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                View More <i class="fa fa-chevron-down ml-1"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Category Section */
    .category-section {
        background: #f8f9fa;
    }

    .category-section-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #04145dff;
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .category-section-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 4px;
        background: #0b5bdbff;
        margin: 10px auto 0;
        border-radius: 2px;
    }

    /* Category Tab Styling */
    .category-tab {
        padding: 1.5rem 1rem;
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        color: #555;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        height: 100%;
    }

    .category-icon {
        font-size: 1.5rem;
        color: #0b5bdbff;
        transition: all 0.3s ease;
    }

    .category-name {
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
        line-height: 1.2;
    }

    /* Active State */
    .category-tab.active {
        background: linear-gradient(135deg, #04145dff 0%, #0b5bdbff 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 10px 20px rgba(11, 91, 219, 0.3);
        transform: translateY(-5px);
    }
    
    .category-tab.active .category-icon {
        color: white;
    }

    /* Hover State (Inactive) */
    .category-tab.inactive:hover {
        background: white;
        border-color: #0b5bdbff;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .category-tab.inactive:hover .category-icon {
        transform: scale(1.2);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .category-tab {
            padding: 0.75rem 0.25rem;
            min-height: 80px;
        }
        
        .category-icon {
            font-size: 1.25rem;
            margin-bottom: 0.25rem !important;
        }
        
        .category-name {
            font-size: 0.75rem;
            line-height: 1.1;
        }

        .category-section-title {
            font-size: 1.25rem;
        }

        .category-wrapper {
            padding-left: 5px;
            padding-right: 5px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewMoreBtn = document.getElementById('view-more-categories');
        const hiddenCategories = document.querySelectorAll('.mobile-hidden-category');
        let isExpanded = false;

        if (viewMoreBtn) {
            viewMoreBtn.addEventListener('click', function() {
                isExpanded = !isExpanded;
                
                hiddenCategories.forEach(function(el) {
                    if (isExpanded) {
                        el.classList.remove('d-none');
                        el.classList.add('d-block');
                        // Add animation class if needed
                    } else {
                        el.classList.add('d-none');
                        el.classList.remove('d-block');
                    }
                });

                if (isExpanded) {
                    viewMoreBtn.innerHTML = 'View Less <i class="fa fa-chevron-up ml-1"></i>';
                } else {
                    viewMoreBtn.innerHTML = 'View More <i class="fa fa-chevron-down ml-1"></i>';
                }
            });
        }
    });
</script>
