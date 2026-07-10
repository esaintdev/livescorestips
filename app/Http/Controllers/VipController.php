<?php

namespace App\Http\Controllers;

use App\Models\VipPlan;
use App\Models\VipResult;
use App\Models\VipSubscription;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function index(Request $request, \App\Services\ApiFootballService $apiService, \App\Services\CurrencyService $currencyService)
    {
        $selectedCountry = $request->query('country') ?? session('selected_country');
        
        if ($request->has('country')) {
            session(['selected_country' => $selectedCountry]);
        }
        
        // Hardcoded comprehensive list of countries
        $availableCountries = [
            'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 
            'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 
            'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cambodia', 
            'Cameroon', 'Canada', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 
            'Côte d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 
            'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 'France', 
            'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 
            'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 
            'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea (North)', 'Korea (South)', 
            'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 
            'Lithuania', 'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 
            'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 
            'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Macedonia', 'Norway', 
            'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 
            'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 
            'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 
            'South Africa', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 
            'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 
            'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 
            'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'
        ];

        // Always show the default dollar plans (where country_name is NULL)
        $plans = VipPlan::active()
            ->ordered()
            ->whereNull('country_name')
            ->get()
            ->groupBy('category');

        $currencyConfig = $currencyService->getConfig($selectedCountry);
        $conversionRate = $currencyConfig['rate'];
        $currencySymbol = $currencyConfig['symbol'];
        $currencyCode = $currencyConfig['code'];

        $metadata = \App\Models\Metadata::where('route_name', 'vip.index')->pluck('value', 'key');

        return view('vip.index', compact('plans', 'metadata', 'availableCountries', 'selectedCountry', 'currencyConfig', 'conversionRate', 'currencySymbol', 'currencyCode'));
    }

    public function games()
    {
        $user = auth()->user();

        $activeSub = VipSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$activeSub) {
            return redirect()->route('vip.index')->with('error', 'You need an active VIP subscription to view VIP games.');
        }

        $games = VipResult::with('items')
            ->where('stage', 'published')
            ->whereDate('date', '>=', now()->subDay()->startOfDay())
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('vip.games', compact('games', 'activeSub'));
    }

    public function checkout(VipPlan $vipPlan)
    {
        return view('vip.checkout', compact('vipPlan'));
    }

    public function cryptoCheckout(Request $request, VipPlan $vipPlan)
    {
        $user = auth()->user();

        // Create pending subscription for Crypto
        $reference = 'VIP_CRYPTO_' . uniqid() . '_' . time();
        $subscription = VipSubscription::create([
            'user_id' => $user->id,
            'vip_plan_id' => $vipPlan->id,
            'name' => $user->name,
            'email' => $user->email,
            'payment_reference' => $reference,
            'status' => 'pending',
        ]);

        return view('vip.crypto', compact('vipPlan', 'subscription'));
    }

    public function initializePayment(Request $request, VipPlan $vipPlan, \App\Services\CurrencyService $currencyService)
    {
        $user = auth()->user();

        // Create pending subscription
        $reference = 'VIP_' . uniqid() . '_' . time();
        $subscription = VipSubscription::create([
            'user_id' => $user->id,
            'vip_plan_id' => $vipPlan->id,
            'name' => $user->name,
            'email' => $user->email,
            'payment_reference' => $reference,
            'status' => 'pending',
        ]);

        $callbackUrl = route('vip.payment.callback');

        // Dynamic Currency Conversion based on user's country
        $country = session('selected_country');
        $currencyConfig = $currencyService->getConfig($country);
        $currencyCode = $currencyConfig['code'];
        $finalAmount = $currencyService->convert((float) $vipPlan->price, $country);

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.flutterwave.secret_key') ?? env('FLW_SECRET_KEY'))
                ->post('https://api.flutterwave.com/v3/payments', [
                    'tx_ref' => $reference,
                    'amount' => $finalAmount,
                    'currency' => $currencyCode,
                    'redirect_url' => $callbackUrl,
                    'customer' => [
                        'email' => $user->email,
                        'phonenumber' => $user->phone ?? '',
                        'name' => $user->name
                    ],
                    'meta' => [
                        'subscription_id' => $subscription->id,
                        'plan_name' => $vipPlan->name,
                        'original_amount' => $vipPlan->price,
                        'original_currency' => $vipPlan->currency
                    ],
                    'customizations' => [
                        'title' => config('app.name') . ' VIP Subscription',
                        'description' => 'Payment for ' . $vipPlan->name . ' (' . $vipPlan->currency . $vipPlan->price . ')'
                    ]
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return redirect()->away($response->json('data.link'));
            }

            // Fallback if initialization fails
            \Illuminate\Support\Facades\Log::error('Flutterwave initialization failed', ['response' => $response->json()]);
            return redirect()->route('account.index')->with('error', 'Could not initialize payment gateway. Please try again.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Flutterwave exception: ' . $e->getMessage());
            return redirect()->route('account.index')->with('error', 'An error occurred connecting to the payment gateway.');
        }
    }

    public function paymentCallback(Request $request)
    {
        $status = $request->query('status');
        $txRef = $request->query('tx_ref');
        $transactionId = $request->query('transaction_id');

        if ($status !== 'successful' && $status !== 'completed') {
            return redirect()->route('account.index')->with('error', 'Payment was cancelled or failed.');
        }

        if (!$txRef || !$transactionId) {
            return redirect()->route('account.index')->with('error', 'Invalid payment callback parameters.');
        }

        try {
            // Verify transaction with Flutterwave
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.flutterwave.secret_key') ?? env('FLW_SECRET_KEY'))
                ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

            if ($response->successful() && $response->json('status') === 'success' && $response->json('data.status') === 'successful') {

                $verifiedTxRef = $response->json('data.tx_ref');
                $subscription = VipSubscription::where('payment_reference', $verifiedTxRef)->first();

                if ($subscription && $subscription->status !== 'active') {
                    $plan = $subscription->vipPlan;
                    $startsAt = now();
                    $expiresAt = $startsAt->copy()->addDays((int) ($plan->duration_days ?? 30)); // fallback to 30 if missing

                    $subscription->update([
                        'status' => 'active',
                        'starts_at' => $startsAt,
                        'expires_at' => $expiresAt,
                    ]);

                    return redirect()->route('account.index')->with('success', "Payment successful! Your {$plan->name} subscription is now active.");
                }

                return redirect()->route('account.index')->with('success', 'Payment verified successfully.');
            }

            \Illuminate\Support\Facades\Log::error('Flutterwave verification failed', ['response' => $response->json()]);
            return redirect()->route('account.index')->with('error', 'Payment verification failed. Please contact support.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Flutterwave verification exception: ' . $e->getMessage());
            return redirect()->route('account.index')->with('error', 'An error occurred during payment verification.');
        }
    }
}
