@extends('admin.layout')

@section('page-title', 'Edit VIP Member')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card bg-dark text-white border-secondary mb-4">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-edit text-warning mr-2"></i> Edit Member Details</h5>
                <a href="{{ route('admin.vip-subscriptions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Back
                </a>
            </div>
            
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #ffcccc;">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.vip-subscriptions.update', $vipSubscription) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control text-white" value="{{ old('name', $vipSubscription->name) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control text-white" value="{{ old('email', $vipSubscription->email) }}" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control text-white" value="{{ old('phone', $vipSubscription->phone) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 form-group mb-3">
                            <label for="vip_plan_id">VIP Plan <span class="text-danger">*</span></label>
                            <select name="vip_plan_id" id="vip_plan_id" class="form-control text-white" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('vip_plan_id', $vipSubscription->vip_plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} ({{ $plan->duration_days }} days)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4 form-group mb-3">
                            <label for="starts_at">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="starts_at" id="starts_at" class="form-control text-white" value="{{ old('starts_at', $vipSubscription->starts_at ? $vipSubscription->starts_at->format('Y-m-d') : '') }}" required>
                        </div>

                        <div class="col-md-3 form-group mb-3 d-flex flex-column justify-content-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="recalculate_expiry" name="recalculate_expiry" value="1" style="transform: scale(1.2); margin-top: 6px;">
                                <label class="form-check-label text-warning small ml-1" for="recalculate_expiry" style="font-weight: 600; cursor: pointer; text-transform: uppercase;">
                                    Auto Extend Expiry
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="status">Membership Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control text-white" required>
                            <option value="active" {{ old('status', $vipSubscription->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('status', $vipSubscription->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ old('status', $vipSubscription->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <hr class="border-secondary mb-3">

                    {{-- Security & Controls --}}
                    <div class="mb-4">
                        <p class="text-muted small mb-2 text-uppercase font-weight-bold" style="letter-spacing: 1px;">
                            <i class="fa fa-shield mr-1 text-warning"></i> Security & Controls
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_disabled" name="is_disabled"
                                        value="1" style="transform: scale(1.2); margin-top: 6px;"
                                        {{ old('is_disabled', $vipSubscription->is_disabled) ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger ml-1 small font-weight-bold" for="is_disabled" style="cursor:pointer; text-transform:uppercase;">
                                        <i class="fa fa-ban mr-1"></i> Disable Member Account
                                    </label>
                                    <small class="d-block text-muted mt-1 ml-1" style="font-size: 0.75rem;">Freezes access to the account.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_flagged" name="is_flagged"
                                        value="1" style="transform: scale(1.2); margin-top: 6px;"
                                        {{ old('is_flagged', $vipSubscription->is_flagged) ? 'checked' : '' }}>
                                    <label class="form-check-label text-warning ml-1 small font-weight-bold" for="is_flagged" style="cursor:pointer; text-transform:uppercase;">
                                        <i class="fa fa-flag mr-1"></i> Flag Account for Warning
                                    </label>
                                    <small class="d-block text-muted mt-1 ml-1" style="font-size: 0.75rem;">Marks the account for admin review.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary mb-4">

                    <button type="submit" class="btn btn-warning font-weight-bold px-4 py-2">
                        <i class="fa fa-save mr-1"></i> Update Member
                    </button>
                    <a href="{{ route('admin.vip-subscriptions.index') }}" class="btn btn-outline-secondary ml-2 px-4 py-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-dark text-white border-secondary mb-4">
            <div class="card-header border-secondary">
                <h6 class="mb-0 "><i class="fa fa-info-circle text-info mr-2"></i> Current Tracking</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Currently Expires At:</p>
                <p class="font-weight-bold {{ $vipSubscription->expires_at && $vipSubscription->expires_at->isPast() ? 'text-danger' : 'text-success' }}">
                    {{ $vipSubscription->expires_at ? $vipSubscription->expires_at->format('l, F j, Y') : 'No Expiry Set' }}
                </p>
                
                <hr class="border-secondary my-3">
                
                <p class="text-muted small mb-0">
                    <i class="fa fa-lightbulb-o text-warning mr-1"></i> If you wish to extend the membership based on a new plan purchase, change the Start Date and check the <strong>Auto Extend Expiry</strong> toggle. This will safely calculate the new expiration date.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
