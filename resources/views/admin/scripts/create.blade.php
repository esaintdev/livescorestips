@extends('admin.layout')

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="text-blue-800 font-bold mb-0">Add New Script</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.scripts.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.scripts.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label font-weight-bold small text-uppercase">Script Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}"
                                required placeholder="e.g. Google Analytics">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="position" class="form-label font-weight-bold small text-uppercase">Position</label>
                            <select class="form-control" id="position" name="position" required>
                                <option value="head" {{ old('position') == 'head' ? 'selected' : '' }}>Head (inside
                                    &lt;head&gt;)</option>
                                <option value="body" {{ old('position') == 'body' ? 'selected' : '' }}>Body (before
                                    &lt;/body&gt;)</option>
                                <option value="banner" {{ old('position') == 'banner' ? 'selected' : '' }}>Bottom Right Banner</option>
                            </select>
                            @error('position') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="code" class="form-label font-weight-bold small text-uppercase">Script Code</label>
                    <textarea class="form-control font-monospace" id="code" name="code" rows="10" required
                        placeholder="Paste your script code here..."
                        style="font-family: monospace; font-size: 0.9rem;">{{ old('code') }}</textarea>
                    <small class="form-text text-muted">Paste the full script tag, including
                        &lt;script&gt;...&lt;/script&gt;.</small>
                    @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-4">
                    <div class="form-check pl-0">
                        <input type="checkbox" id="is_active" name="is_active" checked
                            style="transform: scale(1.5); margin-right: 10px;">
                        <label class="form-check-label font-weight-bold" for="is_active"
                            style="vertical-align: text-bottom;">Active</label>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <button type="submit" class="btn btn-primary px-4">Save Script</button>
                </div>
            </form>
        </div>
    </div>
@endsection