@extends('admin.layout')

@section('page-title', 'Edit VIP Result')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit VIP Result Ticket</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.vip-results.update', $vipResult) }}" method="POST" id="vip-result-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ticket Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ $vipResult->date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Odds (auto-calculated when all items have odds)</label>
                                    <input type="number" step="0.01" name="total_odds" class="form-control" value="{{ $vipResult->total_odds }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Win Rate Label (Optional)</label>
                                    <input type="text" name="win_rate_display" class="form-control" value="{{ $vipResult->win_rate_display }}" placeholder="e.g. 98.5%">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <span class="badge badge-info p-2" style="font-size:13px;">
                                    Stage: <strong>{{ ucfirst($vipResult->stage) }}</strong>
                                </span>
                                @if($vipResult->status !== 'pending')
                                    <span class="badge {{ $vipResult->status === 'won' ? 'badge-success' : 'badge-danger' }} p-2" style="font-size:13px;">
                                        Status: <strong>{{ strtoupper($vipResult->status) }}</strong>
                                    </span>
                                @endif
                                @if($vipResult->stage === 'draft')
                                    <a href="{{ route('admin.vip-results.publish', $vipResult) }}" class="btn btn-success btn-sm ml-2"
                                       onclick="return confirm('Publish this ticket to subscribers?')">
                                        <i class="fa fa-send"></i> Publish to Subscribers
                                    </a>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="status" value="{{ $vipResult->status }}">

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Match Items</h6>
                            <button type="button" class="btn btn-success btn-sm" id="add-item">
                                <i class="fa fa-plus"></i> Add Match
                            </button>
                        </div>

                        <div id="items-container">
                            @foreach($vipResult->items as $index => $item)
                            <div class="item-row card mb-3 p-3 bg-light border">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small">Time</label>
                                            <input type="time" name="items[{{ $index }}][match_time]" class="form-control form-control-sm" value="{{ substr($item->match_time, 0, 5) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">League</label>
                                            <input type="text" name="items[{{ $index }}][league_name]" class="form-control form-control-sm" value="{{ $item->league_name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">Home Team</label>
                                            <input type="text" name="items[{{ $index }}][home_team]" class="form-control form-control-sm" value="{{ $item->home_team }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small">Away Team</label>
                                            <input type="text" name="items[{{ $index }}][away_team]" class="form-control form-control-sm" value="{{ $item->away_team }}" required>
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
                                            <input type="text" name="items[{{ $index }}][tip]" class="form-control form-control-sm" value="{{ $item->tip }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Odds</label>
                                            <input type="number" step="0.01" min="1.00" name="items[{{ $index }}][odds]" class="form-control form-control-sm" value="{{ $item->odds }}" placeholder="e.g. 2.50">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Home Score</label>
                                            <input type="number" name="items[{{ $index }}][home_score]" class="form-control form-control-sm" value="{{ $item->home_score }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small">Away Score</label>
                                            <input type="number" name="items[{{ $index }}][away_score]" class="form-control form-control-sm" value="{{ $item->away_score }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center pt-3">
                                        <label class="small mr-2" style="margin-bottom:0;white-space:nowrap;">Result:</label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[{{ $index }}][winner]" value="" class="custom-control-input" id="win_null_{{ $index }}" {{ $item->is_won === null ? 'checked' : '' }}>
                                            <label class="custom-control-label small" for="win_null_{{ $index }}">—</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[{{ $index }}][winner]" value="won" class="custom-control-input" id="win_won_{{ $index }}" {{ $item->is_won === true ? 'checked' : '' }}>
                                            <label class="custom-control-label small" for="win_won_{{ $index }}">Won</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" name="items[{{ $index }}][winner]" value="lost" class="custom-control-input" id="win_lost_{{ $index }}" {{ $item->is_won === false ? 'checked' : '' }}>
                                            <label class="custom-control-label small" for="win_lost_{{ $index }}">Lost</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4 border-top pt-3 text-right">
                            <a href="{{ route('admin.vip-results.index') }}" class="btn btn-link text-muted">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5">Update Result</button>
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
    let itemIndex = {{ $vipResult->items->count() }};

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
