@extends('admin.layout')

@section('title', 'Edit Site Link')
@section('page-title', 'Edit Site Link')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-dark" style="font-weight: 600;">Link Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.site-links.update', $siteLink) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title" class="form-label font-weight-bold small text-uppercase">Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="{{ old('title', $siteLink->title) }}" required>
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="url" class="form-label font-weight-bold small text-uppercase">URL</label>
                            <input type="url" class="form-control" id="url" name="url"
                                value="{{ old('url', $siteLink->url) }}" required>
                            @error('url') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <input type="hidden" name="section" value="footer">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="order" class="form-label font-weight-bold small text-uppercase">Order</label>
                                <input type="number" class="form-control" id="order" name="order"
                                    value="{{ old('order', $siteLink->order) }}">
                                @error('order') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="form-check pl-0">
                                <input type="checkbox" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', $siteLink->is_active) ? 'checked' : '' }} style="transform: scale(1.5); margin-right: 10px;">
                                <label class="form-check-label font-weight-bold" for="is_active" style="vertical-align: text-bottom;">Active</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.site-links.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection