{{-- Modern Sports Hero Section --}}
<div class="sports-hero position-relative overflow-hidden">
    {{-- Animated Background --}}
    <div class="hero-bg-gradient"></div>
    <div class="hero-pattern"></div>

    <div class="container position-relative" style="z-index: 10;">
        <div class="row align-items-center py-5">
            {{-- Hero Content --}}
            <div class="col-lg-7 col-md-12 text-center text-lg-start py-5">
                {{-- Badge --}}
                <div class="hero-badge mb-3">
                    <span class="badge-icon">⚡</span>
                    <span class="badge-text">95% WIN ACCURACY</span>
                </div>

                {{-- Main Heading --}}
                <h1 class="hero-title mb-4">
                    Expert Football
                    <span class="hero-highlight d-block">Predictions</span>
                    <span class="hero-subtitle d-block">Win Smarter, Bet Better</span>
                </h1>

                {{-- Description --}}
                <p class="hero-description mb-4">
                    Join thousands of winning bettors. Get daily expert predictions,
                    in-depth analysis, and premium tips across 100+ leagues worldwide.
                </p>

                {{-- CTA Buttons --}}
                <div class="hero-cta d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ $telegramLink }}" target="_blank" class="btn btn-hero-primary">
                        <span>Join Telegram Channel</span>
                        <i class="fa fa-telegram ms-2"></i>
                    </a>
                    <a href="{{ route('blog.index') }}" class="btn btn-hero-secondary">
                        <span>Latest Analysis</span>
                    </a>
                </div>

                {{-- Trust Indicators --}}
                <div class="hero-stats mt-5 d-flex flex-wrap gap-4">
                    <div class="stat-item">
                        <div class="stat-value">10K+</div>
                        <div class="stat-label">Daily Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">100+</div>
                        <div class="stat-label">Leagues</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">95%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
            </div>

            {{-- Hero Visual --}}
            <div class="col-lg-5 col-md-12 d-none d-lg-block">
                <div class="hero-visual">
                    {{-- Floating Stats Cards --}}
                    <div class="floating-card card-1">
                        <div class="card-icon">⚽</div>
                        <div class="card-content">
                            <div class="card-title">Today's Pick</div>
                            <div class="card-value">Man City vs Arsenal</div>
                            <div class="card-detail">Over 2.5 Goals</div>
                        </div>
                    </div>

                    <div class="floating-card card-2">
                        <div class="card-icon">📊</div>
                        <div class="card-content">
                            <div class="card-title">Win Rate</div>
                            <div class="card-value">95%</div>
                            <div class="card-detail">Last 30 Days</div>
                        </div>
                    </div>

                    <div class="floating-card card-3">
                        <div class="card-icon">🏆</div>
                        <div class="card-content">
                            <div class="card-title">Today's Tips</div>
                            <div class="card-value">12 Available</div>
                            <div class="card-detail">All Markets</div>
                        </div>
                    </div>

                    {{-- Central Football Image --}}
                    <div class="hero-football-center">
                        <div class="football-glow"></div>
                        <svg viewBox="0 0 200 200" class="football-svg">
                            <circle cx="100" cy="100" r="80" fill="url(#footballGradient)" />
                            <defs>
                                <linearGradient id="footballGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#0b5bdbff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#04145dff;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            {{-- Pentagon pattern --}}
                            <path d="M100,30 L125,55 L115,85 L85,85 L75,55 Z" fill="white" opacity="0.3" />
                            <path d="M125,55 L150,70 L145,100 L115,85 Z" fill="white" opacity="0.2" />
                            <path d="M85,85 L55,100 L50,70 L75,55 Z" fill="white" opacity="0.25" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hero Section Styles */

    .sports-hero {
        min-height: 600px;
        background: linear-gradient(135deg, #04145dff 0%, #0b5bdbff 100%);
        position: relative;
        padding: 2rem 0;
    }

    .hero-bg-gradient {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset('images/bgme.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0.2;
        mix-blend-mode: overlay;
        z-index: 1;
        pointer-events: none;
    }

    .hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
    }

    /* Hero Badge */

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        backdrop-filter: blur(10px);
        animation: fadeInUp 0.6s ease-out;
    }

    .hero-badge .badge-icon {
        font-size: 1.2rem;
    }

    .hero-badge .badge-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 1px;
    }

    /* Hero Title */

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
        animation: fadeInUp 0.6s ease-out 0.1s both;
    }

    .hero-highlight {
        background: linear-gradient(135deg, #14588B 0%, #ffffff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: clamp(1.2rem, 2.5vw, 1.8rem);
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 0.5rem;
    }

    /* Hero Description */

    .hero-description {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.7;
        max-width: 600px;
        animation: fadeInUp 0.6s ease-out 0.2s both;
    }

    /* CTA Buttons */

    .hero-cta {
        animation: fadeInUp 0.6s ease-out 0.3s both;
        position: relative;
        z-index: 20;
    }

    .btn-hero-primary {
        padding: 1rem 2rem;
        background: #ffffff;
        color: #092337;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 21;
        cursor: pointer;
    }

    .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        color: #092337;
    }

    .btn-hero-secondary {
        padding: 1rem 2rem;
        background: transparent;
        color: #ffffff;
        font-weight: 600;
        border-radius: 50px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        position: relative;
        z-index: 21;
        cursor: pointer;
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
        color: #ffffff;
    }

    /* Hero Stats */

    .hero-stats {
        animation: fadeInUp 0.6s ease-out 0.4s both;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.25rem;
    }

    /* Hero Visual */

    .hero-visual {
        position: relative;
        height: 500px;
    }

    .floating-card {
        position: absolute;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        padding: 1rem 1.25rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: float 3s ease-in-out infinite;
    }

    .floating-card.card-1 {
        top: 50px;
        left: -20px;
        animation-delay: 0s;
    }

    .floating-card.card-2 {
        top: 180px;
        right: 20px;
        animation-delay: 1s;
    }

    .floating-card.card-3 {
        bottom: 80px;
        left: 30px;
        animation-delay: 2s;
    }

    .floating-card .card-icon {
        font-size: 2rem;
    }

    .floating-card .card-title {
        font-size: 0.75rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .floating-card .card-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #092337;
    }

    .floating-card .card-detail {
        font-size: 0.875rem;
        color: #14588B;
    }

    .hero-football-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
    }

    .football-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(20, 88, 139, 0.4) 0%, transparent 70%);
        animation: pulse 2s ease-in-out infinite;
    }

    .football-svg {
        width: 100%;
        height: 100%;
        filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.3));
        animation: rotate 20s linear infinite;
    }

    /* Animations */

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 0.5;
            transform: translate(-50%, -50%) scale(1);
        }

        50% {
            opacity: 0.8;
            transform: translate(-50%, -50%) scale(1.1);
        }
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive */

    @media (max-width: 991px) {
        .sports-hero {
            min-height: 500px;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .hero-stats {
            justify-content: center;
        }
    }

    @media (max-width: 767px) {
        .hero-cta {
            flex-direction: column;
            width: 100%;
        }

        .btn-hero-primary,
        .btn-hero-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>