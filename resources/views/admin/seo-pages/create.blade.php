@extends('admin.layout')

@section('page-title', 'Create SEO Page')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>New SEO Page Details</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.seo-pages.store') }}" method="POST" id="seo-page-form">
                @csrf

                <div class="form-group mb-3">
                    <label for="title">Hero Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Victor Prediction Banker Betting Tips">
                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="sub_header">Hero Sub-header</label>
                    <input type="text" name="sub_header" id="sub_header" class="form-control" value="{{ old('sub_header') }}" placeholder="e.g. Expert soccer predictions for football fans around the world.">
                    @error('sub_header') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" required>
                    <small class="text-muted">Unique identifier for the URL.</small>
                    @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Article Content</label>
                    <div id="editor-container">{!! old('content') !!}</div>
                    <textarea id="content" name="content" class="form-control" style="display:none"></textarea>
                    @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <hr>
                <h5>Meta Tags</h5>

                <div class="form-group mb-3">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                        value="{{ old('meta_title') }}">
                </div>

                <div class="form-group mb-3">
                    <label for="meta_description">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" class="form-control"
                        rows="3">{{ old('meta_description') }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                        value="{{ old('meta_keywords') }}">
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Page</button>
                    <a href="{{ route('admin.seo-pages.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor-container {
            height: 400px;
            background: #1a1a1a;
            color: #eee;
        }
    </style>
@endpush

@push('scripts')
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#editor-container', {
                theme: 'snow',
                placeholder: 'Write your SEO article content here...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link', 'blockquote', 'code-block'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });

            // Sync content for form submit
            var form = document.getElementById('seo-page-form');
            form.addEventListener('submit', function (e) {
                var content = document.getElementById('content');
                content.value = quill.root.innerHTML;
            });
        });
    </script>
@endpush