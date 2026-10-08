@extends('layouts.app')

@php
    $formattedDate = \Carbon\Carbon::parse($date)->format('l, jS F Y');
@endphp

@section('title', $metadata['page_title'] ?? 'Banker Predictions Today - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Get today\'s safest banker predictions with expert analysis. High-confidence football betting tips carefully selected by our team at ' . config('app.name') . '.')
@section('keywords', $metadata['page_keywords'] ?? 'banker predictions, banker tips, banker predictions today, football banker tips, safe predictions')
@section('robots', ($date ?? now()->format('Y-m-d')) === now()->format('Y-m-d') ? 'index, follow' : 'noindex, follow')

@section('page-header')
    <div class="bm-page-header">
        <h1 class="bm-page-title">{{ $metadata['page_subheader'] ?? 'Banker Predictions Today' }}</h1>
        @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
            <p class="bm-page-subheader">{{ $metadata['page_description'] }}</p>
        @endif
        @if(isset($metadata['header_intro']) && !empty($metadata['header_intro']))
            <div class="bm-seo-intro"><p class="bm-seo-intro-text">{{ $metadata['header_intro'] }}</p></div>
        @endif
    </div>
@endsection

@section('content')
    <x-filter-toolbar
        :date="$date"
        :dateRange="$dateRange"
        route="banker.show"
        :marketFilters="collect()"
    />

    <div class="bm-match-list">
        @php $currentLeagueId = null; @endphp
        @forelse($predictions as $prediction)
            @if($prediction->league_id !== $currentLeagueId)
                @php $currentLeagueId = $prediction->league_id; @endphp
                <div class="bm-league-header">
                    <i class="fa fa-star-o bm-star"></i>
                    <img src="{{ $prediction->league_logo ? url($prediction->league_logo) : asset('images/logo.png') }}"
                        alt="{{ $prediction->league_name ?? 'League' }} logo" style="max-height: 16px;">
                    <span>{{ $prediction->league_country ?? 'International' }} - {{ $prediction->league_name }}</span>
                </div>
            @endif

            <x-betting-card
                :matchUrl="route('fixture.show', [$prediction->slug ?? $prediction->fixture_id, $prediction->match_date->format('Y-m-d')])"
                :time="isset($prediction->live_elapsed) ? $prediction->live_elapsed : \Carbon\Carbon::parse($prediction->match_time)->format('H:i')"
                :isLive="isset($prediction->is_live) && $prediction->is_live"
                :homeTeam="$prediction->home_team"
                :homeTeamLogo="$prediction->home_team_logo"
                :awayTeam="$prediction->away_team"
                :awayTeamLogo="$prediction->away_team_logo"
                :homeScore="$prediction->home_score"
                :awayScore="$prediction->away_score"
                :probability="$prediction->probability"
                :market="$prediction->category"
                :tip="$prediction->tip"
                :odds="$prediction->odds"
                :status="$prediction->status"
                :homeForm="$prediction->home_form"
                :awayForm="$prediction->away_form"
                :isBanker="true"
            />
        @empty
            <div class="p-4 text-center text-muted card mb-4"
                style="background: #1e1e1e; border: none; border-radius: 12px;">
                <i class="fa fa-calendar-o fa-3x mb-3 text-muted"></i>
                <p>No banker predictions for {{ $formattedDate }}.</p>
            </div>
        @endforelse
    </div>

    @if(isset($recentWinnings) && $recentWinnings->count() > 0)
        <section class="bm-card mt-5">
            <div class="d-flex align-items-center mb-4">
                <h2 class="bm-section-title mb-0"><i class="fa fa-trophy mr-2 text-warning"></i> Recent Banker Winnings</h2>
                <a href="{{ route('recent-winnings.index') }}" class="ml-auto text-success" style="font-size: 13px; font-weight: 500;">View all <i class="fa fa-chevron-right ml-1"></i></a>
            </div>
            <div class="row">
                @foreach($recentWinnings->take(3) as $winning)
                    <div class="col-md-4 mb-3">
                        <x-winning-card :winning="$winning" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <x-info-article :metadata="$metadata" />
@endsection
