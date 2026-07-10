<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManageAdminController extends Controller
{
    public function index()
    {
        $admins = AdminUser::all();
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,super_admin',
        ]);

        AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(AdminUser $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, AdminUser $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admin_users')->ignore($admin->id)],
            'role' => 'required|in:admin,super_admin',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user updated successfully.');
    }

    public function destroy(AdminUser $admin)
    {
        if ($admin->id === session('admin_id')) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin user deleted successfully.');
    }
}
