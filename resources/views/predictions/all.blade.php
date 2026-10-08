@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'All Football Predictions - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'All football predictions for ' . (\Carbon\Carbon::parse($date)->format('M d, Y')) . '. Expert analysis, stats, and betting tips.')
@section('keywords', $metadata['page_keywords'] ?? 'football predictions, betting tips, ' . $date)
@section('robots', ($date ?? now()->format('Y-m-d')) === now()->format('Y-m-d') ? 'index, follow' : 'noindex, follow')

@section('page-header')
    <h1 class="bm-page-title">
        {{ $metadata['page_subheader'] ?? '' }}
    </h1>
    @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
        <p class="bm-page-subheader">
            {{ $metadata['page_description'] }}
        </p>
    @endif
@endsection

@section('content')
    {{-- Filter Toolbar --}}
    <x-filter-toolbar 
        :date="$date" 
        :dateRange="$dateRange" 
        :isLive="$isLive" 
        :liveCount="$liveCount" 
        route="predictions.all"
        :marketFilters="[]"
    />

    <!-- Match List -->
    <div class="bm-match-list">
        @php $currentLeagueId = null; @endphp
        @forelse($predictions as $tip)
            @if($tip->league_id !== $currentLeagueId)
                @php $currentLeagueId = $tip->league_id; @endphp
                <div class="bm-league-header">
                    <i class="fa fa-star-o bm-star"></i>
                    <img src="{{ $tip->league_logo ? url($tip->league_logo) : asset('images/logo.png') }}"
                        alt="{{ $tip->league_name ?? 'League' }} logo" style="max-height: 16px;">
                    <span>{{ $tip->league_country ?? 'Europe' }} - {{ $tip->league_name }}</span>
                </div>
            @endif

            <x-betting-card
                :matchUrl="route('fixture.show', [$tip->slug ?? $tip->fixture_id, $tip->match_date->format('Y-m-d')])"
                :time="isset($tip->live_elapsed) ? $tip->live_elapsed : \Carbon\Carbon::parse($tip->match_time)->format('H:i')"
                :isLive="isset($tip->is_live) && $tip->is_live"
                :homeTeam="$tip->home_team"
                :homeTeamLogo="$tip->home_team_logo"
                :awayTeam="$tip->away_team"
                :awayTeamLogo="$tip->away_team_logo"
                :homeScore="$tip->home_score"
                :awayScore="$tip->away_score"
                :probability="$tip->probability"
                :market="$tip->category"
                :tip="$tip->tip"
                :odds="$tip->odds"
                :status="$tip->status"
                :homeForm="$tip->home_form"
                :awayForm="$tip->away_form"
            />
        @empty
            <div class="p-4 text-center text-muted card mb-4"
                style="background: #1e1e1e; border: none; border-radius: 12px;">
                <i class="fa fa-calendar-o fa-3x mb-3" style="color: #333;"></i>
                <p>No predictions available for this date.</p>
            </div>
        @endforelse
    </div>

    {{-- Back to Home Link --}}
    <div class="text-center mt-5 mb-5">
        <a href="{{ route('home') }}" class="btn btn-lg bm-market-btn active" style="padding: 12px 30px; border-radius: 25px;">
            <i class="fa fa-home mr-2"></i> Back to Home
        </a>
    </div>

    <x-info-article :metadata="$metadata" />
@endsection