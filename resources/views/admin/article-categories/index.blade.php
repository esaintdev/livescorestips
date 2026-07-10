@extends('admin.layout')

@section('page-title', 'Blog Categories')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">All Categories</span>
                <a href="{{ route('admin.article-categories.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Category
                </a>
            </div>

            @if(session('success'))
                <div style="background:rgba(16,185,129,0.08);color:#10b981;border-radius:12px;padding:12px 16px;font-weight:600;font-size:13px;margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Articles Count</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $category->name }}</td>
                                <td><code style="background:rgba(255,255,255,0.05);padding:3px 10px;border-radius:6px;font-size:12px;color:rgba(255,255,255,0.5);">{{ $category->slug }}</code></td>
                                <td><span class="as-table-badge info">{{ $category->articles_count }}</span></td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.article-categories.edit', $category) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.article-categories.destroy', $category) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="as-action-btn delete" onclick="showDeleteModal(this.closest('form'), 'category')" title="Delete">
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
                                        <div class="as-empty-icon"><i class="fa fa-folder-open-o"></i></div>
                                        <div class="as-empty-title">No categories found</div>
                                        <div class="as-empty-text">Create your first blog category.</div>
                                        <a href="{{ route('admin.article-categories.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                            Add Category
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="as-pagination">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
@endsection
