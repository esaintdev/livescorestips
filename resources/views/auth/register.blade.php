@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'Register - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Create an account to access premium VIP football predictions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('css/auth.css') }}">
@endpush

@section('content')
    <div class="bm-login-wrap">
        <div class="bm-login-card bm-login-card-wide">
            {{-- Header --}}
            <div class="bm-login-header bm-login-header-compact">
                <div class="bm-login-icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <h2>Create your account</h2>
                <p>Join our community to access exclusive VIP predictions</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('register') }}">
                @csrf

                @if(request()->has('plan_id'))
                    <input type="hidden" name="plan_id" value="{{ request('plan_id') }}">
                @endif

                {{-- Full Name --}}
                <div class="bm-login-group">
                    <label class="bm-login-label" for="name">Full Name</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-user bm-input-icon"></i>
                        <input id="name" type="text"
                            class="bm-login-input @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}"
                            required autocomplete="name" autofocus
                            placeholder="Your full name">
                    </div>
                    @error('name')
                        <span class="bm-login-error" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="bm-login-group">
                    <label class="bm-login-label" for="email">Email</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-envelope bm-input-icon"></i>
                        <input id="email" type="email"
                            class="bm-login-input @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}"
                            required autocomplete="email"
                            placeholder="you@example.com">
                    </div>
                    @error('email')
                        <span class="bm-login-error" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="bm-login-group">
                    <label class="bm-login-label" for="phone">Phone (WhatsApp)</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-phone bm-input-icon"></i>
                        <input id="phone" type="text"
                            class="bm-login-input @error('phone') is-invalid @enderror"
                            name="phone" value="{{ old('phone') }}"
                            required autocomplete="tel"
                            placeholder="+234...">
                    </div>
                    @error('phone')
                        <span class="bm-login-error" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="bm-login-group">
                    <label class="bm-login-label" for="password">Password</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-lock bm-input-icon"></i>
                        <input id="password" type="password"
                            class="bm-login-input @error('password') is-invalid @enderror"
                            name="password" required autocomplete="new-password"
                            placeholder="Create a password">
                    </div>
                    @error('password')
                        <span class="bm-login-error" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="bm-login-group bm-login-group-last">
                    <label class="bm-login-label" for="password-confirm">Confirm Password</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-lock bm-input-icon"></i>
                        <input id="password-confirm" type="password"
                            class="bm-login-input"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="Confirm your password">
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="bm-login-submit">
                    Create Account <i class="fa fa-arrow-right"></i>
                </button>
            </form>

            {{-- Login --}}
            <div class="bm-login-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </div>
@endsection
