@extends('admin.layout')

@section('page-title', 'SEO Pages')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Manage SEO Pages</span>
                <a href="{{ route('admin.seo-pages.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> New Page
                </a>
            </div>

            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seoPages as $page)
                            <tr>
                                <td>{{ $page->id }}</td>
                                <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $page->title }}</td>
                                <td style="color:rgba(255,255,255,0.4);font-size:12px;">{{ $page->slug }}</td>
                                <td>
                                    @if($page->is_active)
                                        <span class="as-table-badge success">Active</span>
                                    @else
                                        <span class="as-table-badge secondary">Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.seo-pages.edit', $page) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.seo-pages.destroy', $page) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="as-action-btn delete" onclick="showDeleteModal(this.closest('form'), 'SEO page')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="as-empty">
                                        <div class="as-empty-icon"><i class="fa fa-google"></i></div>
                                        <div class="as-empty-title">No SEO pages found</div>
                                        <div class="as-empty-text">Create SEO-optimized pages for your site.</div>
                                        <a href="{{ route('admin.seo-pages.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                            New Page
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($seoPages->hasPages())
                <div class="as-pagination">{{ $seoPages->links() }}</div>
            @endif
        </div>
    </div>
@endsection
