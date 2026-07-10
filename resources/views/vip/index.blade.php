@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'VIP Subscription Plans - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Join ' . config('app.name') . ' VIP for exclusive high-accuracy football predictions and expert tips.')
@section('keywords', $metadata['page_keywords'] ?? 'VIP predictions, betting tips, premium tips')

@push('styles')
    <style>
        .bm-vip-grid {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            margin-left: -15px !important;
            margin-right: -15px !important;
        }

        .bm-vip-column {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            padding-left: 15px !important;
            padding-right: 15px !important;
            margin-bottom: 3rem !important;
        }

        @media (max-width: 991px) {
            .bm-vip-column {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        /* Country Selector */
        .bm-country-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 24px 28px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }
        .bm-country-select-inner {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            height: 56px;
            padding: 0 16px;
            position: relative;
            transition: all 0.2s;
        }
        .bm-country-select-inner:focus-within {
            border-color: #ff6b00;
            box-shadow: 0 0 0 3px rgba(255,107,0,0.15);
        }
        .bm-country-icon {
            font-size: 1.2rem;
            color: #ff6b00;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .bm-country-select {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            height: 100%;
            width: 100%;
            padding: 0;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            outline: none;
        }
        .bm-country-select option {
            background: #1a1a1a;
            color: #fff;
            padding: 8px;
        }
        .bm-country-arrow {
            color: rgba(255,255,255,0.3);
            font-size: 0.8rem;
            margin-left: 10px;
            flex-shrink: 0;
            pointer-events: none;
            transition: transform 0.2s;
        }
        .bm-country-select-inner:focus-within .bm-country-arrow {
            transform: rotate(180deg);
        }
        .bm-country-btn {
            border-radius: 14px;
            height: 56px;
            font-size: 1.1rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(255,107,0,0.35);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            width: 100%;
        }
        .bm-country-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,107,0,0.5);
        }

        /* Light theme overrides */
        .light-theme .bm-country-card {
            background: #ffffff;
            border-color: #e0e0e0;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
        }
        .light-theme .bm-country-select-inner {
            background: #f5f5f5;
            border-color: #d0d0d0;
        }
        .light-theme .bm-country-select-inner:focus-within {
            border-color: #ff6b00;
            box-shadow: 0 0 0 3px rgba(255,107,0,0.15);
        }
        .light-theme .bm-country-select {
            color: #222;
        }
        .light-theme .bm-country-select option {
            background: #fff;
            color: #222;
        }
        .light-theme .bm-country-arrow {
            color: rgba(0,0,0,0.25);
        }

        @media (max-width: 768px) {
            .bm-country-card {
                padding: 16px;
            }
            .bm-country-btn {
                height: 50px;
                font-size: 1rem;
            }
            .bm-country-select-inner {
                height: 50px;
            }
        }

        /* FAQ accordion */
        .bm-faq-list {
            max-width: 700px;
            margin: 0 auto;
        }
        .bm-faq-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .bm-faq-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            cursor: pointer;
            user-select: none;
        }
        .bm-faq-header:hover {
            background: rgba(255,255,255,0.03);
        }
        .bm-faq-question {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }
        .bm-faq-icon {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.3);
            transition: transform 0.25s;
            flex-shrink: 0;
            margin-left: 12px;
        }
        .bm-faq-item.open .bm-faq-icon {
            transform: rotate(180deg);
        }
        .bm-faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            padding: 0 20px;
        }
        .bm-faq-item.open .bm-faq-body {
            max-height: 200px;
            padding: 0 20px 16px;
        }
        .bm-faq-answer {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
            margin: 0;
            line-height: 1.6;
        }
        .light-theme .bm-faq-item {
            background: #f8f8f8;
            border-color: #e0e0e0;
        }
        .light-theme .bm-faq-header:hover {
            background: rgba(0,0,0,0.02);
        }
        .light-theme .bm-faq-question {
            color: #222;
        }
        .light-theme .bm-faq-icon {
            color: rgba(0,0,0,0.25);
        }
        .light-theme .bm-faq-answer {
            color: rgba(0,0,0,0.55);
        }

        /* Light theme overrides for VIP page */
        .light-theme .bm-vip-page-title {
            color: #222 !important;
        }
        .light-theme .bm-vip-category-title {
            color: #222 !important;
        }
        .light-theme .bm-vip-price {
            color: #222 !important;
        }
        .light-theme .bm-vip-feature-highlight {
            color: #222 !important;
        }
        .light-theme .bm-vip-card {
            background: #ffffff !important;
        }
    </style>
@endpush

@section('content')



    <div class="text-center mb-5">
        <h2 class="bm-vip-page-title" style="font-weight: 800; color: #fff; font-size: 2.2rem;">Choose Your <span class="text-warning">Perfect
                Plan</span></h2>
        <p class="text-muted">Select your country to view the plans that fits your needs and start winning.</p>
    </div>

    {{-- Country Selector Section --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 col-lg-8">
            <div class="bm-country-card">
                <form action="{{ route('vip.index') }}" method="GET" class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <div class="bm-country-select-inner">
                            <i class="fa fa-globe bm-country-icon"></i>
                            <select name="country" class="bm-country-select" id="bm-country-select">
                                <option value="">Select Your Country</option>
                                @foreach($availableCountries as $country)
                                    <option value="{{ $country }}" {{ $selectedCountry === $country ? 'selected' : '' }}>
                                        {{ $country }}</option>
                                @endforeach
                            </select>
                            <i class="fa fa-chevron-down bm-country-arrow"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="bm-country-btn">
                            <i class="fa fa-search"></i> View Plans
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pricing Cards --}}
    <div class="bm-vip-grid">
        @forelse($plans as $packageName => $packagePlans)
            <div class="bm-vip-column">
                <div class="text-center mb-4">
                    <h2 class="d-inline-block pb-2 px-3 bm-vip-category-title"
                        style="font-size: 1.25rem; text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid #ff6b00; color: #fff; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                        {{ $packageName }}
                    </h2>
                </div>

                <div class="row justify-content-center">
                    @foreach($packagePlans as $plan)
                        <div class="col-md-10 col-lg-12 mb-4">
                            <div class="bm-card p-4 text-center h-100 bm-vip-card"
                                style="border-top: 4px solid #ff6b00; transition: transform 0.3s; position: relative; overflow: hidden; background: rgba(255,255,255,0.02);">
                                {{-- Subtle background icon --}}
                                <i class="fa fa-star"
                                    style="position: absolute; top: -10px; right: -10px; font-size: 80px; opacity: 0.03; color: #ff6b00; transform: rotate(15deg);"></i>

                                <h3 class="font-weight-bold mb-3 text-warning"
                                    style="text-transform: uppercase; font-size: 1.1rem; letter-spacing: 1px;">
                                    {{ $plan->duration }}
                                </h3>
                                @if($plan->badge_text)
                                    <span class="badge badge-warning mb-3"
                                        style="font-size: 11px; padding: 6px 14px; background: #ff6b00; color: #000; font-weight: 700;">{{ $plan->badge_text }}</span>
                                @endif
                                <div class="mb-4">
                                    <span style="font-size: 1.2rem; color: #aaa; vertical-align: top;">{{ $currencySymbol }}</span>
                                    <span class="bm-vip-price"
                                        style="font-size: 3.5rem; font-weight: 800; color: #fff; line-height: 1;">{{ number_format((float) $plan->price * $conversionRate, 0) }}</span>
                                    @if($selectedCountry && $currencyCode !== 'USD')
                                        <span class="d-block" style="font-size: 0.8rem; color: #888;">({{ $currencyCode }})</span>
                                    @endif
                                </div>
                                @if($plan->features && count($plan->features) > 0)
                                    <ul class="list-unstyled text-left mx-auto mb-4" style="max-width: 280px;">
                                        @foreach($plan->features as $feature)
                                            @php
                                                // Clean up redundancy like "Access Access" or empty "Access"
                                                $feature = trim(str_replace(['Access Access', 'Access Service Access'], 'Access', $feature));
                                                if ($feature === 'Access' || empty($feature))
                                                    continue;

                                                // Highlight key features
                                                $isPremiumFeature = Str::contains($feature, ['Correct Score', '95-97%', '5-10 Odds', 'Bankers']);
                                            @endphp
                                            <li
                                                class="mb-3 d-flex align-items-start {{ $isPremiumFeature ? 'font-weight-bold bm-vip-feature-highlight' : 'small text-muted' }}">
                                                <i
                                                    class="fa {{ $isPremiumFeature ? 'fa-star text-warning' : 'fa-check-circle text-success' }} mr-2 mt-1"></i>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach

                                    </ul>
                                @endif
                                @auth('web')
                                    <a href="{{ route('vip.checkout', $plan->id) }}"
                                        class="btn btn-warning btn-block font-weight-bold py-3 bm-pulse-button"
                                        style="border-radius: 12px; font-size: 1.1rem; background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%); border: none; box-shadow: 0 4px 15px rgba(255,107,0,0.4); text-decoration: none;">
                                        Subscribe Now <i class="fa fa-arrow-right ml-1"></i>
                                    </a>
                                @else
                                    <a href="{{ route('register', ['plan_id' => $plan->id]) }}"
                                        class="btn btn-warning btn-block font-weight-bold py-3 bm-pulse-button"
                                        style="border-radius: 12px; font-size: 1.1rem; background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%); border: none; box-shadow: 0 4px 15px rgba(255,107,0,0.4); text-decoration: none;">
                                        Subscribe Now <i class="fa fa-arrow-right ml-1"></i>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa fa-star fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">No VIP plans available at the moment.</h3>
                <p class="text-muted">Please check back later for our premium offerings.</p>
            </div>
        @endforelse
    </div>

    {{-- Why VIP section --}}
    <section class="bm-card p-4 mt-4">
        <h2 class="bm-section-title text-center mb-4" style="justify-content: center;">Why Choose Our VIP Plans?</h2>
        <div class="bm-faq-list">
            <div class="bm-faq-item">
                <div class="bm-faq-header" onclick="this.closest('.bm-faq-item').classList.toggle('open')">
                    <h4 class="bm-faq-question">High Win Rate</h4>
                    <i class="fa fa-chevron-down bm-faq-icon"></i>
                </div>
                <div class="bm-faq-body">
                    <p class="bm-faq-answer">Our VIP tips come with exceptional accuracy, carefully analyzed by our expert team.</p>
                </div>
            </div>
            <div class="bm-faq-item">
                <div class="bm-faq-header" onclick="this.closest('.bm-faq-item').classList.toggle('open')">
                    <h4 class="bm-faq-question">Daily Tips</h4>
                    <i class="fa fa-chevron-down bm-faq-icon"></i>
                </div>
                <div class="bm-faq-body">
                    <p class="bm-faq-answer">Receive exclusive predictions every day during your subscription period.</p>
                </div>
            </div>
            <div class="bm-faq-item">
                <div class="bm-faq-header" onclick="this.closest('.bm-faq-item').classList.toggle('open')">
                    <h4 class="bm-faq-question">24/7 Support</h4>
                    <i class="fa fa-chevron-down bm-faq-icon"></i>
                </div>
                <div class="bm-faq-body">
                    <p class="bm-faq-answer">Our dedicated support team is available around the clock to assist you.</p>
                </div>
            </div>
        </div>
    </section>

    <x-info-article :metadata="$metadata" />
@endsection