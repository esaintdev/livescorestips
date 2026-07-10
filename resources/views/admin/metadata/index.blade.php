@extends('admin.layout')

@section('page-title', 'SEO Metadata')

@section('content')
    <div class="as-page">
        <section class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon">
                    <i class="fa fa-tags"></i>
                </div>
                <div>
                    <h2 class="as-section-title">SEO Metadata</h2>
                    <p class="as-section-subtitle">Add and manage SEO meta data for your website pages</p>
                </div>
            </div>

            <div class="as-card">
                <div class="as-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4" style="gap:16px;">
                        <div style="flex:1;">
                            <label class="as-label">Page Route</label>
                            <select class="form-control"
                                onchange="window.location.href='{{ route('admin.metadata.index') }}?page='+this.value">
                                @foreach($pages as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach($items as $key => $label)
                                            <option value="{{ $key }}" {{ $currentRoute == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div style="padding-top:22px;">
                            <form action="{{ route('admin.metadata.flush') }}" method="POST">
                                @csrf
                                <button type="button" class="as-btn as-btn-secondary" style="gap:6px;padding:7px 16px;font-size:12px;"
                                    onclick="showConfirmModal(this.form, {title:'Delete All Meta Data', message:'WARNING: This will delete ALL meta data from the website. Are you sure?', btnText:'Delete All', btnIcon:'fa-trash', color:'#ef4444', subtext:'This action cannot be undone.'})">
                                    <i class="fa fa-trash"></i> Flush All
                                </button>
                            </form>
                        </div>
                    </div>

                    <form action="{{ route('admin.metadata.store') }}" method="POST" id="metadata-form">
                        @csrf
                        <input type="hidden" name="route_name" value="{{ $currentRoute }}">

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Page Title (Browser Tab)</label>
                                <input type="text" name="page_title" class="form-control"
                                    value="{{ $metadata['page_title'] ?? '' }}" placeholder="Browser Title">
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Main Page Title (H1 Heading)</label>
                                <input type="text" name="page_subheader" class="form-control"
                                    value="{{ $metadata['page_subheader'] ?? '' }}" placeholder="Page Title">
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Page Subheader / Description (Below H1)</label>
                                <textarea name="page_description" class="form-control" rows="3"
                                    placeholder="Brief description under the main title">{{ $metadata['page_description'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">SEO Intro Text (Below Description)</label>
                                <textarea name="header_intro" class="form-control" rows="2"
                                    placeholder="Short intro text shown between the description and filters">{{ $metadata['header_intro'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Page Keywords</label>
                                <input type="text" name="page_keywords" class="form-control"
                                    value="{{ $metadata['page_keywords'] ?? '' }}" placeholder="keyword1, keyword2">
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">SEO Section Header (Bottom)</label>
                                <input type="text" name="header_content" class="form-control"
                                    value="{{ $metadata['header_content'] ?? '' }}" placeholder="Header Content">
                            </div>
                        </div>

                        <div class="as-divider"></div>

                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">SEO Article Content (Bottom)</label>
                                <div id="editor-container" style="border-radius:12px;overflow:hidden;">{!! $metadata['footer_content'] ?? '' !!}</div>
                                <textarea id="footer_content" name="footer_content" style="display:none"></textarea>
                            </div>
                        </div>

                        <div class="as-card-footer" style="margin-top:24px;margin-left:-32px;margin-right:-32px;margin-bottom:-28px;">
                            <button type="submit" class="as-btn">
                                <i class="fa fa-check"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor-container { height: 300px; }
        .ql-toolbar.ql-snow { border-radius: 12px 12px 0 0 !important; }
        .ql-container.ql-snow { border-radius: 0 0 12px 12px !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Enter footer SEO content...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ]
            }
        });

        var form = document.getElementById('metadata-form');
        form.addEventListener('submit', function (e) {
            var content = document.getElementById('footer_content');
            content.value = quill.root.innerHTML;
        });
    </script>
@endpush
