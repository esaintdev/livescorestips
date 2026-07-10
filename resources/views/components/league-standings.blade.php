@props(['leagueStandings'])

<div class="mb-5 tip-wrapper">
    <div class="tip-wrapper-inner active p-4">
        <div class="row mb-4">
            <div class="col-12">
                <span class="main-title">League Standings</span>
            </div>
        </div>

        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist" style="gap: 0.75rem;">
            @foreach($leagueStandings as $leagueName => $standings)
                <li class="nav-item" role="presentation">
                    <button class="nav-link league-tab-btn {{ $loop->first ? 'active' : '' }}"
                        id="pills-{{ Str::slug($leagueName) }}-tab" data-toggle="pill"
                        data-target="#pills-{{ Str::slug($leagueName) }}" type="button" role="tab"
                        aria-controls="pills-{{ Str::slug($leagueName) }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $leagueName }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="pills-tabContent">
            @foreach($leagueStandings as $leagueName => $standings)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-{{ Str::slug($leagueName) }}"
                    role="tabpanel" aria-labelledby="pills-{{ Str::slug($leagueName) }}-tab">

                    @if(empty($standings))
                        <div class="text-center py-4">
                            <p class="text-muted">Standings not available at the moment.</p>
                        </div>
                    @else
                        {{-- Modern Standings Header --}}
                        <div class="standings-header">
                            <div class="row text-center no-gutters">
                                <div class="col-2 col-md-1 standings-header-cell">#</div>
                                <div class="col-6 col-md-4 standings-header-cell text-left">Team</div>
                                <div class="col-1 standings-header-cell d-none d-lg-block">MP</div>
                                <div class="col-1 standings-header-cell d-none d-md-block">W</div>
                                <div class="col-1 standings-header-cell d-none d-md-block">D</div>
                                <div class="col-1 standings-header-cell d-none d-md-block">L</div>
                                <div class="col-2 col-md-1 standings-header-cell">GD</div>
                                <div class="col-2 col-md-1 standings-header-cell">Pts</div>
                                <div class="col-md-2 col-lg-1 standings-header-cell d-none d-md-block">Form</div>
                            </div>
                        </div>

                        {{-- Team Rows --}}
                        <div class="standings-body">
                            @foreach($standings as $team)
                                <div class="standings-row">
                                    <div class="row align-items-center no-gutters">
                                        {{-- Rank --}}
                                        <div class="col-2 col-md-1 text-center">
                                            <span class="rank-badge">{{ $team['rank'] }}</span>
                                        </div>

                                        {{-- Team --}}
                                        <div class="col-6 col-md-4 d-flex align-items-center team-cell">
                                            <img src="{{ $team['team']['logo'] }}" alt="{{ $team['team']['name'] }}"
                                                class="team-logo">
                                            <span class="team-name text-truncate">{{ $team['team']['name'] }}</span>
                                        </div>

                                        {{-- MP --}}
                                        <div class="col-1 text-center d-none d-lg-block stat-cell">{{ $team['all']['played'] }}
                                        </div>

                                        {{-- W --}}
                                        <div class="col-1 text-center d-none d-md-block stat-cell">{{ $team['all']['win'] }}</div>

                                        {{-- D --}}
                                        <div class="col-1 text-center d-none d-md-block stat-cell">{{ $team['all']['draw'] }}</div>

                                        {{-- L --}}
                                        <div class="col-1 text-center d-none d-md-block stat-cell">{{ $team['all']['lose'] }}</div>

                                        {{-- GD --}}
                                        <div class="col-2 col-md-1 text-center stat-cell">
                                            <span
                                                class="goal-diff {{ $team['goalsDiff'] > 0 ? 'positive' : ($team['goalsDiff'] < 0 ? 'negative' : '') }}">
                                                {{ $team['goalsDiff'] > 0 ? '+' : '' }}{{ $team['goalsDiff'] }}
                                            </span>
                                        </div>

                                        {{-- Points --}}
                                        <div class="col-2 col-md-1 text-center stat-cell">
                                            <span class="points-badge">{{ $team['points'] }}</span>
                                        </div>

                                        {{-- Form --}}
                                        <div class="col-md-2 col-lg-1 text-center form-cell d-none d-md-block">
                                            <div class="d-flex justify-content-center align-items-center">
                                                @foreach(array_slice(str_split($team['form'] ?? ''), -5) as $result)
                                                    <span class="form-badge form-{{ strtolower($result) }}">{{ $result }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    /* Tab Buttons */
    .nav-pills .nav-link {
        outline: none !important;
        box-shadow: none !important;
    }

    .league-tab-btn {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        color: #666;
        border-radius: 8px;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.3s ease;
        margin-right: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .league-tab-btn:hover {
        background-color: #e9ecef;
        color: #04145dff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(12, 122, 213, 0.15);
    }

    .league-tab-btn.active {
        background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 15px rgba(4, 20, 93, 0.3);
    }

    .league-tab-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(12, 122, 213, 0.2);
    }

    /* Standings Header */
    .standings-header {
        background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%);
        border-radius: 10px 10px 0 0;
        padding: 1rem 0.5rem;
        margin-bottom: 0;
    }

    .standings-header-cell {
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Standings Body */
    .standings-body {
        background: white;
        border-radius: 0 0 10px 10px;
        overflow: hidden;
    }

    .standings-row {
        padding: 1rem 0.5rem;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        position: relative;
    }

    .standings-row:last-child {
        border-bottom: none;
    }



    .standings-row.top-team {
        background: linear-gradient(90deg, rgba(12, 122, 213, 0.05) 0%, transparent 100%);
    }

    .standings-row.top-team::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%);
    }

    /* Team Cell */
    .team-cell {
        gap: 12px;
    }

    .team-logo {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }

    .team-name {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }

    /* Rank Badge */
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background-color: #f8f9fa;
        color: #666;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .rank-badge.rank-top {
        background: linear-gradient(135deg, #04145dff 0%, #0c7ad5ff 100%);
        color: white;
    }

    /* Stat Cells */
    .stat-cell {
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Goal Difference */
    .goal-diff {
        font-weight: 700;
        font-size: 0.9rem;
    }

    .goal-diff.positive {
        color: #10b981;
    }

    .goal-diff.negative {
        color: #ef4444;
    }

    /* Points Badge */
    .points-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 4px 8px;
        border-radius: 6px;
        background-color: #f0f9ff;
        color: #0c7ad5ff;
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* Form Badges */
    .form-cell {
        gap: 4px;
    }

    .form-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.7rem;
        margin: 0 2px;
    }

    .form-badge.form-w {
        background-color: #d1fae5;
        color: #10b981;
    }

    .form-badge.form-d {
        background-color: #fef3c7;
        color: #f59e0b;
    }

    .form-badge.form-l {
        background-color: #fee2e2;
        color: #ef4444;
    }

    /* Mobile Adjustments */
    @media (max-width: 768px) {
        .team-name {
            font-size: 0.85rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 150px;
        }

        .team-logo {
            width: 24px;
            height: 24px;
        }

        .rank-badge {
            width: 24px;
            height: 24px;
            font-size: 0.8rem;
        }

        .standings-row {
            padding: 0.75rem 0.25rem;
        }

        .standings-header {
            padding: 0.75rem 0.25rem;
        }

        .standings-header-cell {
            font-size: 0.75rem;
        }

        /* Compact League Tabs for Mobile */
        .league-tab-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .nav-pills {
            gap: 0.25rem !important;
        }
    }
</style>