@extends('layouts.app')

@section('title', 'USDT Payment Instructions - ' . config('app.name'))
@section('description', 'Instructions for paying your ' . config('app.name') . ' VIP subscription via USDT cryptocurrency. Get instant access to premium predictions after payment confirmation.')
@section('keywords', 'USDT payment, crypto payment, VIP subscription, premium tips')

@push('styles')
<style>
    .crypto-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 107, 0, 0.3);
        border-radius: 16px;
        padding: 1.5rem 2rem;
    }
    .wallet-box {
        background: #000;
        border: 1px dashed rgba(255, 107, 0, 0.5);
        border-radius: 12px;
        padding: 1rem;
        margin: 1rem 0;
        word-break: break-all;
        position: relative;
    }
    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 107, 0, 0.2);
        color: #ff6b00;
        border: none;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .copy-btn:hover {
        background: rgba(255, 107, 0, 0.4);
        color: #fff;
    }
    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #ff6b00;
        color: #000;
        border-radius: 50%;
        font-weight: bold;
        margin-right: 12px;
    }
</style>
@endpush

@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $country = session('selected_country');
    $currencyConfig = $currencyService->getConfig($country);
    $currencySymbol = $currencyConfig['symbol'];
    $currencyCode = $currencyConfig['code'];
    $convertedPrice = $currencyService->convert((float) $vipPlan->price, $country);
@endphp

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="crypto-card">
                <div class="text-center mb-3">
                    <i class="fa fa-clock-o text-warning mb-2" style="font-size: 3rem;"></i>
                    <h3 class="text-white font-weight-bold mb-2">Awaiting Payment</h3>
                    <p class="text-muted small">Please follow the instructions below to activate your <span class="text-warning">{{ $vipPlan->name }}</span>.</p>
                </div>

                <hr style="border-color: rgba(255,255,255,0.1); margin: 1.5rem 0;">

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <span class="step-badge">1</span>
                        <h5 class="text-white font-weight-bold mb-0">Transfer USDT</h5>
                    </div>
                    <p class="text-muted ml-5 mb-0">Send exactly <strong class="text-warning">${{ number_format((float) $vipPlan->price, 0) }} USDT</strong> 
                        @if($country && $currencyCode !== 'USD')
                            <span class="text-muted">(approx. {{ $currencySymbol }}{{ number_format($convertedPrice, 0) }} {{ $currencyCode }})</span>
                        @endif
                        to the wallet address below via the <strong class="text-white">TRC20 Network</strong>.</p>
                    
                    <div class="wallet-box ml-5">
                        <button class="copy-btn" onclick="copyWallet()" id="copyBtn"><i class="fa fa-copy mr-1"></i> Copy</button>
                        <p class="text-muted small mb-1 text-uppercase">USDT (TRC20) Wallet Address</p>
                        <h5 class="text-white font-family-monospace mb-0" id="walletAddress">TJkfZ43RrCzYWeApQ38kLpcTsASXDHNTNn</h5>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="step-badge">2</span>
                        <h6 class="text-white font-weight-bold mb-0">Send Payment Proof</h6>
                    </div>
                    <p class="text-muted ml-5 mb-2 small">After your transfer is complete, take a screenshot of the receipt and send it to our WhatsApp support line along with your reference number.</p>
                    
                    <div class="ml-5 p-2" style="background: rgba(255,255,255,0.02); border-radius: 8px;">
                        <p class="text-muted small mb-1">Your Reference Number:</p>
                        <h6 class="text-warning font-family-monospace mb-0">{{ $subscription->payment_reference }}</h6>
                    </div>
                </div>

                <div class="text-center mt-4">
                    @php
                        $message = "Hello, I have just made a USDT payment for my VIP Subscription.\n";
                        $message .= "Reference: {$subscription->payment_reference}\n";
                        $message .= "Plan: {$vipPlan->name}\n";
                        $message .= "Email: " . auth()->user()->email;
                        $whatsappUrl = 'https://wa.me/2348121855275?text=' . urlencode($message);
                    @endphp
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-lg font-weight-bold px-5 py-3" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                        <i class="fa fa-whatsapp mr-2" style="font-size: 1.2rem;"></i> Send Proof on WhatsApp
                    </a>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('account.index') }}" class="text-muted small">Go to my dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyWallet() {
    var copyText = document.getElementById("walletAddress").innerText;
    var textArea = document.createElement("textarea");
    textArea.value = copyText;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("Copy");
    textArea.remove();
    
    var btn = document.getElementById("copyBtn");
    btn.innerHTML = '<i class="fa fa-check mr-1"></i> Copied!';
    btn.style.color = '#28a745';
    
    setTimeout(function() {
        btn.innerHTML = '<i class="fa fa-copy mr-1"></i> Copy';
        btn.style.color = '#ff6b00';
    }, 3000);
}
</script>
@endpush
