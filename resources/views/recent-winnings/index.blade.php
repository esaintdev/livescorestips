@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'Recent Winning Tips - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Check out our recent winning football predictions and see why ' . config('app.name') . ' is the best prediction site.')
@section('keywords', $metadata['page_keywords'] ?? 'winning tips, football results, betting success')

@section('page-header')
    <h2 class="bm-page-title">
        <i class="fa fa-trophy mr-2 text-warning"></i> 
        {{ $metadata['page_subheader'] ?? '' }}
    </h2>
    @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
        <p class="bm-page-subheader">
            {{ $metadata['page_description'] }}
        </p>
    @endif
@endsection

@section('content')
    {{-- Stats Bar --}}
    <div class="bm-card mb-4 p-4">
        <div class="row text-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="h3 font-weight-bold text-warning mb-1">{{ $winnings->total() }}+</div>
                <small class="text-muted">Total Winning Tips</small>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="h3 font-weight-bold text-success mb-1">95%+</div>
                <small class="text-muted">Accuracy Rate</small>
            </div>
            <div class="col-md-4">
                <div class="h3 font-weight-bold text-info mb-1">Daily</div>
                <small class="text-muted">Updated Results</small>
            </div>
        </div>
    </div>

    {{-- Winnings List --}}
    <div class="row">
        @forelse($winnings as $winning)
            <div class="col-md-6 col-lg-4 mb-4">
                <x-winning-card :winning="$winning" />
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa fa-trophy fa-3x text-muted mb-3"></i>
                <p class="text-muted">No recent winnings found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $winnings->links() }}
    </div>

    {{-- CTA Section --}}
    <section class="bm-card mt-5 p-4 text-center" style="background: linear-gradient(135deg, rgba(255,107,0,0.1) 0%, rgba(153,101,21,0.1) 100%); border: 1px solid rgba(255,107,0,0.2);">
        <h2 class="bm-section-title mb-3">Want Access to Premium Winning Tips?</h2>
        <p class="text-muted mb-4">Join our VIP subscription for exclusive high-accuracy predictions!</p>
        <a href="{{ route('vip.index') }}" class="btn btn-warning btn-lg font-weight-bold" style="border-radius: 25px; padding: 12px 35px;">
            View VIP Plans <i class="fa fa-arrow-right ml-2"></i>
        </a>
    </section>
    </section>

    <x-info-article :metadata="$metadata" />
@endsection
