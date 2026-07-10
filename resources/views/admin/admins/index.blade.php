@extends('admin.layout')

@section('page-title', 'Manage Admins')

@section('content')
    <div class="as-card">
        <div class="as-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:15px;font-weight:700;color:#fff;">Administrators List</span>
                <a href="{{ route('admin.admins.create') }}" class="as-btn" style="gap:6px;padding:7px 16px;font-size:12px;">
                    <i class="fa fa-plus"></i> Add New Admin
                </a>
            </div>

            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admins as $admin)
                            <tr>
                                <td style="font-weight:700;color:rgba(255,255,255,0.85);">{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    @if ($admin->role === 'super_admin')
                                        <span class="as-table-badge primary">Super Admin</span>
                                    @else
                                        <span class="as-table-badge secondary">Admin</span>
                                    @endif
                                </td>
                                <td>{{ $admin->created_at->format('M d, Y') }}</td>
                                <td style="text-align:right;padding-right:18px;">
                                    <div class="as-action-group">
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="as-action-btn edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if ($admin->id !== session('admin_id'))
                                            <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="button" class="as-action-btn delete"
                                                    onclick="showDeleteModal(this.form, '{{ $admin->name }}')" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
