@extends('admin.layout')

@section('title', 'Predictions')
@section('page-title', 'Manage Predictions')

@section('content')
    <div class="as-stat-grid">
        <div class="as-stat-card">
            <div class="as-stat-icon orange"><i class="fa fa-list"></i></div>
            <div class="as-stat-content">
                <div class="as-stat-label">Total Predictions</div>
                <div class="as-stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon green"><i class="fa fa-check"></i></div>
            <div class="as-stat-content">
                <div class="as-stat-label">Won</div>
                <div class="as-stat-value">{{ number_format($stats['won'] ?? 0) }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon red"><i class="fa fa-times"></i></div>
            <div class="as-stat-content">
                <div class="as-stat-label">Lost</div>
                <div class="as-stat-value">{{ number_format($stats['lost'] ?? 0) }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon orange"><i class="fa fa-clock-o"></i></div>
            <div class="as-stat-content">
                <div class="as-stat-label">Pending</div>
                <div class="as-stat-value">{{ number_format($stats['pending'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="as-card">
        <div class="as-card-body" style="padding-bottom:16px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Prediction List</span>
                <div class="as-action-group">
                    <form id="bulk-banker-form" action="{{ route('admin.predictions.bulk-banker') }}" method="POST" class="d-inline mr-2" style="display: none !important;">
                        @csrf
                        <input type="hidden" name="action" value="mark">
                        <button type="button" id="bulk-banker-btn" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                            <i class="fa fa-shield"></i> Mark Banker <span id="banker-selected-count" class="as-table-badge warning ml-1" style="padding:2px 8px;">0</span>
                        </button>
                    </form>
                    <form id="bulk-unbanker-form" action="{{ route('admin.predictions.bulk-banker') }}" method="POST" class="d-inline mr-2" style="display: none !important;">
                        @csrf
                        <input type="hidden" name="action" value="unmark">
                        <button type="button" id="bulk-unbanker-btn" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                            <i class="fa fa-shield"></i> Remove Banker <span id="unbanker-selected-count" class="as-table-badge warning ml-1" style="padding:2px 8px;">0</span>
                        </button>
                    </form>
                    <form id="bulk-delete-form" action="{{ route('admin.predictions.bulk-delete') }}" method="POST" class="d-inline mr-2" style="display: none !important;">
                        @csrf
                        <button type="button" id="bulk-delete-btn" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                            <i class="fa fa-trash"></i> Bulk Delete <span id="selected-count" class="as-table-badge danger ml-1" style="padding:2px 8px;">0</span>
                        </button>
                    </form>
                    <form id="send-emails-form" action="{{ route('admin.predictions.send-notifications') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" id="send-emails-btn" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;">
                            <i class="fa fa-envelope"></i> Send Emails
                        </button>
                    </form>
                    <a href="{{ route('admin.predictions.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                        <i class="fa fa-plus"></i> Create New
                    </a>
                </div>
            </div>

            <div class="as-filter-row">
                <form action="{{ route('admin.predictions.index') }}" method="GET" class="w-100">
                    <div class="as-filter-row">
                        <div class="as-filter-group" style="flex:2;">
                            <label>Search</label>
                            <div style="position:relative;">
                                <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.2);font-size:13px;z-index:1;"></i>
                                <input type="text" class="as-input" id="search" name="search" placeholder="Team, league..."
                                    value="{{ request('search') }}" style="padding-left:34px;padding-top:8px;padding-bottom:8px;font-size:13px;">
                            </div>
                        </div>
                        <div class="as-filter-group">
                            <label>Status</label>
                            <div class="as-select-wrap">
                                <select class="as-input as-select" id="status" name="status" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="won" {{ request('status') == 'won' ? 'selected' : '' }}>Won</option>
                                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                </select>
                                <i class="fa fa-chevron-down as-select-arrow"></i>
                            </div>
                        </div>
                        <div class="as-filter-group">
                            <label>Banker</label>
                            <div class="as-select-wrap">
                                <select class="as-input as-select" id="banker" name="banker" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                                    <option value="">All</option>
                                    <option value="yes" {{ request('banker') == 'yes' ? 'selected' : '' }}>Banker Tips</option>
                                    <option value="no" {{ request('banker') == 'no' ? 'selected' : '' }}>Non-Banker</option>
                                </select>
                                <i class="fa fa-chevron-down as-select-arrow"></i>
                            </div>
                        </div>
                        <div class="as-filter-group">
                            <label>Month</label>
                            <input type="month" class="as-input" id="month" name="month"
                                value="{{ request('month') }}" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                        </div>
                        <div class="as-filter-group" style="flex:1.5;">
                            <label>Date Range</label>
                            <div style="display:flex;gap:8px;">
                                <input type="date" class="as-input" name="date_from"
                                    value="{{ request('date_from') }}" placeholder="From" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                                <input type="date" class="as-input" name="date_to"
                                    value="{{ request('date_to') }}" placeholder="To" style="padding-top:8px;padding-bottom:8px;font-size:13px;">
                            </div>
                        </div>
                        <div class="as-filter-group" style="flex-direction:row;align-items:end;gap:8px;min-width:auto;">
                            <button type="submit" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">Apply</button>
                            @if(request()->hasAny(['search', 'status', 'banker', 'month', 'date_from', 'date_to']))
                                <a href="{{ route('admin.predictions.index') }}" class="as-btn as-btn-secondary" style="gap:6px;padding:8px 16px;font-size:12px;">
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
                        <th style="text-align:center;width:60px;">Banker</th>
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
                            <tr class="as-date-sep"><td colspan="7">{{ $dateLabel }}</td></tr>
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
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="as-table-badge primary" style="font-size:10px;padding:2px 10px;">{{ $prediction->league_name }}</span>
                                    <span style="color:rgba(255,255,255,0.3);font-size:12px;"><i class="fa fa-clock-o mr-1"></i>{{ \Carbon\Carbon::parse($prediction->match_time)->format('H:i') }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="color:rgba(255,255,255,0.4);font-size:12px;">
                                    @if($prediction->home_score !== null)
                                        <div style="color:#fff;font-weight:700;margin-bottom:2px;">Score: {{ $prediction->home_score }} - {{ $prediction->away_score }}</div>
                                    @endif
                                    <div>Prob: <span style="color:#fff;font-weight:600;">{{ $prediction->probability }}%</span></div>
                                    @if($prediction->is_banker)
                                        <div style="margin-top:4px;"><span class="as-table-badge warning" style="font-size:10px;padding:2px 10px;"><i class="fa fa-shield mr-1"></i>Banker</span></div>
                                    @endif
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
                            <td style="text-align:center;">
                                <form action="{{ route('admin.predictions.toggle-banker', $prediction) }}" method="POST" class="banker-toggle-form">
                                    @csrf
                                    <input type="checkbox" class="banker-toggle" {{ $prediction->is_banker ? 'checked' : '' }} onchange="this.closest('form').submit();" title="{{ $prediction->is_banker ? 'Remove from banker' : 'Mark as banker' }}">
                                </form>
                            </td>
                            <td style="text-align:right;padding-right:18px;">
                                <div class="as-action-group">
                                    <a href="{{ route('admin.predictions.edit', $prediction) }}" class="as-action-btn edit" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.predictions.destroy', $prediction) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="as-action-btn delete" onclick="showDeleteModal(this.closest('form'), 'prediction')" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @if ($prediction->status === 'pending')
                                        <form action="{{ route('admin.predictions.check-status', $prediction) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="as-action-btn info" title="Check Status API">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="as-empty">
                                    <div class="as-empty-icon"><i class="fa fa-folder-open-o"></i></div>
                                    <div class="as-empty-title">No predictions found</div>
                                    <div class="as-empty-text">Try adjusting your filters or create a new one.</div>
                                    <a href="{{ route('admin.predictions.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                        Create Prediction
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
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.prediction-checkbox');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');
        const selectedCountBadge = document.getElementById('selected-count');
        const bulkBankerForm = document.getElementById('bulk-banker-form');
        const bulkUnbankerForm = document.getElementById('bulk-unbanker-form');
        const bankerSelectedCount = document.getElementById('banker-selected-count');
        const unbankerSelectedCount = document.getElementById('unbanker-selected-count');

        function updateBulkUI() {
            const selected = document.querySelectorAll('.prediction-checkbox:checked');
            const count = selected.length;
            selectedCountBadge.textContent = count;
            bulkDeleteForm.style.setProperty('display', count > 0 ? 'inline-block' : 'none', 'important');
            bulkBankerForm.style.setProperty('display', count > 0 ? 'inline-block' : 'none', 'important');
            bulkUnbankerForm.style.setProperty('display', count > 0 ? 'inline-block' : 'none', 'important');
            bankerSelectedCount.textContent = count;
            unbankerSelectedCount.textContent = count;
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => { cb.checked = this.checked; });
            updateBulkUI();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateBulkUI();
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    selectAll.checked = allChecked;
                }
            });
        });

        function submitBulkBanker(form) {
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            const selected = document.querySelectorAll('.prediction-checkbox:checked');
            if (selected.length === 0) return;
            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        }

        document.getElementById('bulk-banker-btn').addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(bulkBankerForm, {
                    title: 'Mark as Banker Tips',
                    message: 'Mark selected predictions as banker tips?',
                    btnText: 'Mark as Banker',
                    btnClass: 'btn-warning',
                    btnIcon: 'fa-shield',
                });
            } else {
                if (confirm('Mark selected predictions as banker tips?')) {
                    submitBulkBanker(bulkBankerForm);
                }
            }
        });

        document.getElementById('bulk-unbanker-btn').addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(bulkUnbankerForm, {
                    title: 'Remove Banker Status',
                    message: 'Remove banker status from selected predictions?',
                    btnText: 'Remove Banker',
                    btnClass: 'btn-secondary',
                    btnIcon: 'fa-shield',
                });
            } else {
                if (confirm('Remove banker status from selected predictions?')) {
                    submitBulkBanker(bulkUnbankerForm);
                }
            }
        });

        document.getElementById('bulk-delete-btn').addEventListener('click', function(e) {
            e.preventDefault();
            bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            const selected = document.querySelectorAll('.prediction-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one prediction to delete.');
                return;
            }
            selected.forEach(cb => {
                const input = document.createElement('input');
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

        const sendEmailsBtn = document.getElementById('send-emails-btn');
        const sendEmailsForm = document.getElementById('send-emails-form');
        if(sendEmailsBtn) {
            sendEmailsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof showConfirmModal === 'function') {
                    showConfirmModal(sendEmailsForm, {
                        title: 'Send Notification Emails',
                        message: 'Send all unsent predictions for today to subscribers via email?',
                        btnText: 'Send Emails',
                        btnClass: 'btn-success',
                        btnIcon: 'fa-envelope',
                        headerBg: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                        subtext: 'This will notify all active subscribers.'
                    });
                } else {
                    if (confirm('Send all unsent predictions to subscribers via email?')) {
                        sendEmailsForm.submit();
                    }
                }
            });
        }
    });
</script>
@endpush
