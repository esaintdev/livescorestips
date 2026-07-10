@props(['filters' => [], 'date' => null, 'routeName' => 'home'])

<div id="bm-category-modal" class="bm-modal-overlay" style="display: none;">
    <div class="bm-modal-backdrop"></div>
    <div class="bm-modal-panel">
        <div class="bm-modal-header">
            <h3 class="bm-modal-title">Filter by Category</h3>
            <button id="bm-modal-close" class="bm-modal-close">&times;</button>
        </div>
        <div class="bm-modal-body">
            @foreach($filters as $filter)
                @php
                    $isToday = $date == now()->format('Y-m-d');
                    if ($routeName === 'worldcup') {
                        $fRoute = $filter['slug'] ? route('worldcup.index', ['market' => $filter['slug'], 'date' => $date]) : route('worldcup.index', ['date' => $date]);
                    } elseif ($filter['slug']) {
                        $fRoute = $isToday ? route('category.show', ['categorySlug' => $filter['slug']]) : route('category.date', ['categorySlug' => $filter['slug'], 'date' => $date]);
                    } else {
                        $fRoute = $isToday ? route('home') : route('home.date', ['date' => $date]);
                    }
                @endphp
                <a href="{{ $fRoute }}" class="bm-modal-filter-link {{ $filter['active'] ? 'active' : '' }}">
                    <span class="bm-modal-filter-name">{{ $filter['name'] }}</span>
                    @if($filter['active'])
                        <i class="fa fa-check"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('bm-category-modal');
        const openBtn = document.getElementById('bm-cat-filter-btn');
        const closeBtn = document.getElementById('bm-modal-close');
        const backdrop = modal ? modal.querySelector('.bm-modal-backdrop') : null;

        function openModal() {
            if (modal) modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (modal) modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    });
</script>
@endpush
