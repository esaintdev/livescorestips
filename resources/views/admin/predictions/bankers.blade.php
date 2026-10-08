@extends('admin.layout')

@section('title', 'Banker Tips')
@section('page-title', 'Banker Tips Management')

@section('content')
    <div class="as-card">
        <div class="as-card-body" style="padding-bottom:16px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">
                    <i class="fa fa-shield text-warning mr-2"></i> Banker Tips
                </span>
                <div>
                    <form id="bulk-delete-form" action="{{ route('admin.predictions.bulk-delete') }}" method="POST" class="d-inline mr-2" style="display: none !important;">
                        @csrf
                        <button type="button" id="bulk-delete-btn" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                            <i class="fa fa-trash"></i> Bulk Delete <span id="selected-count" class="as-table-badge danger ml-1" style="padding:2px 8px;">0</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.predictions.index', ['banker' => 'yes']) }}" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                        <i class="fa fa-list"></i> View in All Predictions
                    </a>
                </div>
            </div>

            <div class="as-filter-row">
                <form action="{{ route('admin.predictions.bankers') }}" method="GET" class="w-100">
                    <div class="as-filter-row">
                        <div class="as-filter-group" style="flex:2;">
                            <label>Search</label>
                            <div style="position:relative;">
                                <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.2);font-size:13px;z-index:1;"></i>
                                <input type="text" class="as-input" name="search" placeholder="Team, league..."
                                    value="{{ request('search') }}" style="padding-left:34px;padding-top:8px;padding-bottom:8px;font-size:13px;">
                            </div>
                        </div>
                        <div class="as-filter-group">
                            <label>Date From</label>
                            <input type="date" class="as-input" name="date_from"
                                value="{{ request('date_from') }}" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                        </div>
                        <div class="as-filter-group">
                            <label>Date To</label>
                            <input type="date" class="as-input" name="date_to"
                                value="{{ request('date_to') }}" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                        </div>
                        <div class="as-filter-group" style="flex-direction:row;align-items:end;gap:8px;min-width:auto;">
                            <button type="submit" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">Apply</button>
                            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                                <a href="{{ route('admin.predictions.bankers') }}" class="as-btn as-btn-secondary" style="gap:6px;padding:8px 16px;font-size:12px;">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="as-table-wrap" style="border-left:0;border-right:0;border-bottom:0;border-radius:0;">
            <table class="as-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;padding-left:18px;">
                            <input type="checkbox" id="select-all" class="as-checkbox">
                        </th>
                        <th style="width:35%;">Match Details</th>
                        <th>Details</th>
                        <th>Prediction</th>
                        <th>Status</th>
                        <th style="text-align:right;padding-right:18px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentDate = null; @endphp
                    @forelse($predictions as $prediction)
                        @php
                            $predictionDate = \Carbon\Carbon::parse($prediction->match_date)->format('Y-m-d');
                            $displayDate = \Carbon\Carbon::parse($prediction->match_date)->format('l, F j, Y');
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                            $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                            $dateLabel = $displayDate;
                            if ($predictionDate === $today) $dateLabel = 'Today, ' . \Carbon\Carbon::parse($prediction->match_date)->format('F j');
                            elseif ($predictionDate === $tomorrow) $dateLabel = 'Tomorrow, ' . \Carbon\Carbon::parse($prediction->match_date)->format('F j');
                        @endphp
                        @if ($currentDate !== $predictionDate)
                            <tr class="as-date-sep"><td colspan="6">{{ $dateLabel }}</td></tr>
                            @php $currentDate = $predictionDate; @endphp
                        @endif
                        <tr>
                            <td style="text-align:center;padding-left:18px;">
                                <input type="checkbox" class="prediction-checkbox as-checkbox"
                                    id="prediction-{{ $prediction->id }}" value="{{ $prediction->id }}">
                            </td>
                            <td>
                                <div style="font-weight:700;color:#fff;font-size:15px;margin-bottom:4px;">
                                    {{ $prediction->home_team }} <span style="color:rgba(255,255,255,0.3);margin:0 4px;">vs</span>
                                    {{ $prediction->away_team }}
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <span class="as-table-badge primary" style="font-size:10px;padding:2px 10px;">{{ $prediction->league_name }}</span>
                                    <span style="color:rgba(255,255,255,0.3);font-size:12px;"><i class="fa fa-clock-o mr-1"></i>{{ \Carbon\Carbon::parse($prediction->match_time)->format('H:i') }}</span>
                                    <span style="color:rgba(255,255,255,0.3);font-size:12px;">{{ \Carbon\Carbon::parse($prediction->match_date)->format('M j, Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="color:rgba(255,255,255,0.4);font-size:12px;">
                                    @if($prediction->home_score !== null)
                                        <div style="color:#fff;font-weight:700;margin-bottom:2px;">Score: {{ $prediction->home_score }} - {{ $prediction->away_score }}</div>
                                    @endif
                                    <div>Prob: <span style="color:#fff;font-weight:600;">{{ $prediction->probability }}%</span></div>
                                    <div><span class="as-table-badge warning" style="font-size:10px;padding:2px 10px;margin-top:4px;"><i class="fa fa-shield mr-1"></i>Banker</span></div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    <span class="as-table-badge primary" style="width:fit-content;padding:6px 14px;font-size:13px;">{{ $prediction->tip }}</span>
                                    <span style="color:#fff;font-weight:700;font-size:13px;">Odds: {{ $prediction->odds }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($prediction->status === 'won')
                                    <span class="as-table-badge success"><i class="fa fa-check mr-1"></i> Won</span>
                                @elseif ($prediction->status === 'lost')
                                    <span class="as-table-badge danger"><i class="fa fa-times mr-1"></i> Lost</span>
                                @else
                                    <span class="as-table-badge warning"><i class="fa fa-clock-o mr-1"></i> Pending</span>
                                @endif
                            </td>
                            <td style="text-align:right;padding-right:18px;">
                                <div class="as-action-group">
                                    <a href="{{ route('admin.predictions.edit', $prediction) }}" class="as-action-btn edit" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.predictions.bulk-banker') }}" method="POST" class="d-inline unbanker-form" data-banker-form>
                                        @csrf
                                        <input type="hidden" name="action" value="unmark">
                                        <input type="hidden" name="ids[]" value="{{ $prediction->id }}">
                                        <button type="button" class="as-action-btn danger unbanker-btn" title="Remove Banker">
                                            <i class="fa fa-shield"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="as-empty">
                                    <div class="as-empty-icon"><i class="fa fa-shield"></i></div>
                                    <div class="as-empty-title">No banker tips found</div>
                                    <div class="as-empty-text">Mark predictions as banker from the main predictions list or edit page.</div>
                                    <a href="{{ route('admin.predictions.index', ['banker' => 'no']) }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                        Browse Predictions
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($predictions->hasPages())
            <div class="as-card-footer">
                <div class="as-pagination">{{ $predictions->links() }}</div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('select-all');
        var checkboxes = document.querySelectorAll('.prediction-checkbox');
        var bulkDeleteForm = document.getElementById('bulk-delete-form');
        var selectedCountBadge = document.getElementById('selected-count');

        function updateBulkUI() {
            var selected = document.querySelectorAll('.prediction-checkbox:checked');
            selectedCountBadge.textContent = selected.length;
            bulkDeleteForm.style.setProperty('display', selected.length > 0 ? 'inline-block' : 'none', 'important');
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
                updateBulkUI();
            });
        }

        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateBulkUI();
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                } else if (selectAll) {
                    selectAll.checked = Array.from(checkboxes).every(function(c) { return c.checked; });
                }
            });
        });

        document.getElementById('bulk-delete-btn').addEventListener('click', function(e) {
            e.preventDefault();
            bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
            var selected = document.querySelectorAll('.prediction-checkbox:checked');
            if (selected.length === 0) { alert('Please select at least one prediction to delete.'); return; }
            selected.forEach(function(cb) {
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                bulkDeleteForm.appendChild(input);
            });
            if (typeof showDeleteModal === 'function') {
                showDeleteModal(bulkDeleteForm, selected.length + ' selected predictions');
            } else {
                if (confirm('Are you sure you want to delete ' + selected.length + ' selected predictions?')) {
                    bulkDeleteForm.submit();
                }
            }
        });

        document.querySelectorAll('.unbanker-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form[data-banker-form]');
                if (typeof showConfirmModal === 'function') {
                    showConfirmModal(form, {
                        title: 'Remove Banker Status',
                        message: 'Remove banker status from this prediction?',
                        btnText: 'Remove Banker',
                        btnClass: 'btn-danger',
                        btnIcon: 'fa-shield',
                        color: '#ef4444',
                        subtext: null
                    });
                }
            });
        });
    });
</script>
@endpush