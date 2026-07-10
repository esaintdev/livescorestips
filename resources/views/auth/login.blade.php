@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'Login - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Login to access your premium VIP football predictions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
@endpush

@section('content')
    <div class="bm-login-wrap">
        <div class="bm-login-card">
            {{-- Header --}}
            <div class="bm-login-header">
                <div class="bm-login-icon">
                    <i class="fa fa-user-circle"></i>
                </div>
                <h2>Welcome back</h2>
                <p>Sign in to access your account</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="bm-login-group">
                    <label class="bm-login-label" for="email">Email</label>
                    <div class="bm-login-input-wrap">
                        <i class="fa fa-envelope bm-input-icon"></i>
                        <input id="email" type="email"
                            class="bm-login-input @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}"
                            required autocomplete="email" autofocus
                            placeholder="you@example.com">
                    </div>
                    @error('email')
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
                            name="password" required autocomplete="current-password"
                            placeholder="Enter your password">
                    </div>
                    @error('password')
                        <span class="bm-login-error" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Options --}}
                <div class="bm-login-options">
                    <label class="bm-login-remember">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="bm-login-checkbox"><i class="fa fa-check"></i></span>
                        <span>Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="bm-login-submit">
                    Sign In <i class="fa fa-arrow-right"></i>
                </button>
            </form>

            {{-- Register --}}
            <div class="bm-login-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
            </div>
        </div>
    </div>
@endsection
