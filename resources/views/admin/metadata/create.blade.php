@extends('admin.layout')

@section('page-title', 'Add Metadata')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>New Metadata Record</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.metadata.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="route_name">Route Name (Optional)</label>
                    <input type="text" name="route_name" id="route_name" class="form-control"
                        value="{{ old('route_name') }}" placeholder="e.g., home, blog.index (Leave empty for Global)">
                    <small class="text-muted">Specify the route name if this metadata applies to a specific page.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="key">Key <span class="text-danger">*</span></label>
                    <input type="text" name="key" id="key" class="form-control" value="{{ old('key') }}" required
                        placeholder="e.g., og:image, twitter:site">
                    @error('key') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="value">Value</label>
                    <textarea name="value" id="value" class="form-control" rows="3">{{ old('value') }}</textarea>
                    @error('value') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Metadata</button>
                    <a href="{{ route('admin.metadata.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection