@extends('admin.layout')

@section('page-title', 'All Members')

@section('content')
<div class="as-card">
    <div class="as-card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span style="font-size:15px;font-weight:700;color:#fff;">
                <i class="fa fa-users" style="color:#ff6b00;margin-right:8px;"></i>
                @if($filter === 'subscribed')
                    Subscribed Members
                @elseif($filter === 'disabled')
                    Disabled Members
                @elseif($filter === 'flagged')
                    Flagged Members
                @elseif($filter === 'pending')
                    Pending Checkouts
                @else
                    All Members
                @endif
            </span>
            <div class="as-action-group">
                <button type="button" class="as-btn as-btn-secondary" id="btnBulkDelete" style="display:none;gap:6px;padding:7px 16px;font-size:12px;" onclick="confirmBulkDelete()">
                    <i class="fa fa-trash"></i> Bulk Delete
                </button>
                <a href="{{ route('admin.vip-subscriptions.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Assign VIP
                </a>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="as-pill-tabs mb-3">
            <a href="{{ route('admin.vip-subscriptions.index') }}" 
                class="as-pill-tab {{ !$filter || $filter === 'all' ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'subscribed']) }}" 
                class="as-pill-tab {{ $filter === 'subscribed' ? 'active' : '' }}">
                <i class="fa fa-check-circle"></i> Subscribed
            </a>
            <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'pending']) }}" 
                class="as-pill-tab {{ $filter === 'pending' ? 'active' : '' }}">
                <i class="fa fa-clock-o"></i> Pending
            </a>
            <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'disabled']) }}" 
                class="as-pill-tab {{ $filter === 'disabled' ? 'active' : '' }}">
                <i class="fa fa-ban"></i> Disabled
            </a>
            <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'flagged']) }}" 
                class="as-pill-tab {{ $filter === 'flagged' ? 'active' : '' }}">
                <i class="fa fa-flag"></i> Flagged
            </a>
        </div>

        <div class="as-table-wrap">
            <form id="bulkDeleteForm" action="{{ route('admin.vip-subscriptions.bulk-delete') }}" method="POST">
                @csrf
                <table class="as-table">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;padding-left:18px;">
                                <input type="checkbox" id="selectAll" class="as-checkbox">
                            </th>
                            <th style="text-align:left;">Name</th>
                            <th>Email &amp; Phone</th>
                            <th>VIP Plan</th>
                            <th>VIP Status</th>
                            <th>Expires At</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $sub = $user->activeSubscription ?? $user->vipSubscriptions->sortByDesc('created_at')->first();
                            @endphp
                            <tr style="{{ $sub && $sub->is_disabled ? 'opacity:0.5;' : '' }}">
                                <td style="text-align:center;padding-left:18px;">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $user->id }}" class="row-checkbox as-checkbox">
                                </td>
                                <td style="text-align:left;font-weight:700;color:#fff;">
                                    {{ $user->name }}
                                    @if($sub && $sub->is_flagged)
                                        <span class="as-table-badge warning" style="margin-left:4px;" title="Flagged"><i class="fa fa-flag"></i></span>
                                    @endif
                                    @if($sub && $sub->is_disabled)
                                        <span class="as-table-badge danger" style="margin-left:4px;" title="Disabled"><i class="fa fa-ban"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $user->email }}</div>
                                    @if($user->phone)
                                        <div style="color:rgba(255,255,255,0.3);font-size:12px;margin-top:2px;"><i class="fa fa-phone"></i> {{ $user->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($sub)
                                        @php
                                            $isActive = !$sub->is_disabled && $sub->status === 'active' && $sub->expires_at && $sub->expires_at->isFuture();
                                        @endphp
                                        <span class="as-table-badge {{ $isActive ? 'success' : 'secondary' }}">{{ $sub->vipPlan->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="as-table-badge secondary">Free Account</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$sub)
                                        <span class="as-table-badge secondary">None</span>
                                    @elseif($sub->is_disabled)
                                        <span class="as-table-badge danger">Disabled</span>
                                    @elseif($sub->status === 'pending')
                                        <span class="as-table-badge info">Pending</span>
                                    @elseif($sub->status === 'active' && $sub->expires_at && $sub->expires_at->isFuture())
                                        <span class="as-table-badge success">Active</span>
                                    @elseif($sub->status === 'cancelled')
                                        <span class="as-table-badge danger">Cancelled</span>
                                    @else
                                        <span class="as-table-badge danger">Expired</span>
                                    @endif
                                </td>
                                <td style="font-weight:700;{{ $sub && $sub->expires_at && $sub->expires_at->isPast() ? 'color:#ef4444;' : 'color:#10b981;' }}">
                                    {{ $sub && $sub->expires_at ? $sub->expires_at->format('M d, Y') : '-' }}
                                </td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        @if($sub)
                                            <a href="{{ route('admin.vip-subscriptions.edit', $sub->id) }}" class="as-action-btn info" title="Edit Subscription">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.vip-subscriptions.create', ['user_id' => $user->id]) }}" class="as-action-btn warning" title="Assign VIP">
                                                <i class="fa fa-star"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="as-empty">
                                        <div class="as-empty-icon"><i class="fa fa-users"></i></div>
                                        <div class="as-empty-title">
                                            @if($filter === 'subscribed') No subscribed members found.
                                            @elseif($filter === 'disabled') No disabled members found.
                                            @elseif($filter === 'flagged') No flagged members found.
                                            @else No registered accounts found.
                                            @endif
                                        </div>
                                        <div class="as-empty-text">Members will appear here once they register.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        @if($users->hasPages())
            <div class="as-pagination">{{ $users->links() }}</div>
        @endif
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        if (btnBulkDelete) btnBulkDelete.style.display = 'none';
        const selectAll = document.getElementById('selectAll');
        if (selectAll) selectAll.disabled = true;
    });
</script>
@endpush
@endsection
