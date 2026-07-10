{{-- Quick Stats Dashboard --}}
<div class="quick-stats-section py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-2">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="18" fill="#092337" opacity="0.1" />
                            <path d="M20 10L22.5 17.5H30L24 22L26.5 30L20 25L13.5 30L16 22L10 17.5H17.5L20 10Z"
                                fill="#092337" />
                        </svg>
                    </div>
                    <div class="stat-number">95%</div>
                    <div class="stat-text">Win Accuracy</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-2">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="18" fill="#14588B" opacity="0.1" />
                            <path
                                d="M20 8C13.4 8 8 13.4 8 20C8 26.6 13.4 32 20 32C26.6 32 32 26.6 32 20C32 13.4 26.6 8 20 8ZM20 13C21.1 13 22 13.9 22 15C22 16.1 21.1 17 20 17C18.9 17 18 16.1 18 15C18 13.9 18.9 13 20 13ZM24 27H16V25H18V21H16V19H22V25H24V27Z"
                                fill="#14588B" />
                        </svg>
                    </div>
                    <div class="stat-number">{{ \App\Models\Prediction::whereDate('match_date', today())->count() }}+
                    </div>
                    <div class="stat-text">Today's Tips</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-2">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="18" fill="#092337" opacity="0.1" />
                            <path
                                d="M20 8C13.4 8 8 13.4 8 20C8 26.6 13.4 32 20 32C26.6 32 32 26.6 32 20C32 13.4 26.6 8 20 8ZM25.5 16.5L18.5 23.5L14.5 19.5L16 18L18.5 20.5L24 15L25.5 16.5Z"
                                fill="#092337" />
                        </svg>
                    </div>
                    <div class="stat-number">100+</div>
                    <div class="stat-text">Leagues Covered</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-2">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="18" fill="#14588B" opacity="0.1" />
                            <path
                                d="M20 8C13.4 8 8 13.4 8 20C8 26.6 13.4 32 20 32C26.6 32 32 26.6 32 20C32 13.4 26.6 8 20 8ZM14 18C14 15.8 15.8 14 18 14C20.2 14 22 15.8 22 18C22 20.2 20.2 22 18 22C15.8 22 14 20.2 14 18ZM26 26H14V24C14 22 18 20.9 20 20.9C22 20.9 26 22 26 24V26Z"
                                fill="#14588B" />
                        </svg>
                    </div>
                    <div class="stat-number">10K+</div>
                    <div class="stat-text">Active Users</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .quick-stats-section {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .stat-card {
        padding: 1rem;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #092337;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-text {
        font-size: 0.875rem;
        color: #666;
        font-weight: 500;
    }

    @media (max-width: 767px) {
        .stat-number {
            font-size: 1.5rem;
        }

        .stat-text {
            font-size: 0.75rem;
        }

        .stat-icon svg {
            width: 30px;
            height: 30px;
        }
    }
</style>