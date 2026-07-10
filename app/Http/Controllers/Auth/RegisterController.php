<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        $metadata = \App\Models\Metadata::where('route_name', 'auth.register')->pluck('value', 'key');
        return view('auth.register', compact('metadata'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'                => ['required', 'string', 'max:20'],
            'password'             => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        $redirect = $request->has('plan_id')
            ? route('vip.checkout', ['vipPlan' => $request->plan_id])
            : route('account.index');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['redirect' => $redirect]);
        }

        return redirect()->to($redirect)->with('success', 'Account created successfully. Welcome to ' . config('app.name') . '!');
    }
}
