<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Eager load the subscriptions ordered by latest first
        $subscriptions = $user->vipSubscriptions()->with('vipPlan')->latest()->get();
        $activeSubscription = $user->activeSubscription;

        $metadata = \App\Models\Metadata::where('route_name', 'account.index')->pluck('value', 'key');
        
        return view('account.index', compact('user', 'subscriptions', 'activeSubscription', 'metadata'));
    }
}
