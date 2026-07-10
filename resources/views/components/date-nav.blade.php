@props(['date', 'dateRange', 'isLive', 'liveCount', 'route', 'routeParams' => []])

<div class="bm-date-nav shadow-sm mb-4">
    <a href="{{ route(in_array($route, ['home.date', 'category.date', 'predictions.all.date']) ? str_replace('.date', '', $route) : $route, array_merge($routeParams, ['live' => 1])) }}" 
       class="bm-date-live {{ $isLive ? 'active' : '' }}">
        Live @if($liveCount > 0) <span class="badge">{{ $liveCount }}</span> @endif
    </a>
    <div class="bm-date-divider"></div>
    
    <div class="bm-date-items-wrapper">
        @foreach($dateRange as $item)
            @php 
                $isSelected = ($date == $item['date'] && !$isLive);
                $isToday = $item['date'] == now()->format('Y-m-d');
                $currentRoute = $route;
                $params = $routeParams;
                
                if (in_array($route, ['home', 'home.date'])) {
                    if ($isToday) {
                        $currentRoute = 'home';
                        unset($params['date']);
                    } else {
                        $currentRoute = 'home.date';
                        $params['date'] = $item['date'];
                    }
                } elseif (in_array($route, ['category.show', 'category.date'])) {
                    if ($isToday) {
                        $currentRoute = 'category.show';
                        unset($params['date']);
                    } else {
                        $currentRoute = 'category.date';
                        $params['date'] = $item['date'];
                    }
                } elseif (in_array($route, ['predictions.all', 'predictions.all.date'])) {
                    if ($isToday) {
                        $currentRoute = 'predictions.all';
                        unset($params['date']);
                    } else {
                        $currentRoute = 'predictions.all.date';
                        $params['date'] = $item['date'];
                    }
                } elseif (in_array($route, ['worldcup.index', 'worldcup.date'])) {
                    if ($isToday) {
                        $currentRoute = 'worldcup.index';
                        unset($params['date']);
                    } else {
                        $currentRoute = 'worldcup.date';
                        $params['date'] = $item['date'];
                    }
                } else {
                    $params['date'] = $item['date'];
                }
            @endphp
            <a href="{{ route($currentRoute, $params) }}"
                class="bm-date-item {{ $isSelected ? 'active' : '' }}">
                <span class="bm-date-day">{{ $item['day'] }}</span>
                <span class="bm-date-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="bm-date-divider"></div>
    <div class="bm-calendar-wrapper">
        <button id="bm-calendar-picker" class="bm-date-item" title="Select custom date">
            <i class="fa fa-calendar-o" style="font-size: 1.2rem;"></i>
            <span class="bm-date-label">CAL</span>
        </button>
    </div>
    <div class="bm-date-divider"></div>
    <div class="bm-filter-wrapper">
        <button id="bm-cat-filter-btn" class="bm-date-item" title="Filter by category">
            <i class="fa fa-filter" style="font-size: 1.2rem;"></i>
            <span class="bm-date-label">FILTER</span>
        </button>
    </div>
</div>

<style>
    .bm-date-nav {
        display: flex;
        align-items: center;
        background-color: #1a1a1a;
        border-radius: 12px;
        padding: 5px;
        overflow-x: auto;
    }
    .bm-date-items-wrapper {
        display: flex;
        overflow-x: auto;
        flex-grow: 1;
        scrollbar-width: none; /* Firefox */
    }
    .bm-date-items-wrapper::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    .bm-calendar-wrapper {
        padding: 0 10px;
    }
    #bm-calendar-picker {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 8px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    #bm-calendar-picker:hover {
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
    }
    
    /* Flatpickr Custom Styling */
    .flatpickr-calendar.dark {
        background: #1e1e1e;
        border: 1px solid #333;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .flatpickr-calendar.dark .flatpickr-day {
        color: #fff;
    }
    .flatpickr-calendar.dark .flatpickr-day.selected {
        background: #fff !important;
        color: #000 !important;
        border-color: #fff !important;
    }
    .flatpickr-calendar.dark .flatpickr-current-month, 
    .flatpickr-calendar.dark .flatpickr-weekday {
        color: #fff;
        fill: #fff;
    }
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.flatpickr-disabled:hover {
        visibility: hidden;
        pointer-events: none;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#bm-calendar-picker", {
                defaultDate: "{{ $date }}",
                dateFormat: "Y-m-d",
                disableMobile: "true",
                theme: "dark",
                minDate: "{{ now()->subDays(7)->format('Y-m-d') }}", // Allow past 7 days for results
                maxDate: "{{ now()->addDays(14)->format('Y-m-d') }}", // Allow future 14 days (today + 7 days of predictions)
                onChange: function(selectedDates, dateStr, instance) {
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
    });
</script>
@endpush
