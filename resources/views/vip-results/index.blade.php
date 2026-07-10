@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'VIP Results - Historical Performance')
@section('description', $metadata['page_description'] ?? 'View the historical performance and past winnings of our VIP betting tips.')
@section('keywords', $metadata['page_keywords'] ?? 'VIP results, winning tips, historical performance')

@section('content')
<div class="container py-4">
    <!-- VIP Results Header -->
    <div class="vip-results-header">
        <div class="vip-results-title">
            <h1>VIP <span>RESULTS</span></h1>
            <p>Historical performance of our premium investment tips</p>
        </div>
        <div class="win-rate-badge">
            <span class="label">Win Rate</span>
            <span class="value">{{ $overallWinRate }}%</span>
        </div>
    </div>

    <!-- Results Timeline -->
    <div class="vip-results-timeline">
        @forelse($results as $result)
            <div class="vip-date-group">
                <div class="vip-date-header">
                    <div class="vip-date-info">
                        <div class="vip-date-icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="vip-date-text">
                            <h3>{{ $result->date->format('D, d M Y') }}</h3>
                            <span class="odds">TOTAL ODDS: {{ number_format($result->total_odds, 2) }}</span>
                        </div>
                    </div>
                    <div>
                        @if($result->status === 'won')
                            <span class="vip-status-sticker sticker-won">
                                <i class="fa fa-check-circle mr-1"></i> WON
                            </span>
                        @else
                            <span class="vip-status-sticker sticker-lost">
                                <i class="fa fa-times-circle mr-1"></i> LOST
                            </span>
                        @endif
                    </div>
                </div>

                <div class="vip-matches-list">
                    @foreach($result->items as $item)
                        <div class="vip-match-card">
                            <div class="vip-match-time">
                                {{ substr($item->match_time, 0, 5) }}
                            </div>
                            <div class="vip-match-main">
                                <span class="vip-match-league">{{ $item->league_name }}</span>
                                <div class="vip-match-teams">
                                    <span class="vip-team">{{ $item->home_team }}</span>
                                    <span class="vip-score">
                                        {{ $item->home_score ?? 0 }} : {{ $item->away_score ?? 0 }}
                                    </span>
                                    <span class="vip-team">{{ $item->away_team }}</span>
                                </div>
                            </div>
                            <div class="vip-match-tip">
                                <span class="vip-tip-label">Tips</span>
                                <span class="vip-tip-value">
                                    {{ $item->tip }}
                                    @if($item->is_won)
                                        <i class="fa fa-check-circle"></i>
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
                    <i class="fa fa-history fa-4x text-light"></i>
                </div>
                <h3>No results posted yet</h3>
                <p class="text-muted">Stay tuned for our upcoming VIP winning performances.</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection
