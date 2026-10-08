@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'World Cup 2026 Predictions - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'FIFA World Cup 2026 predictions, betting tips and match analysis.')
@section('keywords', $metadata['page_keywords'] ?? 'World Cup 2026, FIFA World Cup, World Cup predictions, World Cup betting tips')

@section('page-header')
    <div class="text-center">
        <div class="bm-worldcup-icon mb-3">
            <i class="fa fa-globe" style="font-size: 32px; color: #ff6b00;"></i>
        </div>
        <h2 class="bm-page-title">FIFA World Cup 2026</h2>
        @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
            <p class="bm-page-subheader">
                {{ $metadata['page_description'] }}
            </p>
        @endif
    </div>
@endsection

@section('content')
    {{-- Filter Toolbar --}}
    <x-filter-toolbar 
        :date="$date" 
        :dateRange="$dateRange" 
        :isLive="$isLive" 
        :liveCount="$liveCount" 
        route="worldcup.index"
        :marketFilters="$marketFilters ?? []"
        :selectedMarket="request()->input('market')"
    />

    {{-- Match List --}}
    <div class="bm-match-list">
        @php $currentLeagueId = null; @endphp
        @forelse($predictions as $tip)
            @if($tip->league_id !== $currentLeagueId)
                @if($currentLeagueId !== null)
                    </div> <!-- Close previous group -->
                @endif
                @php $currentLeagueId = $tip->league_id; @endphp
                <div class="bm-match-group" data-league="{{ $tip->league_name }}">
                    <div class="bm-league-header">
                        <i class="fa fa-star-o bm-star" style="color: #ff6b00;"></i>
                        <img src="{{ $tip->league_logo ? url($tip->league_logo) : asset('images/logo.png') }}"
                            alt="{{ $tip->league_name ?? 'League' }} logo" style="max-height: 16px;">
                        <span>{{ $tip->league_country ?? 'World' }} - {{ $tip->league_name }} 2026</span>
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
                :odds="$tip->odds > 0 ? $tip->odds : '-'"
                :status="$tip->status"
                :homeForm="$tip->home_form"
                :awayForm="$tip->away_form"
            />

            @if($loop->last)
                </div> <!-- Close last group -->
            @endif
        @empty
            <div class="p-4 text-center text-muted card mb-4"
                style="background: #1e1e1e; border: none; border-radius: 12px;">
                <i class="fa fa-futbol-o fa-3x mb-3 text-muted"></i>
                <p>No World Cup 2026 predictions available for this date.</p>
                <p style="font-size: 13px; opacity: 0.5;">World Cup predictions will appear here automatically when generated. Check back later!</p>
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

@push('styles')
<style>
.bm-worldcup-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 107, 0, 0.1);
    border: 2px solid rgba(255, 107, 0, 0.3);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .bm-worldcup-icon {
        width: 48px;
        height: 48px;
    }
    .bm-worldcup-icon i {
        font-size: 24px !important;
    }
}
</style>
@endpush
