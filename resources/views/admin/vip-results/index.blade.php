@extends('admin.layout')

@section('page-title', 'VIP Results')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Manage VIP Results</span>
                <a href="{{ route('admin.vip-results.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Result
                </a>
            </div>

            @if($results->count() > 0)
                <div class="as-table-wrap">
                    <table class="as-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Matches</th>
                                <th>Total Odds</th>
                                <th>Stage</th>
                                <th>Status</th>
                                <th>Win Rate Label</th>
                                <th style="text-align:right;padding-right:18px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                <tr>
                                    <td>{{ $result->date->format('M d, Y') }}</td>
                                    <td><span class="as-table-badge info">{{ $result->items_count }} Matches</span></td>
                                    <td style="font-weight:700;color:#fff;">{{ number_format($result->total_odds, 2) }}</td>
                                    <td>
                                        @if($result->stage === 'draft')
                                            <span class="as-table-badge warning">DRAFT</span>
                                        @elseif($result->stage === 'published')
                                            <span class="as-table-badge info">PUBLISHED</span>
                                        @else
                                            <span class="as-table-badge success">COMPLETED</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($result->status === 'won')
                                            <span class="as-table-badge success">WON</span>
                                        @elseif($result->status === 'lost')
                                            <span class="as-table-badge danger">LOST</span>
                                        @else
                                            <span class="as-table-badge warning">PENDING</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->win_rate_display ?? '-' }}</td>
                                    <td style="text-align:right;padding-right:18px;">
                                        <div class="as-action-group">
                                            <a href="{{ route('admin.vip-results.edit', $result) }}" class="as-action-btn edit" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.vip-results.destroy', $result) }}" method="POST" style="display:inline;" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="as-action-btn delete"
                                                    onclick="showDeleteModal(this.closest('form'), 'result')" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($results->hasPages())
                    <div class="as-pagination">{{ $results->links() }}</div>
                @endif
            @else
                <div class="as-empty">
                    <div class="as-empty-icon"><i class="fa fa-history"></i></div>
                    <div class="as-empty-title">No VIP results posted yet</div>
                    <div class="as-empty-text">Share your first winning result ticket.</div>
                    <a href="{{ route('admin.vip-results.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                        Create First Result
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
