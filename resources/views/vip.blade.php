<!DOCTYPE html>
<html lang="en">

<x-layout.head title="VIP Plans - {{ config('app.name', 'LiveScores Tips') }}"
    description="Unlock premium betting tips with our VIP subscription plans. Get exclusive access to high-odds predictions and expert analysis."
    keywords="VIP betting tips, premium predictions, betting subscription" />

{{-- Import Archivo Black Font --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">

<body class="antialiased">
    <x-preloader />

    <x-layout.navbar />

    {{-- Hero Section --}}
    <section class="vip-hero">
        <div class="container">
            <div class="text-center py-5">
                <h1 class="vip-title">VIP Subscription Plans</h1>
                <p class="vip-subtitle">Get exclusive access to our premium predictions and maximize your winning
                    potential</p>
            </div>
        </div>
    </section>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    @endif

    {{-- Pricing Cards --}}
    <section class="vip-pricing py-5">
        <div class="container">
            @forelse($plans as $packageName => $packagePlans)
                <div class="package-section mb-5">
                    <h2 class="package-title text-center mb-4">{{ $packageName }}</h2>
                    <div class="row justify-content-center">
                        @foreach($packagePlans as $plan)
                            <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                                <div class="pricing-card">
                                    <div class="pricing-header">
                                        <h3 class="duration-title">{{ strtoupper($plan->duration) }}</h3>
                                        @if($plan->badge_text)
                                            <span class="plan-badge">{{ $plan->badge_text }}</span>
                                        @endif
                                    </div>
                                    <div class="pricing-body">
                                        <div class="price">
                                            <span class="currency">{{ $plan->currency }}</span>
                                            <span class="amount">{{ number_format((float) $plan->price, 0) }}</span>
                                        </div>
                                        @if($plan->features && count($plan->features) > 0)
                                            <ul class="features-list">
                                                @foreach($plan->features as $feature)
                                                    <li><i class="fa fa-check"></i> {{ $feature }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="pricing-footer">
                                        <button type="button" class="btn-subscribe" data-toggle="modal"
                                            data-target="#subscribeModal" data-plan-id="{{ $plan->id }}"
                                            data-plan-name="{{ $plan->name }} - {{ $plan->duration }}"
                                            data-plan-price="{{ $plan->formatted_price }}">
                                            Subscribe to Plan <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <h3 class="text-muted">No VIP plans available at the moment.</h3>
                    <p class="text-muted">Please check back later for our premium offerings.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Features Section --}}
    <section class="vip-features py-5" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="text-center mb-5" style="color: #04145d; font-weight: 700;">Why Choose Our VIP Plans?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fa fa-trophy"></i>
                        </div>
                        <h4>High Win Rate</h4>
                        <p>Our VIP tips come with an exceptional accuracy rate, carefully analyzed by our expert team.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <h4>Daily Tips</h4>
                        <p>Receive exclusive predictions every day during your subscription period.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fa fa-headphones"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Our dedicated support team is available around the clock to assist you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Subscribe Modal --}}
    <div class="modal fade" id="subscribeModal" tabindex="-1" role="dialog" aria-labelledby="subscribeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #04145d 0%, #0c7ad5 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title text-white" id="subscribeModalLabel">Subscribe to VIP Plan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="subscribeForm" method="POST" action="">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <h4 id="selectedPlanName" style="color: #04145d; font-weight: 600;"></h4>
                            <p class="text-muted" id="selectedPlanPrice"></p>
                        </div>
                        <div class="form-group">
                            <label for="subscriberName">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subscriberName" name="name" required
                                style="border-radius: 8px; padding: 12px;">
                        </div>
                        <div class="form-group">
                            <label for="subscriberEmail">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="subscriberEmail" name="email" required
                                style="border-radius: 8px; padding: 12px;">
                        </div>
                        <div class="form-group">
                            <label for="subscriberPhone">Phone Number (WhatsApp preferred)</label>
                            <input type="text" class="form-control" id="subscriberPhone" name="phone"
                                style="border-radius: 8px; padding: 12px;">
                        </div>
                        <p class="text-muted small mt-3">
                            <i class="fa fa-info-circle"></i> After submitting, our team will contact you with payment
                            details.
                        </p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 25px;">Cancel</button>
                        <button type="submit" class="btn"
                            style="background: linear-gradient(135deg, #d35244 0%, #b8322a 100%); color: white; border-radius: 8px; padding: 10px 25px;">
                            Submit Request <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <script>
        $('#subscribeModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var planId = button.data('plan-id');
            var planName = button.data('plan-name');
            var planPrice = button.data('plan-price');

            var modal = $(this);
            modal.find('#selectedPlanName').text(planName);
            modal.find('#selectedPlanPrice').text('Price: ' + planPrice);
            modal.find('#subscribeForm').attr('action', '/vip/' + planId + '/subscribe');
        });
    </script>

    <x-layout.footer />

    <style>
        .vip-hero {
            background: linear-gradient(135deg, #04145d 0%, #0c7ad5 100%);
            padding: 40px 0;
        }

        .vip-title {
            font-family: 'Archivo Black', sans-serif;
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .vip-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        .package-title {
            font-family: 'Archivo Black', sans-serif;
            color: #04145d;
            font-weight: 400;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 15px;
            text-transform: uppercase;
        }

        .package-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #d35244 0%, #04145d 100%);
        }

        .pricing-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .pricing-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .duration-title {
            font-family: 'Archivo Black', sans-serif;
            color: #0a5c36;
            font-size: 1.2rem;
            font-weight: 400;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .plan-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0a5c36 0%, #1a7f4b 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pricing-body {
            padding: 25px 20px;
            text-align: center;
            flex-grow: 1;
        }

        .price {
            margin-bottom: 25px;
        }

        .price .currency {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            vertical-align: top;
        }

        .price .amount {
            font-size: 3rem;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .features-list li {
            padding: 8px 0;
            color: #555;
            font-size: 0.9rem;
        }

        .features-list li i {
            color: #0a5c36;
            margin-right: 10px;
        }

        .pricing-footer {
            padding: 20px;
            margin-top: auto;
        }

        .btn-subscribe {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #d35244 0%, #b8322a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-subscribe:hover {
            background: linear-gradient(135deg, #b8322a 0%, #8f251f 100%);
            transform: translateY(-2px);
        }

        .btn-subscribe i {
            margin-left: 8px;
        }

        /* Features Section */
        .feature-box {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #04145d 0%, #0c7ad5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .feature-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .feature-box h4 {
            color: #04145d;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature-box p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .vip-title {
                font-size: 1.8rem;
            }

            .pricing-card {
                margin-bottom: 20px;
            }
        }
    </style>
</body>

</html>