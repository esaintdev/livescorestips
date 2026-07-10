@extends('admin.layout')

@section('title', 'Site Links')
@section('page-title', 'Manage Site Links')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Site Links (Partners &amp; Footer)</span>
                <a href="{{ route('admin.site-links.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Link
                </a>
            </div>

            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th style="padding-left:18px;">Title</th>
                            <th>Section</th>
                            <th>URL</th>
                            <th>Image</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($links as $link)
                            <tr>
                                <td style="padding-left:18px;font-weight:600;color:rgba(255,255,255,0.85);">{{ $link->title }}</td>
                                <td>
                                    @if($link->section === 'partner')
                                        <span class="as-table-badge info text-uppercase">Partner</span>
                                    @else
                                        <span class="as-table-badge secondary text-uppercase">Footer</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $link->url }}" target="_blank" style="color:#ff6b00;font-size:12px;text-decoration:none;">
                                        {{ Str::limit($link->url, 30) }} <i class="fa fa-external-link small ml-1"></i>
                                    </a>
                                </td>
                                <td>
                                    @if($link->image)
                                        <img src="{{ config('app.url') }}/public/{{ ltrim($link->image, '/') }}" alt="{{ $link->title }}"
                                            style="height:28px;border-radius:6px;object-fit:contain;">
                                    @else
                                        <span style="color:rgba(255,255,255,0.2);font-size:12px;">-</span>
                                    @endif
                                </td>
                                <td>{{ $link->order }}</td>
                                <td>
                                    @if($link->is_active)
                                        <span class="as-table-badge success">Active</span>
                                    @else
                                        <span class="as-table-badge danger">Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.site-links.edit', $link) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.site-links.destroy', $link) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="as-action-btn delete"
                                                onclick="showDeleteModal(this.closest('form'), 'link')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="as-empty">
                                        <div class="as-empty-icon"><i class="fa fa-link"></i></div>
                                        <div class="as-empty-title">No site links found</div>
                                        <div class="as-empty-text">Add partner or footer links to your website.</div>
                                        <a href="{{ route('admin.site-links.create') }}" class="as-btn" style="gap:6px;padding:8px 20px;font-size:12px;">
                                            Add Link
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
