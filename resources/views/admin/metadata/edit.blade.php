@extends('admin.layout')

@section('page-title', 'Edit Metadata')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Edit Metadata Record</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.metadata.update', $metadata) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="route_name">Route Name (Optional)</label>
                    <input type="text" name="route_name" id="route_name" class="form-control"
                        value="{{ old('route_name', $metadata->route_name) }}"
                        placeholder="e.g., home, blog.index (Leave empty for Global)">
                </div>

                <div class="form-group mb-3">
                    <label for="key">Key <span class="text-danger">*</span></label>
                    <input type="text" name="key" id="key" class="form-control" value="{{ old('key', $metadata->key) }}"
                        required>
                    @error('key') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="value">Value</label>
                    <textarea name="value" id="value" class="form-control"
                        rows="3">{{ old('value', $metadata->value) }}</textarea>
                    @error('value') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Metadata</button>
                    <a href="{{ route('admin.metadata.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection