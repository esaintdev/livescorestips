<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'                => 'required|email',
            'password'             => 'required',
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_role' => $admin->role,
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $admin->name);
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout()
    {
        session()->forget(['admin_id', 'admin_name', 'admin_role']);
        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}
