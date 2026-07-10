<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function show()
    {
        $admin = AdminUser::find(session('admin_id'));
        return view('admin.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = AdminUser::find(session('admin_id'));

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        session(['admin_name' => $admin->name]);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
}
