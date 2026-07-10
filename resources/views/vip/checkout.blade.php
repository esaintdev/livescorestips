@extends('layouts.app')

@section('title', 'Complete Subscription - ' . config('app.name'))
@section('description', 'Complete your VIP subscription payment to ' . config('app.name') . ' and get instant access to premium football predictions and betting tips.')
@section('keywords', 'VIP subscription, premium tips, betting payment, complete checkout')

@push('styles')
    <style>
        .payment-option-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 107, 0, 0.2);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .payment-option-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 107, 0, 0.6);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .plan-summary {
            background: linear-gradient(135deg, rgba(255, 107, 0, 0.1) 0%, rgba(184, 134, 11, 0.05) 100%);
            border: 1px solid rgba(255, 107, 0, 0.3);
            border-radius: 16px;
        }
    </style>
@endpush

@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $country = session('selected_country');
    $currencyConfig = $currencyService->getConfig($country);
    $conversionRate = $currencyConfig['rate'];
    $currencySymbol = $currencyConfig['symbol'];
    $currencyCode = $currencyConfig['code'];
    $convertedPrice = $currencyService->convert((float) $vipPlan->price, $country);
@endphp

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2 style="font-weight: 800; color: #fff; font-size: 2.2rem;">Complete Your <span
                            class="text-warning">Subscription</span></h2>
                    <p class="text-muted">Choose your preferred payment method below to activate your VIP access.</p>
                </div>

                {{-- Plan Summary --}}
                <div class="plan-summary p-4 mb-5 text-center">
                    <h4 class="text-white font-weight-bold mb-1">{{ $vipPlan->name }}</h4>
                    <p class="text-warning mb-2">{{ $vipPlan->duration }}</p>
                    <h2 class="font-weight-bold text-white mb-0">
                        {{ $currencySymbol }}{{ number_format($convertedPrice, 0) }}</h2>
                    @if($country && $currencyCode !== 'USD')
                        <small class="text-muted">({{ $currencyCode }})</small>
                    @endif
                </div>

                {{-- Payment Methods --}}
                <div class="row">
                    {{-- Paystack Option --}}
                    <div class="col-md-6 mb-4">
                        <div class="payment-option-card text-center">
                            <div>
                                <div class="mb-4">
                                    <i class="fa fa-credit-card text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <h4 class="text-white font-weight-bold mb-3">Credit / Debit Card</h4>
                                <p class="text-muted small mb-4">Secure automated payment via Paystack. Your account will be
                                    activated instantly.</p>
                            </div>
                            <form method="POST" action="{{ route('vip.pay', $vipPlan->id) }}">
                                @csrf
                                <button type="submit"
                                    class="btn btn-warning btn-block font-weight-bold py-3 bm-pulse-button"
                                    style="border-radius: 12px; font-size: 1.1rem; background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%); border: none; box-shadow: 0 4px 15px rgba(255,107,0,0.4); color: #000;">
                                    Pay Now <i class="fa fa-arrow-right ml-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Crypto Option --}}
                    <div class="col-md-6 mb-4">
                        <div class="payment-option-card text-center">
                            <div>
                                <div class="mb-4">
                                    <i class="fa fa-btc text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <h4 class="text-white font-weight-bold mb-3">Cryptocurrency</h4>
                                <p class="text-muted small mb-4">Pay securely using USDT. Manual verification required after
                                    transfer.</p>
                            </div>
                            <form method="POST" action="{{ route('vip.crypto', $vipPlan->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-block font-weight-bold py-3"
                                    style="border-radius: 12px; font-size: 1.1rem; border-width: 2px;">
                                    Pay with USDT <i class="fa fa-arrow-right ml-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('vip.index') }}" class="text-muted"><i class="fa fa-arrow-left mr-1"></i> Return to
                        Plans</a>
                </div>
            </div>
        </div>
    </div>
@endsection