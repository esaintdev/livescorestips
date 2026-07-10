@extends('admin.layout')

@section('title', 'Create Prediction')
@section('page-title', 'Create New Prediction')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Search Fixtures</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Search By</label>
                        <select id="search-type" class="form-control">
                            <option value="date">Specific Date</option>
                            <option value="month">Entire Month (League required)</option>
                        </select>
                    </div>
                    <div class="form-group" id="date-group">
                        <label>Date</label>
                        <input type="date" id="fixture-date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group" id="month-group" style="display: none;">
                        <label>Month</label>
                        <input type="month" id="fixture-month" class="form-control" value="{{ now()->format('Y-m') }}">
                    </div>
                    <div class="form-group">
                        <label>League</label>
                        <select id="league-select" class="form-control">
                            <option value="">All Leagues</option>
                            @foreach($leagues as $league)
                                <option value="{{ $league['id'] }}">{{ $league['name'] }} ({{ $league['country'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="search-fixtures" class="btn btn-primary btn-block">
                        <i class="fa fa-search"></i> Search Fixtures
                    </button>
                    <div id="loading" class="text-center mt-3" style="display: none;">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>

            <div id="fixtures-list" class="mt-3"></div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Prediction Details</h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.predictions.store') }}" method="POST" id="prediction-form">
                        @csrf
                        <input type="hidden" name="fixture_id" id="fixture_id">
                        <input type="hidden" name="league_id" id="league_id">
                        <input type="hidden" name="league_name" id="league_name">
                        <input type="hidden" name="league_country" id="league_country">
                        <input type="hidden" name="league_logo" id="league_logo">
                        <input type="hidden" name="home_team" id="home_team">
                        <input type="hidden" name="home_team_logo" id="home_team_logo">
                        <input type="hidden" name="away_team" id="away_team">
                        <input type="hidden" name="away_team_logo" id="away_team_logo">
                        <input type="hidden" name="match_date" id="match_date">
                        <input type="hidden" name="match_time" id="match_time">
                        <input type="hidden" name="home_form" id="home_form">
                        <input type="hidden" name="away_form" id="away_form">

                        <div id="match-info" class="alert" style="display: none; background: rgba(255, 107, 0, 0.05); border: 1px solid rgba(255, 107, 0, 0.1);">
                            <h6 id="match-teams" style="color: #ff6b00 !important;"></h6>
                            <small id="match-details" class="text-muted"></small>

                            <div id="team-form-display" class="mt-3" style="display: none;">
                                <div class="row">
                                    <div class="col-6">
                                        <strong id="home-team-name" style="color: #fff !important;"></strong>
                                        <div id="home-form-badges" class="mt-1"></div>
                                    </div>
                                    <div class="col-6">
                                        <strong id="away-team-name" style="color: #fff !important;"></strong>
                                        <div id="away-form-badges" class="mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tip/Prediction *</label>
                            <select name="tip" id="tip-select" class="form-control" required>
                                <option value="">Select tip type...</option>
                                <option value="Over 0.5">Over 0.5</option>
                                <option value="Over 1.5">Over 1.5</option>
                                <option value="Over 2.5">Over 2.5</option>
                                <option value="Over 3.5">Over 3.5</option>
                                <option value="Under 1.5">Under 1.5</option>
                                <option value="Under 2.5">Under 2.5</option>
                                <option value="BTTS/GG">BTTS/GG</option>
                                <option value="Home Win">Home Win</option>
                                <option value="Away Win">Away Win</option>
                                <option value="Draw">Draw</option>
                                <option value="1X">1X (Home/Draw)</option>
                                <option value="12">12 (Any Win)</option>
                                <option value="X2">X2 (Away/Draw)</option>
                                <option value="Double Chance">Double Chance</option>
                                <option value="Home Win Either Half">Home Win Either Half</option>
                                <option value="Away Win Either Half">Away Win Either Half</option>
                            </select>
                            <small class="text-muted">Odds will be fetched automatically from API</small>
                        </div>

                        <div class="card bg-dark border-secondary mb-3">
                            <div class="card-body">
                                <div class="form-group mb-2">
                                    <label class="text-warning"><i class="fa fa-bullseye mr-1"></i> Correct Score (Exact)</label>
                                    <input type="text" name="correct_score" id="correct_score" class="form-control"
                                        placeholder="e.g. 2-1">
                                    <small class="text-muted">Fill this if you want to predict exact score. Overrides the tip dropdown above.</small>
                                </div>
                                <div class="mt-2">
                                    <label class="text-muted small mb-1">Quick select:</label>
                                    <div>
                                        <span class="cs-btn" data-score="1-0">1-0</span>
                                        <span class="cs-btn" data-score="2-0">2-0</span>
                                        <span class="cs-btn" data-score="2-1">2-1</span>
                                        <span class="cs-btn" data-score="3-0">3-0</span>
                                        <span class="cs-btn" data-score="3-1">3-1</span>
                                        <span class="cs-btn" data-score="3-2">3-2</span>
                                        <span class="cs-btn" data-score="4-0">4-0</span>
                                        <span class="cs-btn" data-score="4-1">4-1</span>
                                        <span class="cs-btn" data-score="4-2">4-2</span>
                                        <span class="cs-btn" data-score="0-0">0-0</span>
                                        <span class="cs-btn" data-score="1-1">1-1</span>
                                        <span class="cs-btn" data-score="2-2">2-2</span>
                                        <span class="cs-btn" data-score="3-3">3-3</span>
                                        <span class="cs-btn" data-score="0-1">0-1</span>
                                        <span class="cs-btn" data-score="0-2">0-2</span>
                                        <span class="cs-btn" data-score="0-3">0-3</span>
                                        <span class="cs-btn" data-score="1-2">1-2</span>
                                        <span class="cs-btn" data-score="1-3">1-3</span>
                                        <span class="cs-btn" data-score="2-3">2-3</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Odds *</label>
                                    <input type="number" step="0.01" name="odds" id="odds" class="form-control" readonly
                                        required>
                                    <small class="text-muted" id="odds-source"></small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Calculated Probability</label>
                                    <input type="text" id="calc-probability" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                            </option>
                                            <option value="won" {{ old('status') == 'won' ? 'selected' : '' }}>Won</option>
                                            <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Visibility</label>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" name="is_featured" class="form-check-input"
                                                id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured Prediction (Show in
                                                Carousel)</label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" name="is_banker" class="form-check-input"
                                                id="is_banker" value="1" {{ old('is_banker') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_banker">🏦 Banker Tip (High Confidence)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="form-group">
                                                            <label>Admin Note (Internal Reason)</label>
                                                            <textarea name="admin_note" class="form-control" rows="3"
                                                                placeholder="Why this prediction?">{{ old('admin_note') }}</textarea>
                                                        </div> -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category" class="form-control">
                                        <option value="">Select a category...</option>
                                        <option value="Draw" {{ old('category') == 'Draw' ? 'selected' : '' }}>Draw</option>
                                    </select>
                                    <small class="text-muted">Category for Telegram filtering and website category
                                        pages</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                            <i class="fa fa-save"></i> Create Prediction
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>

                <!-- <div class="form-group">
                                                    <label>Bookmaker Name</label>
                                                    <input type="text" name="bookmaker_name" id="bookmaker_name" class="form-control" readonly>
                                                </div>

                                                <div class="form-group">
                                                    <label>Bookmaker URL</label>
                                                    <input type="url" name="bookmaker_url" id="bookmaker_url" class="form-control" readonly>
                                                    <input type="hidden" name="bookmaker_image" id="bookmaker_image">
                                                </div> -->



                </form>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Auto-fill form if fixture data is provided
            @if(isset($fixtureData) && $fixtureData)
                const fixtureData = @json($fixtureData);
                console.log('Auto-filling form with fixture data:', fixtureData);

                // Fill hidden and visible fields
                $('#fixture_id').val(fixtureData.fixture.id);
                $('#league_id').val(fixtureData.league.id);
                $('#league_name').val(fixtureData.league.name);
                $('#league_country').val(fixtureData.league.country);
                $('#league_logo').val(fixtureData.league.logo);
                $('#home_team').val(fixtureData.teams.home.name);
                $('#home_team_logo').val(fixtureData.teams.home.logo);
                $('#away_team').val(fixtureData.teams.away.name);
                $('#away_team_logo').val(fixtureData.teams.away.logo);
                $('#match_date').val(fixtureData.fixture.date.split('T')[0]);

                // Format and set match time
                const matchDate = new Date(fixtureData.fixture.date);
                const hours = String(matchDate.getHours()).padStart(2, '0');
                const minutes = String(matchDate.getMinutes()).padStart(2, '0');
                $('#match_time').val(`${hours}:${minutes}`);

                // Display match info in the selected fixture area
                const fixtureHtml = `
                                                                                                                    <div class="alert alert-info">
                                                                                                                        <h6>Selected Fixture</h6>
                                                                                                                        <p class="mb-0">
                                                                                                                            <strong>${fixtureData.teams.home.name}</strong> vs <strong>${fixtureData.teams.away.name}</strong><br>
                                                                                                                            ${fixtureData.league.name} • ${matchDate.toLocaleString()}
                                                                                                                        </p>
                                                                                                                    </div>
                                                                                                                `;
                $('#match-info').html(fixtureHtml).show(); // Assuming #match-info is the display area

                // Fetch team form
                // Assuming fetchTeamForms function handles both home and away
                fetchTeamForms(fixtureData.teams.home.id, fixtureData.teams.away.id, fixtureData.teams.home.name, fixtureData.teams.away.name);

                // Fetch odds
                fetchOddsForFixture(fixtureData.fixture.id);

                // Enable submit button
                $('#submit-btn').prop('disabled', false);

                // Scroll to prediction form
                $('html, body').animate({
                    scrollTop: $('#prediction-form').offset().top - 100
                }, 500);
            @endif

            // Calculate probability when odds change
            $('#odds').on('input', function () {
                const odds = parseFloat($(this).val());
                if (odds > 0) {
                    const probability = ((1 / odds) * 100).toFixed(2);
                    $('#calc-probability').val(probability + '%');
                }
            });

            // Toggle search type
            $('#search-type').on('change', function() {
                if ($(this).val() === 'date') {
                    $('#date-group').show();
                    $('#month-group').hide();
                } else {
                    $('#date-group').hide();
                    $('#month-group').show();
                }
            });
            // trigger on load
            $('#search-type').trigger('change');

            // Search fixtures
            $('#search-fixtures').click(function () {
                const searchType = $('#search-type').val();
                const leagueId = $('#league-select').val();
                let dateStr = '';

                if (searchType === 'date') {
                    dateStr = $('#fixture-date').val();
                } else {
                    dateStr = $('#fixture-month').val();
                    if (!leagueId) {
                        alert("You must select a League when searching by Entire Month.");
                        return;
                    }
                }

                $('#loading').show();
                $('#fixtures-list').html('');

                $.post('{{ route("admin.fetch-fixtures") }}', {
                    _token: '{{ csrf_token() }}',
                    date: dateStr,
                    league_id: leagueId
                }, function (response) {
                    $('#loading').hide();
                    console.log('API Response:', response);

                    if (response.fixtures && response.fixtures.length > 0) {
                        let html = '';
                        response.fixtures.forEach(function (fixture) {
                            html += `
                                                                                                                <div class="card mb-2 fixture-item" style="cursor: pointer;" data-fixture='${JSON.stringify(fixture)}'>
                                                                                                                    <div class="card-body p-2">
                                                                                                                        <small class="text-muted">${fixture.league.name}</small>
                                                                                                                        <div><strong>${fixture.teams.home.name}</strong> vs <strong>${fixture.teams.away.name}</strong></div>
                                                                                                                        <small>${new Date(fixture.fixture.date).toLocaleString()}</small>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            `;
                        });
                        $('#fixtures-list').html(html);

                        // Handle fixture selection
                        $('.fixture-item').click(function () {
                            const fixture = $(this).data('fixture');
                            selectFixture(fixture);
                        });
                    } else {
                        $('#fixtures-list').html('<div class="alert alert-warning">No fixtures found for this date/league. Try a different date or league.</div>');
                    }
                }).fail(function (xhr, status, error) {
                    $('#loading').hide();
                    console.error('AJAX Error:', xhr.responseText);
                    let errorMsg = 'Error fetching fixtures. ';
                    if (xhr.status === 500) {
                        errorMsg += 'Server error. Check API configuration.';
                    } else if (xhr.status === 0) {
                        errorMsg += 'Network error. Check your connection.';
                    } else {
                        errorMsg += 'Status: ' + xhr.status;
                    }
                    $('#fixtures-list').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                });
            });

            function selectFixture(fixture) {
                $('#fixture_id').val(fixture.fixture.id);
                $('#league_id').val(fixture.league.id);
                $('#league_name').val(fixture.league.name);
                $('#league_country').val(fixture.league.country);
                $('#league_logo').val(fixture.league.logo);
                $('#home_team').val(fixture.teams.home.name);
                $('#home_team_logo').val(fixture.teams.home.logo);
                $('#away_team').val(fixture.teams.away.name);
                $('#away_team_logo').val(fixture.teams.away.logo);
                $('#match_date').val(fixture.fixture.date);
                $('#match_time').val(new Date(fixture.fixture.date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false }));

                $('#match-teams').text(fixture.teams.home.name + ' vs ' + fixture.teams.away.name);
                $('#match-details').text(fixture.league.name + ' • ' + new Date(fixture.fixture.date).toLocaleString());
                $('#match-info').show();
                $('#submit-btn').prop('disabled', false);

                $('.fixture-item').removeClass('border-primary');
                $(event.currentTarget).addClass('border-primary');

                // Fetch odds and team forms
                fetchOddsForFixture(fixture.fixture.id);
                fetchTeamForms(fixture.teams.home.id, fixture.teams.away.id, fixture.teams.home.name, fixture.teams.away.name);
            }

            function fetchTeamForms(homeTeamId, awayTeamId, homeTeamName, awayTeamName) {
                console.log('Fetching team forms...');

                // Set team names
                $('#home-team-name').text(homeTeamName);
                $('#away-team-name').text(awayTeamName);

                // Fetch home team form
                $.post('{{ route("admin.fetch-team-form") }}', {
                    _token: '{{ csrf_token() }}',
                    team_id: homeTeamId
                }, function (response) {
                    if (response.form && response.form.length > 0) {
                        displayTeamForm('#home-form-badges', response.form);
                        $('#home_form').val(JSON.stringify(response.form));
                    }
                });

                // Fetch away team form
                $.post('{{ route("admin.fetch-team-form") }}', {
                    _token: '{{ csrf_token() }}',
                    team_id: awayTeamId
                }, function (response) {
                    if (response.form && response.form.length > 0) {
                        displayTeamForm('#away-form-badges', response.form);
                        $('#away_form').val(JSON.stringify(response.form));
                        $('#team-form-display').show();
                    }
                });
            }

            function displayTeamForm(containerId, form) {
                let html = '';
                form.forEach(function (result) {
                    let badgeClass = '';
                    if (result === 'W') {
                        badgeClass = 'badge-success';
                    } else if (result === 'L') {
                        badgeClass = 'badge-danger';
                    } else {
                        badgeClass = 'badge-secondary';
                    }
                    html += `<span class="badge ${badgeClass} mr-1">${result}</span>`;
                });
                $(containerId).html(html);
            }

            let fixtureOddsData = null; // Store odds data globally

            function fetchOddsForFixture(fixtureId) {
                console.log('Fetching odds for fixture:', fixtureId);

                $.post('{{ route("admin.fetch-odds") }}', {
                    _token: '{{ csrf_token() }}',
                    fixture_id: fixtureId
                }, function (response) {
                    console.log('Odds response:', response);

                    if (response.odds && response.odds.length > 0) {
                        fixtureOddsData = response.odds[0]; // Store for later use
                        console.log('Odds data stored. Select a tip to see odds.');
                        $('#odds-source').text('Select a tip type to load odds');
                    } else {
                        console.log('No odds available for this fixture');
                        $('#odds-source').text('No odds available from API');
                    }
                }).fail(function (xhr) {
                    console.error('Error fetching odds:', xhr.responseText);
                });
            }

            function populateOdds(tip) {
                if (!tip) return;
                if (!fixtureOddsData) {
                    $('#odds').prop('readonly', false).val('');
                    $('#odds-source').text('Manual entry mode (No API odds found)');
                    return;
                }
                const oddsInfo = findOddsForTip(tip, fixtureOddsData);
                if (oddsInfo) {
                    $('#odds').val(oddsInfo.odd).prop('readonly', true).trigger('input');
                    $('#bookmaker_name').val(oddsInfo.bookmaker);
                    $('#bookmaker_url').val(oddsInfo.url || '');
                    if (oddsInfo.bookmaker_id) {
                        const numericId = parseInt(oddsInfo.bookmaker_id);
                        const logoUrl = `https://media.api-sports.io/bookmakers/${numericId}.png`;
                        $('#bookmaker_image').val(logoUrl);
                        $('#odds-source').html(`<img src="${logoUrl}" height="20" class="mr-1"> From ${oddsInfo.bookmaker} - ${oddsInfo.market}`);
                    } else {
                        $('#bookmaker_image').val('');
                        $('#odds-source').text(`From ${oddsInfo.bookmaker} - ${oddsInfo.market}`);
                    }
                } else {
                    $('#odds').prop('readonly', false).val('');
                    $('#odds-source').text('No odds found for this tip. Manual entry enabled.');
                }
            }

            // Correct score button clicks
            $(document).on('click', '.cs-btn', function () {
                const score = $(this).data('score');
                $('#correct_score').val(score).removeClass('is-invalid').addClass('is-valid');
                $('#tip-select').val(score);
                populateOdds(score);
            });

            // When tip dropdown changes
            $(document).on('change', '#tip-select', function () {
                const val = $(this).val();
                if (val && /^\d+-\d+$/.test(val)) {
                    $('#correct_score').val(val).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#correct_score').val('').removeClass('is-valid is-invalid');
                }
                populateOdds(val);
            });

            // Manual correct score typing
            $(document).on('input', '#correct_score', function () {
                const val = $(this).val().trim();
                if (val && /^\d+-\d+$/.test(val)) {
                    $('#tip-select').val(val);
                    $(this).removeClass('is-invalid').addClass('is-valid');
                    populateOdds(val);
                } else if (val) {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                } else {
                    $(this).removeClass('is-invalid is-valid');
                }
            });

            function findOddsForTip(tip, oddsData) {
                if (!oddsData.bookmakers || oddsData.bookmakers.length === 0) {
                    return null;
                }

                const bookmaker = oddsData.bookmakers[0];
                const bets = bookmaker.bets;
                const bookmakerId = bookmaker.id;

                // Map tip types to API market names and values
                // We use arrays for 'markets' to handle API variations (e.g. "Both Teams Score" vs "Both Teams to Score")
                const tipMapping = {
                    'Over 0.5': { markets: ['Goals Over/Under'], value: 'Over 0.5' },
                    'Over 1.5': { markets: ['Goals Over/Under'], value: 'Over 1.5' },
                    'Over 2.5': { markets: ['Goals Over/Under'], value: 'Over 2.5' },
                    'Over 3.5': { markets: ['Goals Over/Under'], value: 'Over 3.5' },
                    'Under 1.5': { markets: ['Goals Over/Under'], value: 'Under 1.5' },
                    'Under 2.5': { markets: ['Goals Over/Under'], value: 'Under 2.5' },
                    'BTTS/GG': { markets: ['Both Teams Score', 'Both Teams to Score', 'BTTS'], value: 'Yes' },
                    'Home Win': { markets: ['Match Winner'], value: 'Home' },
                    'Away Win': { markets: ['Match Winner'], value: 'Away' },
                    'Draw': { markets: ['Match Winner'], value: 'Draw' },
                    '1X': { markets: ['Double Chance'], value: 'Home/Draw' },
                    'X2': { markets: ['Double Chance'], value: 'Draw/Away' },
                    '12': { markets: ['Double Chance'], value: 'Home/Away' },
                    'Double Chance': { markets: ['Double Chance'], value: 'Home/Draw' },
                    'Home Win Either Half': { markets: ['Home/Away'], value: 'Home' },
                    'Away Win Either Half': { markets: ['Home/Away'], value: 'Away' },
                };

                let mapping = tipMapping[tip];

                // Correct score: convert "1-0" to "Correct Score" market with "1:0" value
                if (!mapping && /^\d+-\d+$/.test(tip)) {
                    const apiValue = tip.replace('-', ':');
                    mapping = { markets: ['Correct Score'], value: apiValue };
                }

                if (!mapping) {
                    console.log('No mapping found for tip:', tip);
                    return null;
                }

                // Find the matching bet by checking all possible market names
                for (let bet of bets) {
                    if (mapping.markets.includes(bet.name)) {
                        // Find the matching value
                        for (let value of bet.values) {
                            if (value.value === mapping.value) {
                                return {
                                    odd: value.odd,
                                    bookmaker: bookmaker.name,
                                    bookmaker_id: bookmakerId,
                                    url: `https://${bookmaker.name.toLowerCase().replace(/\s+/g, '')}.com`,
                                    market: `${bet.name} - ${value.value}`
                                };
                            }
                        }
                    }
                }

                console.log('No odds found for tip:', tip, 'in markets:', mapping.markets);
                return null;
            }
        });
    </script>
@endpush
@push('styles')
    <style>
        /* Fixture items in search results */
        .fixture-item {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid #2d2d2d !important;
            transition: all 0.3s !important;
            border-radius: 12px !important;
        }

        .fixture-item:hover {
            border-color: #ff6b00 !important;
            background: rgba(255, 107, 0, 0.05) !important;
            transform: translateX(4px) !important;
        }

        .fixture-item.border-primary {
            border-color: #ff6b00 !important;
            border-width: 2px !important;
            background: rgba(255, 107, 0, 0.08) !important;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
        }

        .badge-secondary {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #888 !important;
            border: 1px solid #2d2d2d !important;
        }
        .cs-btn {
            display: inline-block;
            padding: 4px 12px;
            margin: 3px;
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 6px;
            color: #ccc;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        .cs-btn:hover {
            background: #ff6b00;
            border-color: #ff6b00;
            color: #fff;
        }
    </style>
@endpush