<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Notifications\WelcomeSubscriberNotification;

class SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email',
        ]);

        $subscriber = Subscriber::create([
            'email' => $request->email,
            'is_active' => true,
        ]);

        // Send welcome email to the new subscriber
        try {
            $subscriber->notify(new WelcomeSubscriberNotification());
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'You have successfully subscribed to our newsletter!'
            ]);
        }

        return back()->with('success', 'You have successfully subscribed to our newsletter!');
    }
}
