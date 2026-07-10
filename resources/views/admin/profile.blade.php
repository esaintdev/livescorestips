@extends('admin.layout')

@section('page-title', 'My Profile')

@section('content')
    <div class="as-page">
        <section class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon">
                    <i class="fa fa-user"></i>
                </div>
                <div>
                    <h2 class="as-section-title">Edit Profile</h2>
                    <p class="as-section-subtitle">Update your name, email, or password</p>
                </div>
            </div>
            <div class="as-card">
                @if ($errors->any())
                    <div class="as-card-body" style="padding-bottom:0;">
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="as-card-body">
                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Name</label>
                                <input type="text" name="name" class="as-input"
                                    value="{{ old('name', $admin->name) }}" required>
                            </div>
                        </div>
                        <div class="as-divider"></div>
                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Email</label>
                                <input type="email" name="email" class="as-input"
                                    value="{{ old('email', $admin->email) }}" required>
                            </div>
                        </div>
                        <div class="as-divider"></div>
                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">New Password</label>
                                <input type="password" name="password" class="as-input" placeholder="Leave blank to keep current">
                                <span class="as-hint">Minimum 8 characters recommended.</span>
                            </div>
                        </div>
                        <div class="as-divider"></div>
                        <div class="as-row">
                            <div class="as-field">
                                <label class="as-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="as-input" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>
                    <div class="as-card-footer">
                        <button type="submit" class="as-btn">
                            <i class="fa fa-check"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
