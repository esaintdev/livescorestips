@extends('layouts.app')

@section('title', 'My VIP Games - ' . config('app.name'))
@section('description', 'View your daily VIP betting picks and tips.')

@section('content')
<div class="container py-4">
    <div class="vip-results-header">
        <div class="vip-results-title">
            <h1>VIP <span>GAMES</span></h1>
            <p>Your daily VIP betting picks — {{ $activeSub->vipPlan->name }}</p>
        </div>
        <div class="win-rate-badge">
            <span class="label">Plan</span>
            <span class="value" style="font-size:14px;">{{ $activeSub->vipPlan->name }}</span>
        </div>
    </div>

    <div class="vip-results-timeline">
        @forelse($games as $game)
            <div class="vip-date-group">
                <div class="vip-date-header">
                    <div class="vip-date-info">
                        <div class="vip-date-icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="vip-date-text">
                            <h3>{{ $game->date->format('D, d M Y') }}</h3>
                            @if($game->items->every(fn($i) => $i->is_won !== null))
                                <span class="odds">ALL RESOLVED — Total Odds: {{ number_format($game->total_odds, 2) }}</span>
                            @else
                                <span class="odds">{{ $game->items->count() }} Picks</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="vip-matches-list">
                    @foreach($game->items as $item)
                        <div class="vip-match-card">
                            <div class="vip-match-time">
                                {{ substr($item->match_time, 0, 5) }}
                            </div>
                            <div class="vip-match-main">
                                <span class="vip-match-league">{{ $item->league_name }}</span>
                                <div class="vip-match-teams">
                                    <span class="vip-team">{{ $item->home_team }}</span>
                                    <span class="vip-score">
                                        @if($item->home_score !== null && $item->away_score !== null)
                                            {{ $item->home_score }} : {{ $item->away_score }}
                                        @else
                                            vs
                                        @endif
                                    </span>
                                    <span class="vip-team">{{ $item->away_team }}</span>
                                </div>
                            </div>
                            <div class="vip-match-tip">
                                <span class="vip-tip-label">Tips</span>
                                <span class="vip-tip-value">
                                    {{ $item->tip }}
                                    @if($item->odds)
                                        <span class="vip-odds-badge">@ {{ number_format($item->odds, 2) }}</span>
                                    @endif
                                    @if($item->is_won === true)
                                        <i class="fa fa-check-circle" style="color:#28a745;"></i>
                                    @elseif($item->is_won === false)
                                        <i class="fa fa-times-circle" style="color:#dc3545;"></i>
                                    @else
                                        <span class="vip-status-badge upcoming">Upcoming</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fa fa-gamepad fa-4x text-light"></i>
                </div>
                <h3>No games available yet</h3>
                <p class="text-muted">Your VIP picks will appear here once the admin publishes them.</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
            </div>
        @endforelse
    </div>
</div>

<style>
    .vip-odds-badge {
        display: inline-block;
        background: rgba(255,255,255,0.08);
        padding: 1px 8px;
        border-radius: 4px;
        font-size: 11px;
        color: #aaa;
        margin-left: 6px;
    }
    .vip-status-badge.upcoming {
        display: inline-block;
        background: rgba(255,193,7,0.15);
        color: #ffc107;
        padding: 1px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: 6px;
    }
</style>
@endsection
