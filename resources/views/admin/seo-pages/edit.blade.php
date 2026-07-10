@extends('admin.layout')

@section('page-title', 'Edit SEO Page')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Edit SEO Page: {{ $seoPage->title }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.seo-pages.update', $seoPage) }}" method="POST" id="seo-page-form">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="title">Hero Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control"
                        value="{{ old('title', $seoPage->title) }}" required>
                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="sub_header">Hero Sub-header</label>
                    <input type="text" name="sub_header" id="sub_header" class="form-control"
                        value="{{ old('sub_header', $seoPage->sub_header) }}">
                    @error('sub_header') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $seoPage->slug) }}"
                        required>
                    @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Article Content</label>
                    <div id="editor-container">{!! old('content', $seoPage->content) !!}</div>
                    <textarea id="content" name="content" class="form-control" style="display:none"></textarea>
                    @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <hr>
                <h5>Meta Tags</h5>

                <div class="form-group mb-3">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                        value="{{ old('meta_title', $seoPage->meta_title) }}">
                </div>

                <div class="form-group mb-3">
                    <label for="meta_description">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" class="form-control"
                        rows="3">{{ old('meta_description', $seoPage->meta_description) }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                        value="{{ old('meta_keywords', $seoPage->meta_keywords) }}">
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $seoPage->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Page</button>
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