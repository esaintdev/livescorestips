<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function payToReject(Request $request)
    {
        $price = (float) Setting::get('cookie_price', '10');
        if ($price <= 0) {
            return redirect()->route('home')->with('error', 'Pay to Reject is currently disabled.');
        }

        $reference = 'COOKIE_' . uniqid() . '_' . time();
        session(['cookie_pay_ref' => $reference]);

        $callbackUrl = route('cookie.payment.callback');

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.flutterwave.secret_key') ?? env('FLW_SECRET_KEY'))
                ->post('https://api.flutterwave.com/v3/payments', [
                    'tx_ref' => $reference,
                    'amount' => $price,
                    'currency' => 'GBP',
                    'redirect_url' => $callbackUrl,
                    'customer' => [
                        'email' => $request->input('email', 'guest@livescorestips.com'),
                        'name' => 'Guest User',
                    ],
                    'meta' => [
                        'type' => 'cookie_pay_to_reject',
                    ],
                    'customizations' => [
                        'title' => config('app.name') . ' - Cookie Preference',
                        'description' => 'Pay to Reject Cookies - £' . number_format($price, 2),
                    ],
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return redirect()->away($response->json('data.link'));
            }

            \Illuminate\Support\Facades\Log::error('Cookie pay-to-reject init failed', ['response' => $response->json()]);
            return redirect()->route('home')->with('error', 'Could not initialize payment. Please try again.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cookie pay-to-reject exception: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred connecting to the payment gateway.');
        }
    }

    public function paymentCallback(Request $request)
    {
        $status = $request->query('status');
        $txRef = $request->query('tx_ref');
        $transactionId = $request->query('transaction_id');

        if ($status !== 'successful' && $status !== 'completed') {
            return redirect()->route('home')->with('error', 'Payment was cancelled or failed.');
        }

        if (!$txRef || !$transactionId) {
            return redirect()->route('home')->with('error', 'Invalid payment callback parameters.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.flutterwave.secret_key') ?? env('FLW_SECRET_KEY'))
                ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

            if ($response->successful() && $response->json('status') === 'success' && $response->json('data.status') === 'successful') {
                session(['cookie_paid' => true]);
                return redirect()->route('home', ['cookie_paid' => 'success']);
            }

            \Illuminate\Support\Facades\Log::error('Cookie payment verification failed', ['response' => $response->json()]);
            return redirect()->route('home')->with('error', 'Payment verification failed. Please contact support.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cookie payment verification exception: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred during payment verification.');
        }
    }
}
