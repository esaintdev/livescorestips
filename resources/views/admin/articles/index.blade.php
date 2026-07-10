@extends('admin.layout')

@section('page-title', 'Blog Articles')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Blog Articles</span>
                <a href="{{ route('admin.articles.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Create New Article
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
                            <th>Image</th>
                            <th>Title</th>
                            <th>Published At</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>{{ $article->id }}</td>
                                <td>
                                    @if($article->image)
                                        <img src="{{ Storage::disk('public')->url($article->image) }}" alt="Image" style="height:40px;border-radius:6px;object-fit:cover;">
                                    @else
                                        <span style="color:rgba(255,255,255,0.25);font-size:12px;">No Image</span>
                                    @endif
                                </td>
                                <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $article->title }}</td>
                                <td>
                                    @if($article->published_at)
                                        <span class="as-table-badge secondary">{{ $article->published_at->format('M d, Y') }}</span>
                                    @else
                                        <span class="as-table-badge warning">Draft</span>
                                    @endif
                                </td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="as-action-btn delete" onclick="showDeleteModal(this.closest('form'), 'article')" title="Delete">
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
                                        <div class="as-empty-icon"><i class="fa fa-file-text-o"></i></div>
                                        <div class="as-empty-title">No articles found</div>
                                        <div class="as-empty-text">Get started by creating your first article.</div>
                                        <a href="{{ route('admin.articles.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                            Create Article
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($articles->hasPages())
                <div class="as-pagination">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
@endsection
