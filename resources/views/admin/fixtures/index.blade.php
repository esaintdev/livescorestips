@extends('admin.layout')

@section('page-title', 'Fixtures')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-calendar"></i> Fixtures for {{ \Carbon\Carbon::parse($date)->format('D, M j, Y') }}
                    </h5>
                    <div class="date-navigation">
                        <a href="{{ route('admin.fixtures.index', ['date' => $previousDate]) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-chevron-left"></i> Previous Day
                        </a>
                        <a href="{{ route('admin.fixtures.index') }}" class="btn btn-sm btn-primary mx-2">
                            <i class="fa fa-calendar-o"></i> Today
                        </a>
                        <a href="{{ route('admin.fixtures.index', ['date' => $nextDate]) }}"
                            class="btn btn-sm btn-outline-primary">
                            Next Day <i class="fa fa-chevron-right"></i>
                        </a>
                        <button onclick="refreshFixtures()" class="btn btn-sm btn-success ml-2">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="search-container">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="fixtureSearch" class="form-control"
                                placeholder="Search fixtures by team name..." autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                    <i class="fa fa-times"></i> Clear
                                </button>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fa fa-info-circle"></i> Type to search for teams (e.g., "Arsenal", "Barcelona")
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @php
        // Define top European leagues (in priority order)
        $topLeagues = [
            39 => 1,   // Premier League (England)
            140 => 2,  // La Liga (Spain)
            135 => 3,  // Serie A (Italy)
            78 => 4,   // Bundesliga (Germany)
            61 => 5,   // Ligue 1 (France)
            94 => 6,   // Primeira Liga (Portugal)
            88 => 7,   // Eredivisie (Netherlands)
            203 => 8,  // Süper Lig (Turkey)
            144 => 9,  // Jupiler Pro League (Belgium)
            197 => 10, // Super League (Greece)
        ];

        // Group fixtures by league
        $groupedFixtures = [];
        foreach ($fixtures as $fixture) {
            $leagueId = $fixture['league']['id'] ?? 'unknown';
            if (!isset($groupedFixtures[$leagueId])) {
                $groupedFixtures[$leagueId] = [
                    'league' => $fixture['league'],
                    'fixtures' => [],
                    'priority' => $topLeagues[$leagueId] ?? 999 // Top leagues get low numbers, others get 999
                ];
            }
            $groupedFixtures[$leagueId]['fixtures'][] = $fixture;
        }

        // Sort by priority (top leagues first)
        uasort($groupedFixtures, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    @endphp

    @forelse($groupedFixtures as $leagueGroup)
        <div class="league-group mb-4">
            <div class="league-header">
                <img src="{{ $leagueGroup['league']['logo'] ?? '' }}" alt="{{ $leagueGroup['league']['name'] }}"
                    class="league-logo">
                <div class="league-info">
                    <h6 class="league-name">{{ $leagueGroup['league']['name'] ?? 'Unknown League' }}</h6>
                    <span class="league-country">{{ $leagueGroup['league']['country'] ?? '' }}</span>
                </div>
                <i class="fa fa-chevron-right"></i>
            </div>

            <div class="fixtures-list">
                @foreach($leagueGroup['fixtures'] as $fixture)
                    @php
                        $status = $fixture['fixture']['status']['short'] ?? 'NS';
                        $isFinished = in_array($status, ['FT', 'AET', 'PEN']);
                        $isLive = in_array($status, ['LIVE', '1H', 'HT', '2H']);
                    @endphp
                    <div class="fixture-item-wrapper">
                        <a href="{{ route('fixture.show', [$fixture['fixture']['id'], \Carbon\Carbon::parse($fixture['fixture']['date'])->format('Y-m-d')]) }}" class="fixture-item" target="_blank">
                            <div class="fixture-status">
                                @if($isFinished)
                                    <span class="status-text">FT</span>
                                @elseif($isLive)
                                    <span class="status-text live">LIVE</span>
                                @else
                                    <span
                                        class="status-time">{{ \Carbon\Carbon::parse($fixture['fixture']['date'])->format('H:i') }}</span>
                                @endif
                            </div>

                            <div class="fixture-teams">
                                <div class="team">
                                    <img src="{{ $fixture['teams']['home']['logo'] ?? '' }}"
                                        alt="{{ $fixture['teams']['home']['name'] }}" class="team-logo">
                                    <span class="team-name">{{ $fixture['teams']['home']['name'] }}</span>
                                </div>
                                <div class="team">
                                    <img src="{{ $fixture['teams']['away']['logo'] ?? '' }}"
                                        alt="{{ $fixture['teams']['away']['name'] }}" class="team-logo">
                                    <span class="team-name">{{ $fixture['teams']['away']['name'] }}</span>
                                </div>
                            </div>

                            <div class="fixture-score">
                                @if($isFinished || $isLive)
                                    <div class="score">{{ $fixture['goals']['home'] ?? 0 }}</div>
                                    <div class="score">{{ $fixture['goals']['away'] ?? 0 }}</div>
                                @else
                                    <div class="score-placeholder">-</div>
                                    <div class="score-placeholder">-</div>
                                @endif
                            </div>

                            <div class="fixture-favorite">
                                <i class="fa fa-star-o"></i>
                            </div>
                        </a>

                        <a href="{{ route('admin.predictions.create', ['fixture_id' => $fixture['fixture']['id']]) }}"
                            class="btn-predict">
                            <i class="fa fa-plus"></i> Predict
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle"></i> No fixtures found for this date.
            </div>
        </div>
    @endforelse

@endsection

@push('styles')
    <style>
        /* League Group */
        .league-group {
            background: #1a1a1a !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            border: 1px solid #2d2d2d !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
            margin-bottom: 24px !important;
        }

        .league-header {
            background: rgba(255, 255, 255, 0.03) !important;
            padding: 18px 24px !important;
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            border-bottom: 1px solid #2d2d2d !important;
        }

        .league-header .league-logo {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .league-header .league-name {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
            margin: 0 !important;
        }

        .league-header .league-country {
            color: #888 !important;
            font-size: 0.85rem !important;
        }

        /* Fixtures List */
        .fixture-item-wrapper {
            display: flex !important;
            align-items: center !important;
            border-bottom: 1px solid #2d2d2d !important;
            transition: all 0.2s !important;
        }

        .fixture-item-wrapper:last-child {
            border-bottom: none !important;
        }

        .fixture-item-wrapper:hover {
            background: rgba(255, 255, 255, 0.02) !important;
        }

        .fixture-item {
            display: flex !important;
            align-items: center !important;
            padding: 16px 24px !important;
            flex: 1 !important;
            text-decoration: none !important;
            color: inherit !important;
        }

        .status-text {
            color: #888 !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
        }

        .status-text.live {
            color: #ef4444 !important;
        }

        .status-time {
            color: #ff6b00 !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
        }

        .fixture-status {
            width: 60px;
            text-align: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .fixture-teams {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .team {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .team-logo {
            width: 24px !important;
            height: 24px !important;
            object-fit: contain !important;
        }

        .team-name {
            color: #e0e0e0 !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
        }

        .fixture-score {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 40px;
            text-align: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .score {
            color: #ffffff !important;
            font-size: 1.1rem !important;
            font-weight: 800 !important;
        }

        .score-placeholder {
            color: #444 !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
        }


        /* Modal Overrides */
        .modal-content {
            background: #1a1a1a !important;
            border: 1px solid #2d2d2d !important;
            color: #e0e0e0 !important;
        }

        .modal-header {
            border-bottom: 1px solid #2d2d2d !important;
            background: rgba(255,255,255,0.02) !important;
        }

        .modal-footer {
            border-top: 1px solid #2d2d2d !important;
            background: rgba(255,255,255,0.02) !important;
        }

        .match-preview {
            background: #0f0f0f !important;
            border: 1px solid #2d2d2d !important;
        }

        .team-display span {
            color: #ffffff !important;
        }

        .search-container .form-control {
            background: #1a1a1a !important;
            border-color: #2d2d2d !important;
            color: #ffffff !important;
        }
        .fixture-item-wrapper.hidden {
            display: none;
        }

        .league-group.hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .date-navigation {
                flex-direction: column;
                gap: 8px;
            }

            .date-navigation .btn {
                width: 100%;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }

            .fixture-item {
                padding: 10px 15px;
            }

            .team-name {
                font-size: 0.85rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function openPredictModal(event, button) {
            event.preventDefault();
            event.stopPropagation();

            // Get data from button attributes
            const fixtureId = button.getAttribute('data-fixture-id');
            const leagueName = button.getAttribute('data-league-name');
            const leagueCountry = button.getAttribute('data-league-country');
            const leagueLogo = button.getAttribute('data-league-logo');
            const homeTeam = button.getAttribute('data-home-team');
            const homeLogo = button.getAttribute('data-home-logo');
            const awayTeam = button.getAttribute('data-away-team');
            const awayLogo = button.getAttribute('data-away-logo');
            const matchDate = button.getAttribute('data-match-date');

            // Fill in fixture data
            $('#modal_fixture_id').val(fixtureId);
            $('#modal_league_name').val(leagueName);
            $('#modal_league_country').val(leagueCountry);
            $('#modal_league_logo').val(leagueLogo);
            $('#modal_home_team').val(homeTeam);
            $('#modal_away_team').val(awayTeam);
            $('#modal_match_date').val(matchDate);

            // Format time
            const matchDateTime = new Date(matchDate);
            const hours = String(matchDateTime.getHours()).padStart(2, '0');
            const minutes = String(matchDateTime.getMinutes()).padStart(2, '0');
            $('#modal_match_time').val(`${hours}:${minutes}`);

            // Display match info
            $('#modal_home_logo').attr('src', homeLogo);
            $('#modal_home_name').text(homeTeam);
            $('#modal_away_logo').attr('src', awayLogo);
            $('#modal_away_name').text(awayTeam);
            $('#modal_league_display').text(leagueName);
            $('#modal_time_display').text(matchDateTime.toLocaleString());

            // Reset form
            $('#modal_tip').val('');
            $('#modal_odds').val('');
            $('#modal_probability').val('');

            // Show modal
            $('#predictModal').modal('show');
        }
    </script>
    <script>
        // Search functionality
        $(document).ready(function () {
            $('#fixtureSearch').on('input', function () {
                const searchTerm = $(this).val().toLowerCase().trim();

                if (searchTerm === '') {
                    // Show all fixtures if search is empty
                    $('.fixture-item-wrapper').removeClass('hidden');
                    $('.league-group').removeClass('hidden');
                    return;
                }

                let visibleLeagues = [];

                // Loop through each fixture
                $('.fixture-item-wrapper').each(function () {
                    const homeTeam = $(this).find('.team-name').eq(0).text().toLowerCase();
                    const awayTeam = $(this).find('.team-name').eq(1).text().toLowerCase();

                    // Check if search term matches either team
                    if (homeTeam.includes(searchTerm) || awayTeam.includes(searchTerm)) {
                        $(this).removeClass('hidden');
                        // Track which league this fixture belongs to
                        const leagueGroup = $(this).closest('.league-group');
                        if (!visibleLeagues.includes(leagueGroup[0])) {
                            visibleLeagues.push(leagueGroup[0]);
                        }
                    } else {
                        $(this).addClass('hidden');
                    }
                });

                // Hide/show league groups based on whether they have visible fixtures
                $('.league-group').each(function () {
                    if (visibleLeagues.includes(this)) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });
            });
        });

        function clearSearch() {
            $('#fixtureSearch').val('').trigger('input');
        }

        // Calculate probability when odds change
        $(document).ready(function () {
            $('#modal_odds').on('input', function () {
                const odds = parseFloat($(this).val());
                if (odds > 0) {
                    const probability = ((1 / odds) * 100).toFixed(2);
                    $('#modal_probability').val(probability + '%');
                } else {
                    $('#modal_probability').val('');
                }
            });
        });

        function refreshFixtures() {
            const currentDate = '{{ $date }}';
            const btn = event.target.closest('button');
            const icon = btn.querySelector('i');

            // Add spinning animation
            icon.classList.add('fa-spin');
            btn.disabled = true;

            // Make AJAX request to clear cache and reload
            fetch('{{ route("admin.fixtures.refresh") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ date: currentDate })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show fresh data
                        window.location.reload();
                    } else {
                        alert('Failed to refresh fixtures');
                        icon.classList.remove('fa-spin');
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while refreshing');
                    icon.classList.remove('fa-spin');
                    btn.disabled = false;
                });
        }
    </script>
@endpush