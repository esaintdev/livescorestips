@extends('admin.layout')

@section('page-title', 'Create VIP Result')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New VIP Result Ticket</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.vip-results.store') }}" method="POST" id="vip-result-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ticket Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Odds (auto-calculated when odds set per match)</label>
                                    <input type="number" step="0.01" name="total_odds" class="form-control" placeholder="e.g. 5.50" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Win Rate Label (Optional)</label>
                                    <input type="text" name="win_rate_display" class="form-control" placeholder="e.g. 98.5%">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="status" value="pending">

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Match Items</h6>
                            <button type="button" class="btn btn-success btn-sm" id="add-item">
                                <i class="fa fa-plus"></i> Add Match
                            </button>
                        </div>

                        <div id="items-container">
                            <div class="item-row card mb-3 p-3 bg-light border">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small">Time</label>
                                            <input type="time" name="items[0][match_time]" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">League</label>
                                            <input type="text" name="items[0][league_name]" class="form-control form-control-sm" placeholder="e.g. Premier League" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">Home Team</label>
                                            <input type="text" name="items[0][home_team]" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">Away Team</label>
                                            <input type="text" name="items[0][away_team]" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end justify-content-center pb-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="small">Tip</label>
                                            <input type="text" name="items[0][tip]" class="form-control form-control-sm" placeholder="e.g. Home Win" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Odds</label>
                                            <input type="number" step="0.01" min="1.00" name="items[0][odds]" class="form-control form-control-sm" placeholder="e.g. 2.50">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Home Score</label>
                                            <input type="number" name="items[0][home_score]" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Away Score</label>
                                            <input type="number" name="items[0][away_score]" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center pt-3">
                                        <label class="small mr-2" style="margin-bottom:0;white-space:nowrap;">Result:</label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[0][winner]" value="" class="custom-control-input" id="win_null_0" checked>
                                            <label class="custom-control-label small" for="win_null_0">—</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[0][winner]" value="won" class="custom-control-input" id="win_won_0">
                                            <label class="custom-control-label small" for="win_won_0">Won</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[0][winner]" value="lost" class="custom-control-input" id="win_lost_0">
                                            <label class="custom-control-label small" for="win_lost_0">Lost</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 text-right">
                            <a href="{{ route('admin.vip-results.index') }}" class="btn btn-link text-muted">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5">Save Result</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 1;

    $('#add-item').click(function() {
        let newRow = `
            <div class="item-row card mb-3 p-3 bg-light border">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="small">Time</label>
                            <input type="time" name="items[${itemIndex}][match_time]" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="small">League</label>
                            <input type="text" name="items[${itemIndex}][league_name]" class="form-control form-control-sm" placeholder="e.g. Premier League" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="small">Home Team</label>
                            <input type="text" name="items[${itemIndex}][home_team]" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="small">Away Team</label>
                            <input type="text" name="items[${itemIndex}][away_team]" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end justify-content-center pb-2">
                        <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small">Tip</label>
                            <input type="text" name="items[${itemIndex}][tip]" class="form-control form-control-sm" placeholder="e.g. Home Win" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small">Odds</label>
                            <input type="number" step="0.01" min="1.00" name="items[${itemIndex}][odds]" class="form-control form-control-sm" placeholder="e.g. 2.50">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small">Home Score</label>
                            <input type="number" name="items[${itemIndex}][home_score]" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small">Away Score</label>
                            <input type="number" name="items[${itemIndex}][away_score]" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center pt-3">
                        <label class="small mr-2" style="margin-bottom:0;white-space:nowrap;">Result:</label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" name="items[${itemIndex}][winner]" value="" class="custom-control-input" id="win_null_${itemIndex}" checked>
                            <label class="custom-control-label small" for="win_null_${itemIndex}">—</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" name="items[${itemIndex}][winner]" value="won" class="custom-control-input" id="win_won_${itemIndex}">
                            <label class="custom-control-label small" for="win_won_${itemIndex}">Won</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" name="items[${itemIndex}][winner]" value="lost" class="custom-control-input" id="win_lost_${itemIndex}">
                            <label class="custom-control-label small" for="win_lost_${itemIndex}">Lost</label>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#items-container').append(newRow);
        itemIndex++;
    });

    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
        } else {
            alert('At least one match is required.');
        }
    });
});
</script>
@endpush
