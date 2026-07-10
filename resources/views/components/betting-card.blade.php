@props([
    'matchUrl' => '#',
    'time' => '00:00',
    'isLive' => false,
    'homeTeam' => 'Team A',
    'homeTeamLogo' => null,
    'awayTeam' => 'Team B',
    'awayTeamLogo' => null,
    'homeScore' => null,
    'awayScore' => null,
    'probability' => 0,
    'market' => 'Full Time',
    'tip' => '-',
    'odds' => '-',
    'status' => 'pending',
    'homeForm' => null,
    'awayForm' => null,
    'isBanker' => false
])

<a href="{{ $matchUrl ?? '#' }}" class="bm-match-row-premium @if($status === 'won' || $status === 'lost') status-{{ $status }} @endif" 
   data-probability="{{ $probability }}" 
   data-odds="{{ $odds > 0 ? $odds : '0' }}" 
   data-market="{{ $market }}"
   style="text-decoration: none; color: inherit;">
    <div class="bm-match-main">
        <div class="bm-match-star">
            @if($isBanker)
                <span class="bm-banker-badge"><i class="fa fa-shield"></i></span>
            @else
                <i class="fa fa-star-o"></i>
            @endif
        </div>
        <div class="bm-match-time">
            @if($isLive)
                <span class="text-danger blink_me d-block">{{ $time }}</span>
            @else
                <span class="d-block">{{ $time }}</span>
            @endif
        </div>
        <div class="bm-match-teams">
            <div class="bm-match-team">
                <img src="{{ $homeTeamLogo ?? asset('images/logo.png') }}" alt="{{ $homeTeam }}" class="bm-team-logo-tiny">
                <span class="bm-team-name-text">{{ $homeTeam }}</span>
                @if($homeForm && is_array($homeForm))
                    <span class="bm-form-badges">
                        @foreach(array_slice($homeForm, -5) as $result)
                            <span class="bm-form-badge bm-form-{{ strtolower($result) }}">{{ $result }}</span>
                        @endforeach
                    </span>
                @endif
                @if(isset($homeScore))
                    <span class="bm-match-score">{{ $homeScore }}</span>
                @endif
            </div>
            <div class="bm-match-team">
                <img src="{{ $awayTeamLogo ?? asset('images/logo.png') }}" alt="{{ $awayTeam }}" class="bm-team-logo-tiny">
                <span class="bm-team-name-text">{{ $awayTeam }}</span>
                @if($awayForm && is_array($awayForm))
                    <span class="bm-form-badges">
                        @foreach(array_slice($awayForm, -5) as $result)
                            <span class="bm-form-badge bm-form-{{ strtolower($result) }}">{{ $result }}</span>
                        @endforeach
                    </span>
                @endif
                @if(isset($awayScore))
                    <span class="bm-match-score">{{ $awayScore }}</span>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Mobile Status Badge (in the middle) --}}
    @if($status === 'won' || $status === 'lost')
        <div class="d-md-none bm-mobile-status-middle mx-2">
            <span class="bm-mobile-status-badge stamp-{{ $status }}">
                {{ strtoupper($status) }}
            </span>
        </div>
    @endif
    
    <div class="bm-match-stats">
        <div class="bm-stat-col bm-prob-col">
            @if($probability > 0)
                @php
                    $probClass = 'prob-low';
                    if($probability >= 80) $probClass = 'prob-high';
                    elseif($probability >= 50) $probClass = 'prob-med';
                @endphp
                <span class="bm-prob-badge {{ $probClass }}">{{ $probability }}%</span>
            @else
                -
            @endif
        </div>
        <div class="bm-stat-col bm-tip-col">
            <span class="bm-tip-badge">{{ $tip }}</span>
        </div>
        <div class="bm-stat-col bm-odd-col">{{ $odds }}</div>
    </div>
    
    @if($status === 'won' || $status === 'lost')
        {{-- Desktop Status Badge (right side) --}}
        <div class="bm-match-status-badge stamp-{{ $status }} d-none d-md-block">
            {{ strtoupper($status) }}
        </div>
    @endif
</a>

@push('styles')
@once
<style>
.bm-match-row-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: background 0.2s;
    position: relative;
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.bm-match-row-premium:hover {
    background: rgba(255, 255, 255, 0.05);
}

/* Removed right border logic, using inline badges instead */
.bm-match-status-badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    border: 1px solid;
    margin-left: 10px;
}

.bm-match-status-badge.stamp-won,
.bm-mobile-status-badge.stamp-won {
    color: #28a745;
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.bm-match-status-badge.stamp-lost,
.bm-mobile-status-badge.stamp-lost {
    color: #dc3545;
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.bm-mobile-status-badge {
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.5px;
    border: 1px solid;
    display: inline-block;
}

.bm-match-main {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.bm-match-star {
    width: 30px;
    color: #666;
    font-size: 14px;
    cursor: pointer;
}

.bm-match-star:hover {
    color: #ff6b00;
}

.bm-banker-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: #ff6b00;
    border-radius: 50%;
    font-size: 11px;
    color: #fff;
    flex-shrink: 0;
}

.bm-match-time {
    width: 60px;
    font-size: 12px;
    color: #888;
    font-weight: 500;
}

.bm-match-teams {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.bm-match-team {
    display: flex;
    align-items: center;
    min-width: 0;
}

.bm-team-logo-tiny {
    width: 18px;
    height: 18px;
    object-fit: contain;
    margin-right: 10px;
}

.bm-team-name-text {
    font-size: 13px;
    font-weight: 500;
    color: #eee;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.bm-match-score {
    margin-left: auto;
    font-weight: 700;
    color: #eee;
    font-size: 14px;
    padding: 0 10px;
}

.bm-match-stats {
    display: flex;
    align-items: center;
    width: auto;
    min-width: 240px;
    margin-right: 5px;
}

.bm-stat-col {
    flex: 1;
    text-align: center;
    font-size: 12px;
    color: #aaa;
}

.bm-prob-col {
    font-weight: 600;
}

.bm-prob-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.bm-prob-badge.prob-high {
    background: rgba(40, 167, 69, 0.2);
    color: #ffffff;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.bm-prob-badge.prob-med {
    background: rgba(255, 193, 7, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.bm-prob-badge.prob-low {
    background: rgba(220, 53, 69, 0.2);
    color: #ffffff;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.bm-market-col {
    font-size: 11px;
    color: #888;
}

.bm-tip-badge {
    background: rgba(255, 255, 255, 0.1);
    color: #eee;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
}

.bm-odd-col {
    font-weight: 700;
    color: #eee;
}

.bm-form-badges {
    display: inline-flex;
    gap: 2px;
    margin-left: 6px;
    flex-shrink: 0;
}

.bm-form-badge {
    width: 16px;
    height: 16px;
    border-radius: 20%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}

.bm-form-w {
    background: #28a745;
}

.bm-form-d {
    background: #ffc107;
}

.bm-form-l {
    background: #fb061fff;
}

@media (max-width: 768px) {
    .bm-match-row-premium {
        flex-direction: row;
        align-items: center;
        padding: 10px 8px;
        gap: 6px;
    }
    .bm-match-star {
        display: none;
    }
    .bm-match-main {
        flex-direction: row;
        align-items: center;
        flex: 1;
        gap: 8px;
    }
    .bm-match-time {
        width: 35px;
        font-size: 11px;
        color: #aaa;
        padding: 0;
        margin: 0;
        border: none;
        text-align: left;
    }
    .bm-match-teams {
        flex: 1;
        gap: 4px;
        width: auto;
    }
    .bm-team-logo-tiny {
        width: 14px;
        height: 14px;
        margin-right: 6px;
    }
    .bm-team-name-text {
        font-size: 13px;
    }
    .bm-match-score {
        font-size: 14px;
        padding: 0 4px;
    }
    .bm-form-badge {
        width: 12px;
        height: 12px;
        font-size: 6px;
    }
    .bm-form-badges {
        display: none;
    }
    .bm-match-stats {
        width: auto;
        min-width: 65px;
        margin: 0;
        padding: 0;
        background: transparent;
        border: none;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        gap: 4px;
    }
    .bm-stat-col {
        flex: unset;
        width: 100%;
        text-align: right;
    }
    .bm-prob-badge, .bm-tip-badge {
        font-size: 10px;
        padding: 2px 6px;
    }
    .bm-odd-col {
        font-size: 13px;
        font-weight: 700;
        color: #ffdf00;
    }
    .bm-mobile-status-middle {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
    }
}
</style>
@endonce
@endpush

@push('styles')
@once
<style>
.expert-betting-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    margin-bottom: 20px;
}

.expert-betting-card {
    background: #ffffff;
    border-radius: 12px;
    /* overflow: hidden; */
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.12);
    border: 2px solid #e0e0e0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.expert-betting-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
    transform: translateY(-3px);
    border-color: #092337;
}

/* Teams Section */
.expert-card-teams {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 10px 16px;
    position: relative;
    text-align: center;
    border-bottom: 2px solid #dee2e6;
}

.expert-status-absolute {
    position: absolute;
    top: 10px;
    right: 15px;
    z-index: 2;
}

.expert-time-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(9, 35, 55, 0.1);
    backdrop-filter: blur(10px);
    padding: 4px 10px;
    border-radius: 20px;
    color: #092337;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 8px;
    border: 1px solid rgba(9, 35, 55, 0.2);
}

.expert-teams-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.expert-team {
    flex: 1;
    min-width: 0;
}

.expert-team-name {
    color: #1a1a1a;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.expert-team-form {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    justify-content: center;
}

.expert-form-badge {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    color: #ffffff;
}

.expert-form-w {
    background: #28a745;
}

.expert-form-d {
    background: #14588B;
}

.expert-form-l {
    background: #dc3545;
}

.expert-vs {
    flex-shrink: 0;
    width: 35px;
    height: 35px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C1392A;
    font-weight: 700;
    font-size: 0.7rem;
    letter-spacing: 1px;
    border: 2px solid #C1392A;
}

/* Info Section */
.expert-card-info {
    background: #f8f9fa;
    padding: 0;
}

.expert-info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}

.expert-info-item {
    padding: 10px 8px;
    text-align: center;
    border-right: 1px solid #e9ecef;
    background: #ffffff;
}

.expert-info-item:last-child {
    border-right: none;
}

.expert-info-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: #6c757d;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.expert-info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a1a1a;
}

.expert-odds {
    color: #092337;
    font-size: 1.05rem;
    font-weight: 700;
}

.expert-tip {
    color: #14588B;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}

.expert-tip-section {
    grid-column: span 2;
}

.expert-probability {
    position: relative;
}

.expert-prob-value {
    color: #14588B;
    font-size: 0.95rem;
    font-weight: 700;
}

.expert-status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.expert-score-badge {
    font-size: 0.85rem;
    padding: 4px 12px;
    text-transform: none;
    letter-spacing: normal;
    border-radius: 6px;
}

.expert-status-won {
    background: #28a745;
    color: #ffffff;
}

.expert-status-lost {
    background: #dc3545;
    color: #ffffff;
}

.expert-bookmaker-img {
    max-height: 28px;
    max-width: 80px;
    object-fit: contain;
}

.expert-bookmaker-text {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .expert-card-teams {
        padding: 12px 15px;
    }
    
    .expert-team-name {
        font-size: 0.9rem;
    }
    
    .expert-teams-container {
        gap: 10px;
    }
    
    .expert-vs {
        width: 35px;
        height: 35px;
        font-size: 0.7rem;
    }
    
    .expert-info-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .expert-tip-section {
        grid-column: span 1; /* Reset span to 1 */
    }
    
    .expert-info-item {
        padding: 8px 4px; /* Reduce padding to fit */
        border-bottom: none; /* No bottom border needed for single row */
        border-right: 1px solid #e9ecef;
    }

    .expert-info-item:last-child {
        border-right: none;
    }

    /* Remove specific nth-child border rules from previous 2-col layout */
    .expert-info-item:nth-child(odd),
    .expert-info-item:nth-child(even),
    .expert-info-item:nth-last-child(-n+2) {
        border-right: 1px solid #e9ecef;
        border-bottom: none;
    }
    
    .expert-info-item:last-child {
        border-right: none;
    }
}

@media (max-width: 576px) {
    .expert-team-name {
        font-size: 0.85rem;
    }
    
    .expert-form-badge {
        width: 20px;
        height: 20px;
        font-size: 0.65rem;
    }
    
    .expert-info-value {
        font-size: 0.9rem;
    }
    
    .expert-odds {
        font-size: 1.1rem;
    }
}
</style>
@endonce
@endpush