@extends('admin.layout')

@section('title', 'Edit Prediction')
@section('page-title', 'Edit Prediction')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Edit Prediction</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>{{ $prediction->home_team }} vs {{ $prediction->away_team }}</strong><br>
                        <small>{{ $prediction->league_name }} •
                            {{ \Carbon\Carbon::parse($prediction->match_date)->format('M d, Y H:i') }}</small>
                    </div>

                    <form action="{{ route('admin.predictions.update', $prediction) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Tip/Prediction *</label>
                            <select name="tip" id="tip-select" class="form-control" required>
                                <option value="">Select tip type...</option>
                                <option value="Over 1.5" {{ $prediction->tip === 'Over 1.5' ? 'selected' : '' }}>Over 1.5</option>
                                <option value="Over 2.5" {{ $prediction->tip === 'Over 2.5' ? 'selected' : '' }}>Over 2.5</option>
                                <option value="Over 3.5" {{ $prediction->tip === 'Over 3.5' ? 'selected' : '' }}>Over 3.5</option>
                                <option value="Under 1.5" {{ $prediction->tip === 'Under 1.5' ? 'selected' : '' }}>Under 1.5</option>
                                <option value="Under 2.5" {{ $prediction->tip === 'Under 2.5' ? 'selected' : '' }}>Under 2.5</option>
                                <option value="BTTS/GG" {{ $prediction->tip === 'BTTS/GG' ? 'selected' : '' }}>BTTS/GG (Both Teams To Score)</option>
                                <option value="NG" {{ $prediction->tip === 'NG' ? 'selected' : '' }}>NG (No Goal)</option>
                                <option value="Home Win" {{ $prediction->tip === 'Home Win' ? 'selected' : '' }}>Home Win</option>
                                <option value="Away Win" {{ $prediction->tip === 'Away Win' ? 'selected' : '' }}>Away Win</option>
                                <option value="Draw" {{ $prediction->tip === 'Draw' ? 'selected' : '' }}>Draw</option>
                                <option value="1X" {{ $prediction->tip === '1X' ? 'selected' : '' }}>1X (Home or Draw)</option>
                                <option value="12" {{ $prediction->tip === '12' ? 'selected' : '' }}>12 (Home or Away)</option>
                                <option value="X2" {{ $prediction->tip === 'X2' ? 'selected' : '' }}>X2 (Draw or Away)</option>
                                <option value="Double Chance" {{ $prediction->tip === 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                                <option value="Home Win Either Half" {{ $prediction->tip === 'Home Win Either Half' ? 'selected' : '' }}>Home Win Either Half</option>
                                <option value="Away Win Either Half" {{ $prediction->tip === 'Away Win Either Half' ? 'selected' : '' }}>Away Win Either Half</option>
                            </select>
                        </div>

                        <div class="card bg-dark border-secondary mb-3">
                            <div class="card-body">
                                <div class="form-group mb-2">
                                    <label class="text-warning"><i class="fa fa-bullseye mr-1"></i> Correct Score (Exact)</label>
                                    <input type="text" name="correct_score" id="correct_score" class="form-control"
                                        placeholder="e.g. 2-1"
                                        value="{{ preg_match('/^\d+-\d+$/', $prediction->tip) ? $prediction->tip : '' }}">
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

                        {{-- Category Moved to Bottom Row --}}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Odds *</label>
                                    <input type="number" step="0.01" name="odds" id="odds" class="form-control"
                                        value="{{ $prediction->odds }}" required>
                                    <small class="text-muted" id="odds-source">Probability will be auto-calculated</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Calculated Probability</label>
                                    <input type="text" id="calc-probability" class="form-control"
                                        value="{{ $prediction->probability }}%" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">Select a category...</option>
                                <option value="Draw" {{ old('category', $prediction->category) == 'Draw' ? 'selected' : '' }}>Draw</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Bookmaker Name</label>
                            <input type="text" name="bookmaker_name" class="form-control"
                                value="{{ $prediction->bookmaker_name }}">
                        </div>

                        <div class="form-group">
                            <label>Bookmaker URL</label>
                            <input type="url" name="bookmaker_url" class="form-control"
                                value="{{ $prediction->bookmaker_url }}">
                            <input type="hidden" name="bookmaker_image" value="{{ $prediction->bookmaker_image }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Home Team Score</label>
                                    <input type="number" name="home_score" class="form-control" min="0"
                                        value="{{ old('home_score', $prediction->home_score) }}" placeholder="e.g., 2">
                                    <small class="text-muted">Enter score after match is settled</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Away Team Score</label>
                                    <input type="number" name="away_score" class="form-control" min="0"
                                        value="{{ old('away_score', $prediction->away_score) }}" placeholder="e.g., 1">
                                    <small class="text-muted">Enter score after match is settled</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ old('status', $prediction->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="won" {{ old('status', $prediction->status) == 'won' ? 'selected' : '' }}>Won</option>
                                <option value="lost" {{ old('status', $prediction->status) == 'lost' ? 'selected' : '' }}>Lost</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                    value="1" {{ old('is_featured', $prediction->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Featured Prediction (Show in Carousel)</label>
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_banker" class="form-check-input" id="is_banker"
                                    value="1" {{ old('is_banker', $prediction->is_banker) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_banker">🏦 Banker Tip (High Confidence)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Admin Note (Internal Reason)</label>
                            <textarea name="admin_note" class="form-control"
                                rows="3">{{ old('admin_note', $prediction->admin_note) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Prediction
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const fixtureId = '{{ $prediction->fixture_id }}';
            let fixtureOddsData = null;
            const isCorrectScore = /^\d+-\d+$/.test('{{ $prediction->tip }}');

            // Calculate probability when odds change
            $('#odds').on('input', function () {
                const odds = parseFloat($(this).val());
                if (odds > 0) {
                    const probability = ((1 / odds) * 100).toFixed(2);
                    $('#calc-probability').val(probability + '%');
                }
            });

            // Fetch odds for this fixture on page load
            if (fixtureId) {
                $.post('{{ route("admin.fetch-odds") }}', {
                    _token: '{{ csrf_token() }}',
                    fixture_id: fixtureId
                }, function (response) {
                    if (response.odds && response.odds.length > 0) {
                        fixtureOddsData = response.odds[0];
                        $('#odds-source').text('API odds loaded. Select a tip to auto-populate.');
                        // If current tip is correct score, auto-fetch its odds
                        if (isCorrectScore) {
                            $('#tip-select').trigger('change');
                        }
                    } else {
                        $('#odds-source').text('No API odds found. Manual entry enabled.');
                    }
                }).fail(function () {
                    $('#odds-source').text('Failed to fetch odds. Manual entry enabled.');
                });
            }

            function populateOdds(tip) {
                if (!tip) return;
                if (!fixtureOddsData) {
                    $('#odds').prop('readonly', false);
                    $('#odds-source').text('Manual entry mode (No API odds found)');
                    return;
                }
                const oddsInfo = findOddsForTip(tip, fixtureOddsData);
                if (oddsInfo) {
                    $('#odds').val(oddsInfo.odd).prop('readonly', true).trigger('input');
                    $('input[name="bookmaker_name"]').val(oddsInfo.bookmaker);
                    $('input[name="bookmaker_url"]').val(oddsInfo.url || '');
                    if (oddsInfo.bookmaker_id) {
                        const logoUrl = 'https://media.api-sports.io/bookmakers/' + parseInt(oddsInfo.bookmaker_id) + '.png';
                        $('input[name="bookmaker_image"]').val(logoUrl);
                        $('#odds-source').html('<img src="' + logoUrl + '" height="20" class="mr-1"> From ' + oddsInfo.bookmaker + ' - ' + oddsInfo.market);
                    } else {
                        $('input[name="bookmaker_image"]').val('');
                        $('#odds-source').text('From ' + oddsInfo.bookmaker + ' - ' + oddsInfo.market);
                    }
                } else {
                    $('#odds').prop('readonly', false);
                    $('#odds-source').text('No odds found for this tip. Manual entry enabled.');
                }
            }

            // Correct score button clicks
            $('.cs-btn').on('click', function () {
                const score = $(this).data('score');
                $('#correct_score').val(score).removeClass('is-invalid').addClass('is-valid');
                $('#tip-select').val(score);
                populateOdds(score);
            });

            // When tip dropdown changes
            $('#tip-select').on('change', function () {
                const val = $(this).val();
                if (val && /^\d+-\d+$/.test(val)) {
                    $('#correct_score').val(val).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#correct_score').val('').removeClass('is-valid is-invalid');
                }
                populateOdds(val);
            });

            // Manual correct score typing
            $('#correct_score').on('input', function () {
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
                if (!oddsData.bookmakers || oddsData.bookmakers.length === 0) return null;
                const bookmaker = oddsData.bookmakers[0];
                const bets = bookmaker.bets;
                const bookmakerId = bookmaker.id;

                const tipMapping = {
                    'Over 0.5': { markets: ['Goals Over/Under'], value: 'Over 0.5' },
                    'Over 1.5': { markets: ['Goals Over/Under'], value: 'Over 1.5' },
                    'Over 2.5': { markets: ['Goals Over/Under'], value: 'Over 2.5' },
                    'Over 3.5': { markets: ['Goals Over/Under'], value: 'Over 3.5' },
                    'Under 1.5': { markets: ['Goals Over/Under'], value: 'Under 1.5' },
                    'Under 2.5': { markets: ['Goals Over/Under'], value: 'Under 2.5' },
                    'BTTS/GG': { markets: ['Both Teams Score', 'Both Teams to Score', 'BTTS'], value: 'Yes' },
                    'NG': { markets: ['Both Teams Score', 'Both Teams to Score', 'BTTS'], value: 'No' },
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

                if (!mapping) return null;

                for (let bet of bets) {
                    if (mapping.markets.includes(bet.name)) {
                        for (let value of bet.values) {
                            if (value.value === mapping.value) {
                                return {
                                    odd: value.odd,
                                    bookmaker: bookmaker.name,
                                    bookmaker_id: bookmakerId,
                                    url: 'https://' + bookmaker.name.toLowerCase().replace(/\s+/g, '') + '.com',
                                    market: bet.name + ' - ' + value.value
                                };
                            }
                        }
                    }
                }
                return null;
            }
        });
    </script>
    <style>
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
