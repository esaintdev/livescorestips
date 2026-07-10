<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        $metadata = \App\Models\Metadata::where('route_name', 'auth.login')->pluck('value', 'key');
        return view('auth.login', compact('metadata'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'                => ['required', 'email'],
            'password'             => ['required'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['redirect' => route('account.index')]);
            }

            return redirect()->intended(route('account.index'))->with('success', 'Welcome back!');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.']
                ]
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }
}
