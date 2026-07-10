@extends('admin.layout')

@section('page-title', 'Register VIP Member')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card bg-dark text-white border-secondary mb-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-user-plus text-warning mr-2"></i> Register New Member</h5>
                    <a href="{{ route('admin.vip-subscriptions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left mr-1"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"
                            style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #ffcccc;">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.vip-subscriptions.store') }}" method="POST">
                        @csrf
                        @if($targetUser)
                            <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                            <div class="alert alert-info" style="background-color: rgba(23, 162, 184, 0.1); border-color: #17a2b8; color: #17a2b8;">
                                <i class="fa fa-info-circle mr-1"></i> You are assigning a VIP plan to the registered user: <strong>{{ $targetUser->name }}</strong>.
                            </div>
                        @endif

                        @if(!$targetUser)
                            <div class="form-group mb-3">
                                <label for="user_id">Select Registered User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-control text-white" required>
                                    <option value="">Choose a user...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12 form-group mb-3">
                                <label for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control text-white"
                                    value="{{ old('phone') }}" placeholder="Optional">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="vip_plan_id">VIP Plan <span class="text-danger">*</span></label>
                                <select name="vip_plan_id" id="vip_plan_id" class="form-control text-white" required>
                                    <option value="">Select Plan...</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('vip_plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} ({{ $plan->duration_days }} days) -
                                            {{ config('app.currency_symbol', '$') }}{{ number_format($plan->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Expiry date will be calculated automatically based on
                                    the plan duration.</small>
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label for="starts_at">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="starts_at" id="starts_at" class="form-control text-white"
                                    value="{{ old('starts_at', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="status">Membership Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control text-white" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <hr class="border-secondary mb-4">

                        <button type="submit" class="btn btn-warning font-weight-bold px-4 py-2">
                            <i class="fa fa-save mr-1"></i> {{ $targetUser ? 'Assign VIP Plan' : 'Default Register & Activate' }}
                        </button>
                        <a href="{{ route('admin.vip-subscriptions.index') }}"
                            class="btn btn-outline-secondary ml-2 px-4 py-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark text-white border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h6 class="mb-0 text-warning"><i class="fa fa-info-circle mr-2"></i> How it works</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small"><strong>1. Selection:</strong> Choose the appropriate VIP Plan for the
                        member.</p>
                    <p class="text-muted small"><strong>2. Registration Date:</strong> Pick the exact date the membership
                        should become active.</p>
                    <p class="text-muted small"><strong>3. Expiration:</strong> The system automatically computes the <code
                            class="text-white small">expires_at</code> timestamp using the duration associated with your
                        selected plan.</p>

                    <hr class="border-secondary">

                    <p class="text-muted small mb-0"><i class="fa fa-lightbulb-o text-warning mr-1"></i> Note: Manual
                        registration assigns a generated dummy payment reference to bypass standard payment gateways.</p>
                </div>
            </div>
        </div>
    </div>
@endsection