@extends('admin.layout')

@section('page-title', 'Script Management')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Script Management</span>
                <a href="{{ route('admin.scripts.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Script
                </a>
            </div>

            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th style="padding-left:18px;">Title</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scripts as $script)
                            <tr>
                                <td style="padding-left:18px;font-weight:600;color:rgba(255,255,255,0.85);">{{ $script->title }}</td>
                                <td>
                                    @if($script->position === 'head')
                                        <span class="as-table-badge info text-uppercase">Head</span>
                                    @elseif($script->position === 'body')
                                        <span class="as-table-badge warning text-uppercase">Body</span>
                                    @elseif($script->position === 'banner')
                                        <span class="as-table-badge success text-uppercase">Banner</span>
                                    @else
                                        <span class="as-table-badge secondary text-uppercase">{{ $script->position }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($script->is_active)
                                        <span class="as-table-badge success">Active</span>
                                    @else
                                        <span class="as-table-badge danger">Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.scripts.edit', $script) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.scripts.destroy', $script) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="as-action-btn delete"
                                                onclick="showDeleteModal(this.closest('form'), 'script')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="as-empty">
                                        <div class="as-empty-icon"><i class="fa fa-code"></i></div>
                                        <div class="as-empty-title">No scripts found</div>
                                        <div class="as-empty-text">Add your first tracking or custom script.</div>
                                        <a href="{{ route('admin.scripts.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                            Add Script
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($scripts->hasPages())
                <div class="as-pagination">{{ $scripts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
