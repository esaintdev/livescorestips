@extends('layouts.app')

@section('title', $fixture['teams']['home']['name'] . ' vs ' . $fixture['teams']['away']['name'] . ' Prediction & Betting Tips (' . \Carbon\Carbon::parse(request()->route('date') ?? $fixture['fixture']['date'])->format('j M Y') . ')')
@section('description', $fixture['teams']['home']['name'] . ' vs ' . $fixture['teams']['away']['name'] . ' prediction and betting tips, including odds, head-to-head stats and ' . $fixture['league']['name'] . ' standings.')
@section('keywords', $fixture['teams']['home']['name'] . ', ' . $fixture['teams']['away']['name'] . ', ' . $fixture['league']['name'] . ' predictions, betting tips, match analysis')

@push('head')
@foreach(($structuredData ?? []) as $schema)
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endforeach
@endpush

@section('content')
<style>
.fixture-show-page .as-card.match-header {
    background: linear-gradient(135deg, #1e1e1e 0%, #1a1a1a 100%);
    border-color: rgba(255,107,0,0.15);
}
.fixture-show-page .match-venue-row {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    padding: 16px 0 0;
    border-top: 1px solid rgba(255,255,255,0.04);
    margin-top: 20px;
}
.fixture-show-page .match-venue-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: rgba(255,255,255,0.35);
}
.fixture-show-page .match-page-title {
    font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 16px; line-height: 1.3;
}
.light-theme .fixture-show-page .match-page-title { color: #222; }
.fixture-show-page .team-name {
    font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px;
}
.fixture-show-page .form-badge {
    width: 22px; height: 22px;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    line-height: 1;
}
.fixture-show-page .form-badge.win { background: rgba(16,185,129,0.15); color: #10b981; }
.fixture-show-page .form-badge.draw { background: rgba(245,158,11,0.15); color: #f59e0b; }
.fixture-show-page .form-badge.loss { background: rgba(239,68,68,0.15); color: #ef4444; }
.fixture-show-page .prediction-card {
    border: 1px solid rgba(255,107,0,0.2);
    position: relative;
}
.fixture-show-page .prediction-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(255,107,0,0.03) 0%, transparent 50%);
    pointer-events: none;
}
.fixture-show-page .stat-label-compact {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255,255,255,0.3);
}
.fixture-show-page .team-logo-lg {
    width: 56px; height: 56px;
    object-fit: contain;
    transition: transform 0.3s;
}
.fixture-show-page .team-logo-lg:hover { transform: scale(1.08); }
.fixture-show-page .league-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 20px;
    font-size: 12px;
    color: rgba(255,255,255,0.5);
}
.fixture-show-page .score-display {
    font-size: 40px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1;
}
.fixture-show-page .score-display.live { color: #ff6b00; }
.fixture-show-page .score-display.final { color: #fff; }
.fixture-show-page .score-display.upcoming { color: rgba(255,255,255,0.8); }
.fixture-show-page .elapsed-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.fixture-show-page .elapsed-badge.live {
    background: rgba(255,107,0,0.12);
    color: #ff6b00;
}
.fixture-show-page .elapsed-badge.ft {
    background: rgba(255,255,255,0.04);
    color: rgba(255,255,255,0.4);
}
.fixture-show-page .comment-bubble {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 12px;
    padding: 16px;
    border-left: 3px solid rgba(255,107,0,0.3);
    transition: all 0.2s;
}
.fixture-show-page .comment-bubble:hover {
    background: rgba(255,255,255,0.03);
    border-color: rgba(255,107,0,0.1);
}
.fixture-show-page .standings-rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    padding: 0 6px;
}
.fixture-show-page .standings-rank-badge.top { background: rgba(16,185,129,0.12); color: #10b981; }
.fixture-show-page .standings-rank-badge.mid { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); }
.fixture-show-page .prediction-prob { font-size:22px;font-weight:800;color:#fff; }
.fixture-show-page .h2h-date { color:rgba(255,255,255,0.35);font-size:12px; }
.fixture-show-page .comment-time { color:rgba(255,255,255,0.25);font-size:10px; }
.fixture-show-page .comment-body { color:rgba(255,255,255,0.7);font-size:13px;line-height:1.6; }
.fixture-show-page .stat-label-center { font-size:10px;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.04em; }
.fixture-show-page .standings-rank-badge.bottom { background: rgba(239,68,68,0.12); color: #ef4444; }
.fixture-show-page .team-logo-sm { width: 18px; height: 18px; object-fit: contain; vertical-align: middle; }
.fixture-show-page .stat-bar-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.fixture-show-page .stat-bar-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.fixture-show-page .stat-bar-labels {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.fixture-show-page .stat-bar-label {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
}
.fixture-show-page .stat-bar-value {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
    min-width: 20px;
    text-align: center;
}
.fixture-show-page .stat-bar-track {
    display: flex;
    height: 4px;
    border-radius: 2px;
    overflow: hidden;
    background: rgba(255,255,255,0.04);
}
.fixture-show-page .stat-bar-home { background: #10b981; border-radius: 2px 0 0 2px; }
.fixture-show-page .stat-bar-away { background: #ef4444; border-radius: 0 2px 2px 0; }
.fixture-show-page .result-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 18px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: 0.04em;
}
.fixture-show-page .result-badge.won { background: rgba(16,185,129,0.15); color: #10b981; }
.fixture-show-page .result-badge.lost { background: rgba(239,68,68,0.15); color: #ef4444; }
.fixture-show-page .result-badge.void { background: rgba(245,158,11,0.15); color: #f59e0b; }
.fixture-show-page .analysis-text { color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.8; margin: 0; }
.fixture-show-page .factor-list { list-style: none; padding: 0; margin: 0; }
.fixture-show-page .factor-list li {
    position: relative;
    padding: 8px 0 8px 24px;
    color: rgba(255,255,255,0.7);
    font-size: 13px;
    line-height: 1.6;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.fixture-show-page .factor-list li:last-child { border-bottom: none; }
.fixture-show-page .factor-list li::before {
    content: '\f00c';
    font-family: FontAwesome;
    position: absolute;
    left: 0;
    top: 9px;
    color: #ff6b00;
    font-size: 11px;
}
.fixture-show-page .related-list { display: flex; flex-direction: column; gap: 10px; }
.fixture-show-page .related-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.04);
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
}
.fixture-show-page .related-item:hover { background: rgba(255,255,255,0.05); }
.fixture-show-page .related-teams { font-size: 13px; font-weight: 600; color: #fff; }
.fixture-show-page .related-tip { font-size: 11px; font-weight: 700; color: #f59e0b; white-space: nowrap; }
.fixture-show-page .related-category-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    padding: 10px 16px;
    border-radius: 10px;
    background: rgba(255,107,0,0.1);
    color: #ff6b00;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}
.light-theme .fixture-show-page .analysis-text,
.light-theme .fixture-show-page .factor-list li { color: rgba(0,0,0,0.65); }
.light-theme .fixture-show-page .factor-list li { border-bottom-color: rgba(0,0,0,0.05); }
.light-theme .fixture-show-page .related-item { background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.04); }
.light-theme .fixture-show-page .related-teams { color: #222; }

@media (max-width: 576px) {
    .fixture-show-page .score-display { font-size: 28px; }
    .fixture-show-page .team-logo-lg { width: 40px; height: 40px; }
    .fixture-show-page .match-venue-row { gap: 12px; }
    .fixture-show-page .match-venue-item { font-size: 10px; }
}

/* ── Light Theme Overrides ── */
.light-theme .fixture-show-page .as-card.match-header {
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    border-color: rgba(0,0,0,0.04);
}
.light-theme .fixture-show-page .match-venue-item,
.light-theme .fixture-show-page .league-badge {
    color: rgba(0,0,0,0.4);
}
.light-theme .fixture-show-page .league-badge {
    background: rgba(0,0,0,0.02);
    border-color: rgba(0,0,0,0.04);
}
.light-theme .fixture-show-page .match-venue-row {
    border-top-color: rgba(0,0,0,0.04);
}
.light-theme .fixture-show-page .stat-bar-label {
    color: rgba(0,0,0,0.6);
}
.light-theme .fixture-show-page .stat-bar-track {
    background: rgba(0,0,0,0.04);
}
.light-theme .fixture-show-page .comment-bubble {
    background: rgba(0,0,0,0.02);
    border-color: rgba(0,0,0,0.04);
}
.light-theme .fixture-show-page .comment-bubble:hover {
    background: rgba(0,0,0,0.03);
}
.light-theme .fixture-show-page .score-display.final,
.light-theme .fixture-show-page .score-display.upcoming {
    color: #222;
}
.light-theme .fixture-show-page .prediction-card {
    border-color: rgba(255,107,0,0.25);
}
.light-theme .fixture-show-page .team-name { color: #222; }
.light-theme .fixture-show-page .stat-label-compact { color: rgba(0,0,0,0.3); }
.light-theme .fixture-show-page .elapsed-badge.ft { background: rgba(0,0,0,0.04); color: rgba(0,0,0,0.4); }
.light-theme .fixture-show-page .prediction-prob { color: #222; }
.light-theme .fixture-show-page .h2h-date { color: rgba(0,0,0,0.35); }
.light-theme .fixture-show-page .comment-time { color: rgba(0,0,0,0.25); }
.light-theme .fixture-show-page .comment-body { color: rgba(0,0,0,0.6); }
.light-theme .fixture-show-page .stat-label-center { color: rgba(0,0,0,0.3); }
.light-theme .fixture-show-page .score-display.final { color: #222; }
.light-theme .fixture-show-page .score-display.upcoming { color: rgba(0,0,0,0.7); }
.light-theme .fixture-show-page .standings-rank-badge.mid { background: rgba(0,0,0,0.04); color: rgba(0,0,0,0.5); }
.light-theme .fixture-show-page .prediction-card::before { opacity: 0.5; }
</style>

<div class="fixture-show-page" style="max-width:1100px;margin:0 auto;">

    <h1 class="match-page-title">{{ $fixture['teams']['home']['name'] }} vs {{ $fixture['teams']['away']['name'] }} Prediction &amp; Betting Tips</h1>

    {{-- ==================== MATCH HEADER ==================== --}}
    <div class="as-card match-header mb-4">
        <div class="as-card-body">
            {{-- League badge + time --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="league-badge">
                    <i class="fa fa-trophy" style="font-size:10px;"></i>
                    <span>{{ $fixture['league']['name'] }}</span>
                </div>
                <div class="text-muted small">
                    <i class="fa fa-calendar mr-1"></i>
                    {{ \Carbon\Carbon::parse($fixture['fixture']['date'])->format('M d, Y H:i') }}
                </div>
            </div>

            {{-- Teams + Score --}}
            <div class="row align-items-center text-center">
                {{-- Home --}}
                <div class="col-5">
                    <div class="mb-2">
                        <img src="{{ $fixture['teams']['home']['logo'] }}" alt="{{ $fixture['teams']['home']['name'] }}" class="team-logo-lg">
                    </div>
                    <h2 class="team-name">{{ $fixture['teams']['home']['name'] }}</h2>
                    @if(!empty($homeForm))
                        <div class="d-flex justify-content-center" style="gap:3px;">
                            @foreach(array_slice($homeForm, 0, 5) as $result)
                                <span class="form-badge {{ $result == 'W' ? 'win' : ($result == 'D' ? 'draw' : 'loss') }}">{{ $result }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Score --}}
                <div class="col-2">
                    @if(isset($fixture['goals']['home']) && isset($fixture['goals']['away']))
                        @php
                            $isLive = in_array($fixture['fixture']['status']['short'], ['1H','HT','2H','ET','BT','P','SUSP','INT','LIVE']);
                            $isFt = $fixture['fixture']['status']['short'] == 'FT';
                        @endphp
                        <div class="score-display {{ $isLive ? 'live' : ($isFt ? 'final' : 'upcoming') }}">
                            {{ $fixture['goals']['home'] }}:{{ $fixture['goals']['away'] }}
                        </div>
                        @if($isLive)
                            <div class="elapsed-badge live mt-2">
                                <span class="blink_me" style="font-size:8px;">&#9679;</span>
                                {{ $fixture['fixture']['status']['elapsed'] }}'
                            </div>
                        @elseif($isFt)
                            <div class="elapsed-badge ft mt-2">FT</div>
                        @endif
                    @else
                        <div class="score-display upcoming">
                            {{ \Carbon\Carbon::parse($fixture['fixture']['date'])->format('H:i') }}
                        </div>
                        <div class="text-muted small mt-1">Not started</div>
                    @endif
                </div>

                {{-- Away --}}
                <div class="col-5">
                    <div class="mb-2">
                        <img src="{{ $fixture['teams']['away']['logo'] }}" alt="{{ $fixture['teams']['away']['name'] }}" class="team-logo-lg">
                    </div>
                    <h2 class="team-name">{{ $fixture['teams']['away']['name'] }}</h2>
                    @if(!empty($awayForm))
                        <div class="d-flex justify-content-center" style="gap:3px;">
                            @foreach(array_slice($awayForm, 0, 5) as $result)
                                <span class="form-badge {{ $result == 'W' ? 'win' : ($result == 'D' ? 'draw' : 'loss') }}">{{ $result }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Venue / Referee row --}}
            <div class="match-venue-row">
                <span class="match-venue-item"><i class="fa fa-map-marker"></i> {{ $fixture['fixture']['venue']['name'] ?? 'N/A' }}</span>
                <span class="match-venue-item"><i class="fa fa-user"></i> {{ $fixture['fixture']['referee'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- ==================== CONTENT ROW ==================== --}}
    <div class="row">
        {{-- LEFT COLUMN (8 cols) --}}
        <div class="col-lg-8">

            {{-- ── Expert Prediction ── --}}
            @if($prediction)
                <div class="as-card prediction-card mb-4">
                    <div class="as-card-body">
                        <div class="as-section-header" style="margin-bottom:20px;">
                            <div class="as-section-icon">
                                <i class="fa fa-star"></i>
                            </div>
                            <div>
                                <h3 class="as-section-title" style="font-size:18px;">Expert Prediction</h3>
                            </div>
                        </div>
                        <div class="row text-center align-items-center">
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="stat-label-compact mb-2">Market</div>
                                <span class="as-table-badge primary" style="font-size:12px;padding:5px 14px;">{{ $prediction->category }}</span>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="stat-label-compact mb-2">Our Tip</div>
                                <div style="font-size:26px;font-weight:800;color:#10b981;">{{ $prediction->tip }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="stat-label-compact mb-2">Probability</div>
                                <div class="prediction-prob">{{ $prediction->probability }}%</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-label-compact mb-2">Odds</div>
                                <div style="font-size:22px;font-weight:800;color:#f59e0b;">{{ $prediction->odds }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Prediction Result (finished / voided) ── --}}
            @if($prediction && !empty($predictionResult) && $predictionResult !== 'pending')
                @php
                    $finalHome = $fixture['goals']['home'] ?? ($prediction->home_score ?? null);
                    $finalAway = $fixture['goals']['away'] ?? ($prediction->away_score ?? null);
                @endphp
                <div class="as-card mb-4" id="prediction-result">
                    <div class="as-card-body">
                        <div class="as-section-header" style="margin-bottom:20px;">
                            <div class="as-section-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div>
                                <h3 class="as-section-title" style="font-size:18px;">Prediction Result</h3>
                            </div>
                        </div>
                        <div class="row text-center align-items-center">
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="stat-label-compact mb-2">Final Score</div>
                                <div class="score-display final" style="font-size:26px;">
                                    @if($finalHome !== null && $finalAway !== null)
                                        {{ $finalHome }}:{{ $finalAway }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="stat-label-compact mb-2">Prediction</div>
                                <div style="font-size:20px;font-weight:800;color:#10b981;">{{ $prediction->tip }}</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="stat-label-compact mb-2">Result</div>
                                <span class="result-badge {{ $predictionResult }}">{{ strtoupper($predictionResult) }}</span>
                            </div>
                        </div>
                        @if(!empty($summary))
                            <p class="analysis-text" style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.05);">
                                {{ $summary }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ── Prediction Analysis ── --}}
            @if($prediction && !empty($analysis))
                <div class="as-card mb-4">
                    <div class="as-card-body">
                        <div class="as-section-header" style="margin-bottom:20px;">
                            <div class="as-section-icon" style="background:rgba(255,107,0,0.1);color:#ff6b00;">
                                <i class="fa fa-lightbulb-o"></i>
                            </div>
                            <div>
                                <h3 class="as-section-title" style="font-size:18px;">Prediction Analysis</h3>
                            </div>
                        </div>
                        <p class="analysis-text">{{ $analysis }}</p>
                    </div>
                </div>
            @endif

            {{-- ── Key Match Factors ── --}}
            @if(!empty($factors))
                <div class="as-card mb-4">
                    <div class="as-card-body">
                        <div class="as-section-header" style="margin-bottom:16px;">
                            <div class="as-section-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9;">
                                <i class="fa fa-list-ul"></i>
                            </div>
                            <div>
                                <h3 class="as-section-title" style="font-size:18px;">Key Match Factors</h3>
                            </div>
                        </div>
                        <ul class="factor-list">
                            @foreach($factors as $factor)
                                <li>{{ $factor }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ── Head to Head ── --}}
            @if(!empty($headToHead))
            <div class="as-card mb-4">
                <div class="as-card-body">
                    <div class="as-section-header">
                        <div class="as-section-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
                            <i class="fa fa-history"></i>
                        </div>
                        <div>
                            <h3 class="as-section-title" style="font-size:18px;">Head to Head (H2H)</h3>
                        </div>
                    </div>
                    <div class="as-table-wrap">
                        <table class="as-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-right">Home</th>
                                    <th class="text-center">Score</th>
                                    <th>Away</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($headToHead, 0, 5) as $h2h)
                                    <tr>
                                        <td class="h2h-date" style="width:18%;">{{ \Carbon\Carbon::parse($h2h['fixture']['date'])->format('M d, Y') }}</td>
                                        <td class="text-right font-weight-bold" style="width:30%;">{{ $h2h['teams']['home']['name'] }}</td>
                                        <td class="text-center font-weight-bold" style="width:16%;color:#f59e0b;">{{ $h2h['goals']['home'] ?? '-' }}-{{ $h2h['goals']['away'] ?? '-' }}</td>
                                        <td class="font-weight-bold" style="width:30%;">{{ $h2h['teams']['away']['name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Discussion ── --}}
            <div class="as-card mb-4">
                <div class="as-card-body">
                    <div class="as-section-header">
                        <div class="as-section-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                            <i class="fa fa-comments-o"></i>
                        </div>
                        <div>
                            <h3 class="as-section-title" style="font-size:18px;">Discussion</h3>
                        </div>
                    </div>

                    {{-- Comment form --}}
                    <form action="{{ $prediction ? route('prediction.comment.store', $prediction->id) : '#' }}" method="POST" style="margin-bottom:24px;">
                        @csrf
                        <div class="as-row-2col" style="gap:16px;">
                            <div class="as-field">
                                <input type="text" name="name" class="as-input" placeholder="Your Name *" required>
                            </div>
                            <div class="as-field">
                                <input type="email" name="email" class="as-input" placeholder="Your Email *" required>
                            </div>
                        </div>
                        <div class="as-field mt-3">
                            <textarea name="comment" rows="3" class="as-input" placeholder="Share your thoughts on this prediction..." required style="resize:vertical;"></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="as-btn">
                                <i class="fa fa-paper-plane"></i> Post Comment
                            </button>
                        </div>
                    </form>

                    {{-- Comments list --}}
                    <div class="comments-list">
                        @forelse($comments as $comment)
                            <div class="comment-bubble mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold" style="color:#f59e0b;font-size:13px;">
                                        <i class="fa fa-user-circle mr-1"></i> {{ $comment->name }}
                                    </span>
                                    <small class="comment-time">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="comment-body">
                                    {!! nl2br(e($comment->comment)) !!}
                                </div>
                            </div>
                        @empty
                            <div class="as-empty" style="padding:32px 16px;">
                                <div class="as-empty-icon"><i class="fa fa-comments-o"></i></div>
                                <div class="as-empty-title">No comments yet</div>
                                <div class="as-empty-text">Be the first to share your thoughts!</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (4 cols) --}}
        <div class="col-lg-4">

            {{-- ── Team Stats (hidden entirely when no meaningful data) ── --}}
            @if(!empty($homeStats) && !empty($awayStats))
            <div class="as-card mb-4">
                <div class="as-card-body">
                    <div class="as-section-header" style="margin-bottom:20px;">
                        <div class="as-section-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;">
                            <i class="fa fa-bar-chart"></i>
                        </div>
                        <div>
                            <h3 class="as-section-title" style="font-size:16px;">Team Stats</h3>
                        </div>
                    </div>
                    @php
                            $statsCols = [
                                ['label' => 'Matches Played', 'home' => $homeStats['fixtures']['played']['total'] ?? 0, 'away' => $awayStats['fixtures']['played']['total'] ?? 0],
                                ['label' => 'Wins', 'home' => $homeStats['fixtures']['wins']['total'] ?? 0, 'away' => $awayStats['fixtures']['wins']['total'] ?? 0],
                                ['label' => 'Draws', 'home' => $homeStats['fixtures']['draws']['total'] ?? 0, 'away' => $awayStats['fixtures']['draws']['total'] ?? 0],
                                ['label' => 'Losses', 'home' => $homeStats['fixtures']['loses']['total'] ?? 0, 'away' => $awayStats['fixtures']['loses']['total'] ?? 0],
                                ['label' => 'Goals For', 'home' => $homeStats['goals']['for']['total']['total'] ?? 0, 'away' => $awayStats['goals']['for']['total']['total'] ?? 0],
                                ['label' => 'Goals Against', 'home' => $homeStats['goals']['against']['total']['total'] ?? 0, 'away' => $awayStats['goals']['against']['total']['total'] ?? 0],
                            ];
                        @endphp
                        <div class="stat-bar-container">
                            @foreach($statsCols as $stat)
                                <div class="stat-bar-row">
                                    <div class="stat-bar-labels">
                                        <span class="stat-bar-label">{{ $stat['home'] }}</span>
                                        <span class="stat-label-center">{{ $stat['label'] }}</span>
                                        <span class="stat-bar-label">{{ $stat['away'] }}</span>
                                    </div>
                                    <div class="stat-bar-track">
                                        @php
                                            $total = intval($stat['home']) + intval($stat['away']);
                                            $homePct = $total > 0 ? (intval($stat['home']) / $total) * 100 : 50;
                                            $awayPct = $total > 0 ? (intval($stat['away']) / $total) * 100 : 50;
                                        @endphp
                                        <div class="stat-bar-home" style="width:{{ $homePct }}%;"></div>
                                        <div class="stat-bar-away" style="width:{{ $awayPct }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== LEAGUE STANDINGS (hidden when unavailable) ==================== --}}
    @if(!empty($standings))
    <div class="as-card mb-4">
        <div class="as-card-body">
            <div class="as-section-header">
                <div class="as-section-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9;">
                    <i class="fa fa-list-ol"></i>
                </div>
                <div>
                    <h3 class="as-section-title" style="font-size:18px;">League Standings — {{ $fixture['league']['name'] }}</h3>
                </div>
            </div>
            @php $tableRows = $standings; @endphp
                <div class="as-table-wrap">
                    <table class="as-table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Team</th>
                                <th class="text-center">MP</th>
                                <th class="text-center hide-mobile-cell">W</th>
                                <th class="text-center hide-mobile-cell">D</th>
                                <th class="text-center hide-mobile-cell">L</th>
                                <th class="text-center hide-mobile-cell">G</th>
                                <th class="text-center">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($tableRows, 0, 20) as $rank)
                                @php
                                    $isHome = $rank['team']['id'] == $fixture['teams']['home']['id'];
                                    $isAway = $rank['team']['id'] == $fixture['teams']['away']['id'];
                                @endphp
                                <tr class="{{ $isHome || $isAway ? 'as-active-row' : '' }}">
                                    <td class="text-center align-middle" style="width:40px;">
                                        <span class="standings-rank-badge {{ $rank['rank'] <= 4 ? 'top' : ($rank['rank'] >= 18 ? 'bottom' : 'mid') }}">
                                            {{ $rank['rank'] }}
                                        </span>
                                    </td>
                                    <td class="align-middle font-weight-bold">
                                        <img src="{{ $rank['team']['logo'] }}" alt="{{ $rank['team']['name'] }} logo" class="team-logo-sm mr-2">
                                        {{ $rank['team']['name'] }}
                                    </td>
                                    <td class="text-center align-middle">{{ $rank['all']['played'] }}</td>
                                    <td class="text-center align-middle hide-mobile-cell">{{ $rank['all']['win'] }}</td>
                                    <td class="text-center align-middle hide-mobile-cell">{{ $rank['all']['draw'] }}</td>
                                    <td class="text-center align-middle hide-mobile-cell">{{ $rank['all']['lose'] }}</td>
                                    <td class="text-center align-middle hide-mobile-cell">{{ $rank['all']['goals']['for'] }}:{{ $rank['all']['goals']['against'] }}</td>
                                    <td class="text-center align-middle font-weight-bold" style="color:#f59e0b;">{{ $rank['points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
    @endif

    {{-- ==================== RELATED PREDICTIONS ==================== --}}
    @if($prediction && ($categorySlug || $related->isNotEmpty()))
    <div class="as-card mb-4">
        <div class="as-card-body">
            <div class="as-section-header" style="margin-bottom:16px;">
                <div class="as-section-icon" style="background:rgba(255,107,0,0.1);color:#ff6b00;">
                    <i class="fa fa-link"></i>
                </div>
                <div>
                    <h3 class="as-section-title" style="font-size:18px;">Related Predictions</h3>
                </div>
            </div>
            @if($categorySlug)
                <a href="{{ route('category.show', $categorySlug) }}" class="related-category-link">
                    <i class="fa fa-th-list"></i> More {{ $prediction->category }} predictions
                </a>
            @endif
            @if($related->isNotEmpty())
                <div class="related-list">
                    @foreach($related as $relatedPrediction)
                        <a class="related-item" href="{{ route('fixture.show', [$relatedPrediction->slug, $relatedPrediction->match_date->format('Y-m-d')]) }}">
                            <span class="related-teams">{{ $relatedPrediction->home_team }} vs {{ $relatedPrediction->away_team }}</span>
                            <span class="related-tip">{{ $relatedPrediction->category }} · {{ $relatedPrediction->tip }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
.hide-mobile-cell { display: table-cell; }
@media (max-width: 576px) {
    .hide-mobile-cell { display: none !important; }
}
</style>
@endsection
