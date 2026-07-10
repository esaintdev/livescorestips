@props(['winning'])

<div class="bm-winning-card shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="d-flex align-items-center">
            <i class="fa fa-star-o mr-2 text-muted" style="font-size: 10px;"></i>
            <small class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($winning->match_date)->format('M d') }}</small>
            <span class="bm-header-tip ml-2">Tip: {{ $winning->tip }}</span>
        </div>
    </div>
    <div class="bm-winning-match">
        <div class="bm-winning-team-row">
            <div class="bm-team-info">
                <img src="{{ $winning->home_team_logo ?? asset('images/logo.png') }}" class="bm-team-logo-tiny">
                <span>{{ $winning->home_team }}</span>
            </div>
            <span class="bm-score">{{ $winning->home_score ?? '-' }}</span>
        </div>
        <div class="bm-winning-team-row">
            <div class="bm-team-info">
                <img src="{{ $winning->away_team_logo ?? asset('images/logo.png') }}" class="bm-team-logo-tiny">
                <span>{{ $winning->away_team }}</span>
            </div>
            <span class="bm-score">{{ $winning->away_score ?? '-' }}</span>
        </div>
    </div>
</div>