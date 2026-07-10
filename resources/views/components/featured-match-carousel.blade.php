@props(['predictions'])

{{-- Featured Matches Section --}}
<div class="featured-matches-section">
    <div class="container">
        <div class="section-headers text-center mb-4">
            <h2 class="section-title">Featured Tips of the Day</h2>
        </div>

        @if($predictions && $predictions->count() > 0)
            <div class="featured-carousel-wrapper">
                <div class="carousel-track-container">
                    <div class="carousel-track" id="featuredCarousel">
                        @foreach($predictions->take(5) as $prediction)
                            <div class="featured-slide">
                                <div class="fixture-card">
                                    {{-- Header: League Info --}}
                                    <a href="{{ route('fixture.show', [$prediction->slug ?? $prediction->fixture_id, $prediction->match_date->format('Y-m-d')]) }}"
                                        class="fixture-header">
                                        <div class="league-group">
                                            <img src="{{ $prediction->league_logo ?? asset('images/default-flag.svg') }}"
                                                alt="{{ $prediction->league_name }}" class="league-logo">
                                            <div class="league-text">
                                                <span class="league-name">{{ $prediction->league_name }}</span>
                                                <span
                                                    class="country-name">{{ $prediction->league_country ?? 'International' }}</span>
                                            </div>
                                        </div>
                                        <div class="header-arrow">
                                            <i class="fa fa-chevron-right"></i>
                                        </div>
                                    </a>

                                    {{-- Body: Time, Teams, Action --}}
                                    <div class="fixture-body">
                                        {{-- Time Column --}}
                                        <div class="time-column">
                                            <span
                                                class="match-time">{{ \Carbon\Carbon::parse($prediction->match_time)->format('H:i') }}</span>
                                        </div>

                                        {{-- Teams Column --}}
                                        <div class="teams-column">
                                            <div class="team-row">
                                                <img src="{{ $prediction->home_team_logo }}"
                                                    class="team-logo" alt="{{ $prediction->home_team }}">
                                                <span class="team-name">{{ $prediction->home_team }}</span>
                                            </div>
                                            <div class="team-row">
                                                <img src="{{ $prediction->away_team_logo }}"
                                                    class="team-logo" alt="{{ $prediction->away_team }}">
                                                <span class="team-name">{{ $prediction->away_team }}</span>
                                            </div>
                                        </div>

                                        {{-- Action/Tip Column --}}
                                        <div class="action-column">
                                            <div class="tip-display">
                                                <span class="tip-badge">{{ $prediction->tip }}</span>
                                                <span class="odds-display">{{ $prediction->odds }}</span>
                                            </div>
                                            <a href="{{ route('fixture.show', [$prediction->slug ?? $prediction->fixture_id, $prediction->match_date->format('Y-m-d')]) }}"
                                                class="predict-btn">
                                                <i class="fa fa-plus"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="carousel-controls">
                    <button class="nav-btn prev" id="carouselPrev"><i class="fa fa-chevron-left"></i></button>
                    <div class="dots" id="carouselDots"></div>
                    <button class="nav-btn next" id="carouselNext"><i class="fa fa-chevron-right"></i></button>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Reset & Base */
    .featured-matches-section {
        background: #f4f6f8;
        padding: 40px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a2a3a;
        margin-bottom: 25px;
    }

    /* Carousel Structure */
    .featured-carousel-wrapper {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        padding: 0 20px;
    }

    .carousel-track-container {
        overflow: hidden;
        margin-bottom: 20px;
    }

    .carousel-track {
        display: flex;
        transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .featured-slide {
        min-width: 100%;
        padding: 5px;
        /* Space for shadow */
    }

    /* Card Styling - Exact Replica */
    .fixture-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e1e4e8;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    /* Header */
    .fixture-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        border-bottom: 1px solid #f0f2f5;
        background: #fff;
        text-decoration: none !important;
        transition: background 0.2s;
    }

    .fixture-header:hover {
        background: #f8fafc;
    }

    .league-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .league-logo {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }

    .league-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .league-name {
        font-size: 14px;
        font-weight: 700;
        color: #1a2a3a;
    }

    .country-name {
        font-size: 12px;
        color: #6b7c93;
    }

    .header-arrow {
        color: #cbd5e0;
        font-size: 12px;
    }

    /* Body */
    .fixture-body {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        min-height: 80px;
    }

    /* Time Column */
    .time-column {
        width: 60px;
        flex-shrink: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1a2a3a;
        padding-top: 4px;
        /* Optical alignment */
    }

    /* Teams Column */
    .teams-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .team-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .team-logo {
        width: 20px;
        height: 20px;
        object-fit: contain;
    }

    .team-name {
        font-size: 15px;
        color: #2d3748;
        font-weight: 500;
    }

    /* Action Column */
    .action-column {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .tip-display {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
    }

    .tip-badge {
        font-size: 12px;
        font-weight: 700;
        color: #2b6cb0;
        background: #ebf8ff;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .odds-display {
        font-size: 12px;
        color: #718096;
    }

    .predict-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #ebf8ff;
        /* Light blue bg like image */
        color: #007bff;
        /* Blue text */
        border: 1px solid #cce5ff;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .predict-btn:hover {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    /* Controls */
    .carousel-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-top: 15px;
    }

    .nav-btn {
        background: #fff;
        border: 1px solid #e1e4e8;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #4a5568;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .nav-btn:hover {
        background: #edf2f7;
        color: #1a2a3a;
    }

    .dots {
        display: flex;
        gap: 6px;
    }

    .dot {
        width: 8px;
        height: 8px;
        background: #cbd5e0;
        border-radius: 50%;
        cursor: pointer;
        transition: background 0.2s;
    }

    .dot.active {
        background: #007bff;
    }

    /* Mobile */
    @media (max-width: 576px) {
        .fixture-body {
            padding: 15px;
        }

        .time-column {
            width: 50px;
            font-size: 13px;
        }

        .team-name {
            font-size: 14px;
        }

        .action-column {
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
            margin-left: 10px;
        }

        .predict-btn {
            padding: 4px 10px;
            font-size: 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.getElementById('featuredCarousel');
        const prevBtn = document.getElementById('carouselPrev');
        const nextBtn = document.getElementById('carouselNext');
        const dotsContainer = document.getElementById('carouselDots');

        if (!track) return;

        const slides = track.querySelectorAll('.featured-slide');
        let currentIndex = 0;
        const totalSlides = slides.length;

        // Create dots
        slides.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.className = `dot ${i === 0 ? 'active' : ''}`;
            dot.onclick = () => goToSlide(i);
            dotsContainer.appendChild(dot);
        });

        const dots = dotsContainer.querySelectorAll('.dot');

        function updateCarousel() {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
        }

        function goToSlide(index) {
            currentIndex = index;
            updateCarousel();
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateCarousel();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        if (nextBtn) nextBtn.onclick = nextSlide;
        if (prevBtn) prevBtn.onclick = prevSlide;

        // Auto play
        let interval = setInterval(nextSlide, 5000);
        track.onmouseenter = () => clearInterval(interval);
        track.onmouseleave = () => interval = setInterval(nextSlide, 5000);
    });
</script>