@extends('layouts.app')

@section('title', $league['name'] . ' Predictions & Standings - ' . config('app.name'))
@section('description', $league['name'] . ' predictions, standings, and match results. Expert betting tips and odds for all upcoming ' . $league['name'] . ' fixtures from ' . config('app.name') . '.')
@section('keywords', $league['name'] . ', ' . ($league['country'] ?? 'football') . ' predictions, standings, betting tips, match results')

@section('content')
    {{-- League Header / Hero --}}
    <div class="bm-league-header-premium mb-4">
        <div class="bm-league-info-main">
            <div class="bm-league-logo-wrapper">
                <img src="{{ $league['logo'] }}" alt="{{ $league['name'] }}" class="bm-league-logo-img">
            </div>
            <div class="bm-league-text-group">
                <div class="bm-league-country-row">
                    <img src="{{ $league['flag'] ?? asset('images/default-flag.svg') }}" alt="{{ $league['country'] ?? $league['name'] }} flag" class="bm-country-flag-small">
                    <span>{{ $league['country'] }}</span>
                </div>
                <h1 class="bm-league-title-main">{{ $league['name'] }}: predictions, odds, and results</h1>
            </div>
        </div>
    </div>

    {{-- Market Filters --}}
    <div class="bm-market-filters-row mb-3">
        <div class="bm-market-filters-scroll custom-scrollbar-h">
            <button class="bm-market-btn active">All Tips</button>
            <button class="bm-market-btn">1X2</button>
            <button class="bm-market-btn">Both teams to score</button>
            <button class="bm-market-btn">Under/Over: 2.5</button>
            <button class="bm-market-btn">Double chance</button>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bm-search-bar-rounded mb-4">
        <div class="bm-search-inner">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Team, league or country" class="bm-search-input-field">
        </div>
    </div>

    {{-- Matches Section --}}
    <div class="bm-section-card mb-5">
        <div class="bm-section-header">
            <h2 class="bm-section-title">Matches {{ $league['name'] }}</h2>
            <div class="bm-match-headers hideSM">
                <span>Prob</span>
                <span>Tips</span>
                <span>Odd</span>
            </div>
        </div>

        <div class="bm-match-list-grouped">
            @php
                $allMatches = collect($results)->merge($upcoming)
                    ->filter(fn ($item) => !empty($item['fixture']['date']) && !empty($item['teams']))
                    ->sortBy('fixture.date');
                $grouped = $allMatches->groupBy(function($item) {
                    return \Carbon\Carbon::parse($item['fixture']['date'])->format('l / F j, Y');
                });
            @endphp

            @foreach($grouped as $dateLabel => $matches)
                <div class="bm-date-header">{{ $dateLabel }}</div>
                @foreach($matches as $fixture)
                    @php
                        $prediction = $predictions->firstWhere('fixture_id', $fixture['fixture']['id']);
                    @endphp
                    <x-betting-card
                        :matchUrl="route('fixture.show', [$prediction?->slug ?? $fixture['fixture']['id'], $prediction?->match_date?->format('Y-m-d') ?? \Carbon\Carbon::parse($fixture['fixture']['date'])->format('Y-m-d')])"
                        :time="\Carbon\Carbon::parse($fixture['fixture']['date'])->format('H:i')"
                        :homeTeam="$fixture['teams']['home']['name']"
                        :homeTeamLogo="$fixture['teams']['home']['logo']"
                        :awayTeam="$fixture['teams']['away']['name']"
                        :awayTeamLogo="$fixture['teams']['away']['logo']"
                        :homeScore="$fixture['goals']['home']"
                        :awayScore="$fixture['goals']['away']"
                        :status="$fixture['fixture']['status']['short']"
                        :probability="$prediction?->probability ?? 0"
                        :market="$prediction?->category ?? 'Full Time'"
                        :tip="$prediction?->tip ?? '-'"
                        :odds="$prediction?->odds ?? '-'"
                        :homeForm="$prediction?->home_form ?? null"
                        :awayForm="$prediction?->away_form ?? null"
                    />
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Ranking Section --}}
    <div class="bm-section-card mb-5">
        <div class="bm-section-header">
            <h2 class="bm-section-title">Ranking {{ $league['name'] }}</h2>
        </div>

        <div class="bm-ranking-tabs-wrapper">
            <div class="bm-ranking-tabs-main mb-3">
                <button class="bm-rank-tab active">Ranking</button>
                <button class="bm-rank-tab">Over/Under</button>
                <button class="bm-rank-tab">Over/Under Half Time</button>
                <button class="bm-rank-tab">Other</button>
            </div>

            <div class="bm-ranking-subtabs mb-4">
                <button class="bm-subtab active">Total</button>
                <button class="bm-subtab">Home</button>
                <button class="bm-subtab">Away</button>
            </div>

            <div class="table-responsive">
                <table class="table bm-table-ranking mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th class="text-left">Team</th>
                            <th class="text-center">MP</th>
                            <th class="text-center">W</th>
                            <th class="text-center">D</th>
                            <th class="text-center">L</th>
                            <th class="text-center">G</th>
                            <th class="text-center">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($standings as $row)
                            @continue(empty($row['team']))
                            @php
                                $rank = $row['rank'] ?? null;
                                $rankClass = '';
                                if ($rank !== null) {
                                    if ($rank <= 4) $rankClass = 'rank-cl';
                                    elseif ($rank == 5) $rankClass = 'rank-el';
                                    elseif ($rank >= 18) $rankClass = 'rank-rel';
                                }
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="bm-rank-indicator {{ $rankClass }}">{{ $rank ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="bm-rank-team">
                                        <img src="{{ $row['team']['logo'] ?? '' }}" alt="{{ $row['team']['name'] ?? '' }} logo" class="bm-team-logo-tiny">
                                        <span>{{ $row['team']['name'] ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">{{ $row['all']['played'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['all']['win'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['all']['draw'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['all']['lose'] ?? '-' }}</td>
                                <td class="text-center">{{ ($row['all']['goals']['for'] ?? '-') }}:{{ ($row['all']['goals']['against'] ?? '-') }}</td>
                                <td class="text-center font-weight-bold">{{ $row['points'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="bm-standings-legend-premium mt-3">
                <div class="bm-legend-item"><span class="bm-indicator rank-cl"></span> UEFA Champions League</div>
                <div class="bm-legend-item"><span class="bm-indicator rank-el"></span> UEFA Europa League</div>
                <div class="bm-legend-item"><span class="bm-indicator rank-rel"></span> Relegation</div>
            </div>
        </div>
    </div>

    <style>
        .bm-league-header-premium { padding: 20px; }
        .bm-league-info-main { display: flex; align-items: center; gap: 20px; }
        .bm-league-logo-wrapper {
            background: #fff; padding: 10px; border-radius: 50%; width: 80px; height: 80px;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .bm-league-logo-img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .bm-league-country-row { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #888; margin-bottom: 5px; }
        .bm-country-flag-small { width: 20px; height: 14px; object-fit: cover; border-radius: 2px; }
        .bm-league-title-main { font-size: 1.5rem; font-weight: 700; color: #fff; margin: 0; }

        .bm-market-filters-row { overflow: hidden; }
        .bm-market-filters-scroll {
            display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;
        }
        .bm-market-btn {
            background: rgba(255,255,255,0.08); border: none; color: #ccc;
            padding: 6px 16px; border-radius: 20px; font-size: 13px; white-space: nowrap; transition: all 0.2s;
        }
        .bm-market-btn.active, .bm-market-btn:hover { background: #fff; color: #000; }

        .bm-search-bar-rounded { max-width: 100%; }
        .bm-search-inner {
            position: relative; background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05); border-radius: 30px; padding: 8px 15px;
            display: flex; align-items: center;
        }
        .bm-search-inner i { color: #666; margin-right: 12px; }
        .bm-search-input-field {
            background: transparent; border: none; color: #fff; width: 100%; font-size: 14px;
        }
        .bm-search-input-field:focus { outline: none; }

        .bm-section-card {
            background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px; overflow: hidden;
        }
        .bm-section-header {
            padding: 15px 20px; background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: space-between;
        }
        .bm-section-title { font-size: 16px; font-weight: 700; margin: 0; color: #fff; }
        .bm-match-headers {
            display: flex; width: 245px; color: #666; font-size: 11px;
            text-transform: uppercase; font-weight: 600; padding-right: 15px;
        }
        .bm-match-headers span { flex: 1; text-align: center; }
        .bm-date-header {
            background: rgba(255,255,255,0.03); padding: 8px 20px; font-size: 12px;
            font-weight: 600; color: #888; border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .bm-ranking-tabs-main {
            display: flex; gap: 20px; padding: 0 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .bm-rank-tab {
            background: transparent; border: none; color: #888; padding: 10px 0;
            font-size: 14px; font-weight: 600; position: relative;
        }
        .bm-rank-tab.active { color: #fff; }
        .bm-rank-tab.active::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: #fff;
        }
        .bm-ranking-subtabs { display: flex; gap: 8px; padding: 0 20px; }
        .bm-subtab {
            background: rgba(255,255,255,0.05); border: none; color: #aaa;
            padding: 4px 16px; border-radius: 20px; font-size: 12px;
        }
        .bm-subtab.active { background: #fff; color: #000; }
        .bm-table-ranking { color: #ccc; font-size: 13px; }
        .bm-table-ranking th { border: none; color: #666; font-size: 11px; padding: 12px; }
        .bm-table-ranking td { border-top: 1px solid rgba(255,255,255,0.03); padding: 10px 12px; vertical-align: middle; }
        .bm-rank-indicator {
            display: inline-block; width: 24px; height: 24px; line-height: 24px;
            border-radius: 50%; font-size: 11px; font-weight: 700; background: #333; color: #fff;
        }
        .rank-cl { background: #28a745; }
        .rank-el { background: #007bff; }
        .rank-rel { background: #dc3545; }
        .bm-rank-team { display: flex; align-items: center; gap: 10px; }
        .bm-standings-legend-premium { padding: 0 20px 20px; display: flex; gap: 20px; flex-wrap: wrap; }
        .bm-legend-item { display: flex; align-items: center; gap: 8px; font-size: 11px; color: #888; }
        .bm-indicator { width: 10px; height: 10px; border-radius: 2px; }
    </style>
@endsection
