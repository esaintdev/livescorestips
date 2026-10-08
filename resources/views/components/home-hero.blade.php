{{-- Homepage Hero — single H1, SEO-clean, honest dynamic stats --}}
@props([
    'h1' => null,
    'description' => null,
    'headerIntro' => null,
    'tipsCount' => 0,
    'marketsCount' => 0,
    'leaguesCount' => 0,
])

<div class="bm-home-hero" id="bm-hero">
    <div class="bm-home-hero-bg" aria-hidden="true"></div>
    <div class="bm-home-hero-inner">
        <div class="bm-home-hero-copy">
            <div class="bm-hero-badge">
                <span class="bm-hero-badge-dot"></span>
                Free Daily Football Predictions
            </div>

            <h1 class="bm-home-hero-title">{{ $h1 ?? config('app.name', 'LiveScores Tips') }}</h1>

            @if($description)
                <p class="bm-home-hero-desc">{{ $description }}</p>
            @endif

            @if($headerIntro)
                <div class="bm-home-hero-intro">{!! $headerIntro !!}</div>
            @endif

            <div class="bm-home-hero-cta">
                <a href="#bm-today-tips" class="bm-hero-btn bm-hero-btn-primary">
                    <span>View Today's Tips</span>
                    <i class="fa fa-arrow-down ms-2"></i>
                </a>
                <a href="{{ $telegramLink ?? 'https://t.me/sportvillaprediction' }}" target="_blank" rel="noopener" class="bm-hero-btn bm-hero-btn-secondary">
                    <i class="fa fa-telegram me-2"></i>
                    <span>Join Telegram</span>
                </a>
            </div>
        </div>

        <div class="bm-home-hero-stats">
            <div class="bm-hero-stat">
                <div class="bm-hero-stat-value">{{ $tipsCount }}</div>
                <div class="bm-hero-stat-label">Today's Tips</div>
            </div>
            <div class="bm-hero-stat">
                <div class="bm-hero-stat-value">{{ $marketsCount }}</div>
                <div class="bm-hero-stat-label">Betting Markets</div>
            </div>
            <div class="bm-hero-stat">
                <div class="bm-hero-stat-value">{{ $leaguesCount }}+</div>
                <div class="bm-hero-stat-label">Leagues</div>
            </div>
        </div>
    </div>
</div>

<style>
    .bm-home-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        padding: 48px 40px;
        background: linear-gradient(120deg, #04145d 0%, #0b5bdb 100%);
        margin-bottom: 28px;
        isolation: isolate;
    }

    .bm-home-hero-bg {
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image: url('{{ asset('images/bgme.jpg') }}');
        background-size: cover;
        background-position: center;
        opacity: 0.18;
    }

    .bm-home-hero-bg::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.12) 0%, transparent 50%);
    }

    .bm-home-hero-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 32px;
    }

    .bm-home-hero-copy {
        flex: 1 1 460px;
        max-width: 620px;
    }

    .bm-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 100px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .bm-hero-badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ff6b00;
        box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.6);
        animation: bm-hero-pulse 2s infinite;
    }

    @keyframes bm-hero-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.55); }
        50% { box-shadow: 0 0 0 6px rgba(255, 107, 0, 0); }
    }

    .bm-home-hero-title {
        font-size: clamp(1.9rem, 4.5vw, 3.1rem);
        font-weight: 800;
        line-height: 1.15;
        color: #fff;
        margin: 0 0 16px;
    }

    .bm-home-hero-desc {
        font-size: 1.05rem;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.85);
        margin: 0 0 20px;
        max-width: 560px;
    }

    .bm-home-hero-intro {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .bm-home-hero-cta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .bm-hero-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 22px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .bm-hero-btn:hover {
        text-decoration: none;
        transform: translateY(-2px);
    }

    .bm-hero-btn-primary {
        background: #ff6b00;
        color: #fff;
        box-shadow: 0 8px 20px rgba(255, 107, 0, 0.35);
    }

    .bm-hero-btn-primary:hover {
        background: #ff7a1a;
        color: #fff;
        box-shadow: 0 10px 26px rgba(255, 107, 0, 0.45);
    }

    .bm-hero-btn-secondary {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.28);
    }

    .bm-hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .bm-home-hero-stats {
        flex: 1 1 220px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 180px;
    }

    .bm-hero-stat {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 14px;
        padding: 16px 20px;
    }

    .bm-hero-stat-value {
        font-size: 1.7rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .bm-hero-stat-label {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 6px;
        letter-spacing: 0.3px;
    }

    @media (max-width: 767px) {
        .bm-home-hero {
            padding: 32px 22px;
            border-radius: 16px;
        }
        .bm-home-hero-stats {
            flex-direction: row;
            flex-wrap: wrap;
            width: 100%;
        }
        .bm-hero-stat {
            flex: 1 1 30%;
            text-align: center;
            padding: 14px 10px;
        }
        .bm-hero-btn {
            width: 100%;
        }
    }
</style>
