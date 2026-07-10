@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create New Article</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="article-form" action="{{ route('admin.articles.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Image/Video</label>
                            <input type="file" name="image" class="form-control-file">
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Meta Description (for SEO)</label>
                            <textarea name="meta_description" class="form-control" rows="2" maxlength="500"
                                placeholder="Brief description for search engines and social media previews">{{ old('meta_description') }}</textarea>
                            <small class="text-muted">Recommended: 150-160 characters</small>
                        </div>

                        <div class="form-group">
                            <label>Meta Keywords (for SEO)</label>
                            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords') }}"
                                placeholder="keyword1, keyword2, keyword3">
                            <small class="text-muted">Comma-separated keywords</small>
                        </div>

                        <div class="form-group">
                            <label>Content</label>
                            <div id="editor-container"></div>
                            <textarea id="content" name="content" class="form-control">{!! old('content') !!}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Article</button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
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

        #content {
            display: none;
        }
    </style>
@endpush

@push('scripts')
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Register font size options
        var Size = Quill.import('attributors/style/size');
        Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px', '36px', '48px'];
        Quill.register(Size, true);

        // HTML Source Code Editor variables (declared early for form submit handler)
        var htmlEditorActive = false;
        var htmlTextarea = document.createElement('textarea');

        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'size': ['10px', '12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px', '36px', '48px'] }],
                    [{ 'font': [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['blockquote', 'code-block'],
                    ['clean']
                ]
            },
            placeholder: 'Write your article content here...'
        });

        // Sync Quill content to hidden textarea on form submit
        var form = document.getElementById('article-form');
        form.addEventListener('submit', function (e) {
            // If in HTML mode, sync HTML back to Quill first
            if (htmlEditorActive) {
                quill.root.innerHTML = htmlTextarea.value;
            }

            var content = document.getElementById('content');
            content.value = quill.root.innerHTML;

            // Validate that content is not empty
            // Check HTML textarea if in HTML mode, otherwise check Quill text
            var hasContent = false;
            if (htmlEditorActive) {
                hasContent = htmlTextarea.value.trim().length > 0;
            } else {
                var text = quill.getText().trim();
                hasContent = text.length > 0;
            }

            if (!hasContent) {
                e.preventDefault();
                alert('Please enter article content.');
                return false;
            }

            // Allow form to submit
            return true;
        });

        // Load existing content if any (from validation errors)
        var contentHolder = document.getElementById('content');
        if (contentHolder.value && contentHolder.value.trim() !== '') {
            quill.root.innerHTML = contentHolder.value;
        }

        // HTML Source Code Editor Setup - Light Theme
        htmlTextarea.style.width = '100%';
        htmlTextarea.style.height = '400px';
        htmlTextarea.style.fontFamily = 'monospace';
        htmlTextarea.style.fontSize = '14px';
        htmlTextarea.style.padding = '10px';
        htmlTextarea.style.border = '1px solid #d1d5db';
        htmlTextarea.style.background = '#f9fafb';
        htmlTextarea.style.color = '#1f2937';
        htmlTextarea.style.display = 'none';

        // Insert HTML editor after Quill container
        document.getElementById('editor-container').parentNode.insertBefore(htmlTextarea, document.getElementById('editor-container').nextSibling);

        // Create toggle button
        var toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-sm btn-outline-secondary mt-2';
        toggleButton.innerHTML = '<i class="fa fa-code"></i> HTML Source';
        toggleButton.style.marginBottom = '10px';

        // Insert button before editor
        document.getElementById('editor-container').parentNode.insertBefore(toggleButton, document.getElementById('editor-container'));

        toggleButton.addEventListener('click', function () {
            if (!htmlEditorActive) {
                // Switch to HTML mode
                htmlTextarea.value = quill.root.innerHTML;
                document.getElementById('editor-container').style.display = 'none';
                htmlTextarea.style.display = 'block';
                toggleButton.innerHTML = '<i class="fa fa-eye"></i> Visual Editor';
                htmlEditorActive = true;
            } else {
                // Switch back to visual mode
                quill.root.innerHTML = htmlTextarea.value;
                htmlTextarea.style.display = 'none';
                document.getElementById('editor-container').style.display = 'block';
                toggleButton.innerHTML = '<i class="fa fa-code"></i> HTML Source';
                htmlEditorActive = false;
            }
        });
    </script>
@endpush