@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center text-center">
        <div class="col-md-8">
            <div class="error-container py-5">
                {{-- Decorative 404 with glowing effect --}}
                <div class="error-code mb-0" style="
                    font-size: 15vw;
                    font-weight: 900;
                    line-height: 1;
                    background: linear-gradient(180deg, #ff6b00 0%, #ff6b00 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    text-shadow: 0 10px 30px rgba(255, 107, 0, 0.2);
                    filter: drop-shadow(0 0 10px rgba(255, 107, 0, 0.5));
                ">
                    404
                </div>
                
                <h1 class="text-white font-weight-bold mb-3" style="font-size: 2.5rem;">Oops! Match Missing.</h1>
                <p class="text-muted mb-5 px-md-5" style="font-size: 1.1rem; line-height: 1.8;">
                    It looks like the page you're searching for has been moved or no longer exists. 
                    Don't worry, we have plenty of winning predictions waiting for you!
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ url('/') }}" class="btn btn-warning font-weight-bold px-5 py-3 rounded-pill shadow-lg mr-2 mb-3">
                        <i class="fa fa-home mr-2"></i> Back to Homepage
                    </a>
                    <a href="{{ route('predictions.all') }}" class="btn btn-outline-warning font-weight-bold px-5 py-3 rounded-pill mb-3">
                        <i class="fa fa-soccer-ball-o mr-2"></i> Today's Tips
                    </a>
                </div>

                <div class="row mt-5 pt-5 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                    <div class="col-12">
                        <p class="text-muted text-uppercase small font-weight-bold mb-4" style="letter-spacing: 2px;">Popular Shortcuts</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('vip-results.index') }}" class="text-white text-decoration-none d-block p-3 rounded bg-dark border border-secondary hover-gold">
                            <i class="fa fa-star text-warning mb-2 d-block fa-2x"></i>
                            <span class="small font-weight-bold">VIP Results</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('blog.index') }}" class="text-white text-decoration-none d-block p-3 rounded bg-dark border border-secondary hover-gold">
                            <i class="fa fa-newspaper-o text-info mb-2 d-block fa-2x"></i>
                            <span class="small font-weight-bold">Expert Analysis</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="#" onclick="window.history.back(); return false;" class="text-white text-decoration-none d-block p-3 rounded bg-dark border border-secondary hover-gold">
                            <i class="fa fa-arrow-left text-muted mb-2 d-block fa-2x"></i>
                            <span class="small font-weight-bold">Previous Page</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-gold {
        transition: all 0.3s ease;
    }
    .hover-gold:hover {
        border-color: #ff6b00 !important;
        background: rgba(255, 107, 0, 0.05) !important;
        transform: translateY(-5px);
    }
    .gap-3 { gap: 1rem; }
    
    @keyframes floating {
        0% { transform: translate(0, 0px); }
        50% { transform: translate(0, 15px); }
        100% { transform: translate(0, -0px); }
    }
    
    .error-code {
        animation: floating 4s ease-in-out infinite;
    }
</style>
@endsection
