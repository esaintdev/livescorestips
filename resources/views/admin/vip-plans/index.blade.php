@extends('admin.layout')

@section('page-title', 'VIP Plans')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Manage VIP Plans</span>
                <a href="{{ route('admin.vip-plans.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Plan
                </a>
            </div>

            @if($plans->count() > 0)
                <div class="as-table-wrap">
                    <table class="as-table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Subscribers</th>
                                <th style="text-align:right;padding-right:18px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                <tr>
                                    <td>
                                        @if($plan->country_name)
                                            <span class="as-table-badge info">{{ $plan->country_name }} ({{ $plan->country_code }})</span>
                                        @else
                                            <span class="as-table-badge secondary">Global (USD)</span>
                                        @endif
                                    </td>
                                    <td><span class="as-table-badge primary">{{ $plan->category }}</span></td>
                                    <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $plan->name }}</td>
                                    <td>{{ $plan->duration }} ({{ $plan->duration_days }} days)</td>
                                    <td style="font-weight:700;color:#fff;">{{ $plan->formatted_price }}</td>
                                    <td>
                                        @if($plan->is_active)
                                            <span class="as-table-badge success">Active</span>
                                        @else
                                            <span class="as-table-badge secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td><span class="as-table-badge info">{{ $plan->subscriptions_count }}</span></td>
                                    <td style="text-align:right;padding-right:18px;">
                                        <div class="as-action-group">
                                            <a href="{{ route('admin.vip-plans.edit', $plan) }}" class="as-action-btn edit" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.vip-plans.destroy', $plan) }}" method="POST" style="display:inline;" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="as-action-btn delete"
                                                    onclick="showDeleteModal(this.closest('form'), 'VIP plan')" title="Delete">
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

                @if($plans->hasPages())
                    <div class="as-pagination">{{ $plans->links() }}</div>
                @endif
            @else
                <div class="as-empty">
                    <div class="as-empty-icon"><i class="fa fa-star"></i></div>
                    <div class="as-empty-title">No VIP plans found</div>
                    <div class="as-empty-text">Create your first VIP subscription plan.</div>
                    <a href="{{ route('admin.vip-plans.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                        Create Your First Plan
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
