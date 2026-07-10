@extends('admin.layout')

@section('page-title', 'Create VIP Plan')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New VIP Plan</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.vip-plans.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="category">Category <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                @foreach(\App\Models\VipPlan::CATEGORIES as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Plans are grouped by category on the VIP page</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="{{ old('name') }}" placeholder="e.g., 2 Week Premium" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration">Duration Label <span class="text-danger">*</span></label>
                                    <input type="text" name="duration" id="duration" class="form-control" 
                                           value="{{ old('duration') }}" placeholder="e.g., 2 Week, 1 Month" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_days">Duration in Days <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_days" id="duration_days" class="form-control" 
                                           value="{{ old('duration_days') }}" placeholder="e.g., 14, 30" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country_name">Country Name</label>
                                    <input type="text" name="country_name" id="country_name" class="form-control" 
                                           value="{{ old('country_name') }}" placeholder="e.g., Nigeria, Ghana">
                                    <small class="text-muted">Leave empty for Global/USD plans</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country_code">Country Code</label>
                                    <input type="text" name="country_code" id="country_code" class="form-control" 
                                           value="{{ old('country_code') }}" placeholder="e.g., NG, GH">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control" 
                                           value="{{ old('price') }}" placeholder="e.g., 50" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currency">Currency Symbol</label>
                                    <input type="text" name="currency" id="currency" class="form-control" 
                                           value="{{ old('currency', '$') }}" placeholder="e.g., $, ₦, ₵">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="badge_text">Badge Text</label>
                            <input type="text" name="badge_text" id="badge_text" class="form-control" 
                                   value="{{ old('badge_text') }}" placeholder="e.g., Sure 2+Odds Daily">
                            <small class="text-muted">Displayed as a badge on the pricing card</small>
                        </div>

                        <div class="form-group">
                            <label for="features">Features</label>
                            <textarea name="features" id="features" class="form-control" rows="3" 
                                      placeholder="95% Accuracy, 2+Odds bankers, 24/7 Support">{{ old('features') }}</textarea>
                            <small class="text-muted">Comma-separated list of features</small>
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" 
                                   value="{{ old('sort_order', 0) }}" placeholder="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>

                        <div class="form-group">
                            <div class="d-flex align-items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active"
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       style="width: 18px; height: 18px; cursor: pointer; margin-right: 10px;">
                                <label for="is_active" style="cursor: pointer; margin-bottom: 0;">Active</label>
                            </div>
                            <small class="text-muted d-block mt-1">Only active plans are shown on the public VIP page</small>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Create Plan</button>
                            <a href="{{ route('admin.vip-plans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
