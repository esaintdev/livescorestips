<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use App\Services\TelegramService;

class SettingsController extends Controller
{
    public function index()
    {
        $emailSettings = Setting::getGroup('email');
        $telegramSettings = Setting::getGroup('telegram');
        $generalSettings = Setting::getGroup('general');
        $predictionSettings = Setting::getGroup('prediction');
        $settings = Setting::first();

        return view('admin.settings.index', compact('emailSettings', 'telegramSettings', 'generalSettings', 'predictionSettings', 'settings'));
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_logo_light' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'cookie_price' => 'nullable|numeric|min:0',
        ]);

        Setting::set('site_name', $request->site_name, 'general', 'text');

        if ($request->hasFile('site_logo')) {
            $image = $request->file('site_logo');
            $name = 'logo_' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            
            Setting::set('site_logo', 'images/' . $name, 'general', 'image');
        }

        if ($request->hasFile('site_logo_light')) {
            $image = $request->file('site_logo_light');
            $name = 'logo_light_' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);

            Setting::set('site_logo_light', 'images/' . $name, 'general', 'image');
        }

        if ($request->hasFile('og_image')) {
            $image = $request->file('og_image');
            $name = 'og_image_' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);

            Setting::set('og_image', 'images/' . $name, 'general', 'image');
        }

        Setting::set('cookie_price', $request->cookie_price ?? '0', 'general', 'number');

        // Clear settings cache
        \Cache::forget('general_settings');

        return back()->with('success', 'General settings updated successfully!');
    }

    public function updatePrediction(Request $request)
    {
        $request->validate([
            'priority_leagues' => 'nullable|string',
        ]);

        Setting::set('priority_leagues', $request->priority_leagues, 'prediction', 'text');

        // Clear settings cache
        \Cache::forget('prediction_settings');

        return back()->with('success', 'Prediction settings updated successfully!');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            
            // Advert
            'mail_advert_username' => 'nullable|string',
            'mail_advert_password' => 'nullable|string',
            'mail_advert_from_address' => 'nullable|email',
            'mail_advert_from_name' => 'nullable|string',

            // Info
            'mail_info_username' => 'nullable|string',
            'mail_info_password' => 'nullable|string',
            'mail_info_from_address' => 'nullable|email',
            'mail_info_from_name' => 'nullable|string',
        ]);

        // Save email settings to database
        Setting::set('mail_driver', $request->mail_driver, 'email', 'text');
        Setting::set('mail_host', $request->mail_host, 'email', 'text');
        Setting::set('mail_port', $request->mail_port, 'email', 'number');
        Setting::set('mail_username', $request->mail_username, 'email', 'text');
        Setting::set('mail_password', $request->mail_password, 'email', 'password');
        Setting::set('mail_encryption', $request->mail_encryption, 'email', 'text');
        Setting::set('mail_from_address', $request->mail_from_address, 'email', 'text');
        Setting::set('mail_from_name', $request->mail_from_name, 'email', 'text');

        // Save Advert Settings
        Setting::set('mail_advert_username', $request->mail_advert_username, 'email', 'text');
        Setting::set('mail_advert_password', $request->mail_advert_password, 'email', 'password');
        Setting::set('mail_advert_from_address', $request->mail_advert_from_address, 'email', 'text');
        Setting::set('mail_advert_from_name', $request->mail_advert_from_name, 'email', 'text');

        // Save Info Settings
        Setting::set('mail_info_username', $request->mail_info_username, 'email', 'text');
        Setting::set('mail_info_password', $request->mail_info_password, 'email', 'password');
        Setting::set('mail_info_from_address', $request->mail_info_from_address, 'email', 'text');
        Setting::set('mail_info_from_name', $request->mail_info_from_name, 'email', 'text');

        // Clear the cache to ensure new settings take effect immediately
        \Cache::forget('email_settings');

        // Update runtime configuration for the default mailer
        Config::set('mail.default', $request->mail_driver);
        Config::set('mail.mailers.smtp.host', $request->mail_host);
        Config::set('mail.mailers.smtp.port', $request->mail_port);
        Config::set('mail.mailers.smtp.username', $request->mail_username);
        Config::set('mail.mailers.smtp.password', $request->mail_password);
        Config::set('mail.mailers.smtp.encryption', $request->mail_encryption);
        Config::set('mail.from.address', $request->mail_from_address);
        Config::set('mail.from.name', $request->mail_from_name);

        return back()->with('success', 'All email settings updated successfully!');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
            'test_mailer' => 'nullable|string'
        ]);

        $mailer = $request->input('test_mailer', 'smtp');
        $settings = Setting::getGroup('email');

        // Determine from address/name
        $fromAddress = $settings['mail_from_address'] ?? config('mail.from.address');
        $fromName = $settings['mail_from_name'] ?? config('mail.from.name');

        if ($mailer === 'smtp_advert') {
            $fromAddress = $settings['mail_advert_from_address'] ?? $fromAddress;
            $fromName = $settings['mail_advert_from_name'] ?? $fromName;
        } elseif ($mailer === 'smtp_info') {
            $fromAddress = $settings['mail_info_from_address'] ?? $fromAddress;
            $fromName = $settings['mail_info_from_name'] ?? $fromName;
        }

        try {
            \Mail::mailer($mailer)->send('emails.test', [
                'testMessage' => 'This is a test email to verify your SMTP configuration for the ' . str_replace('smtp_', '', $mailer) . ' account.'
            ], function ($msg) use ($request, $fromAddress, $fromName) {
                $msg->to($request->test_email)
                    ->from($fromAddress, $fromName)
                    ->subject('SMTP Configuration Test - bet365');
            });

            return back()->with('success', 'Test email sent successfully using ' . $mailer . ' (from: ' . $fromAddress . ') to ' . $request->test_email);
        } catch (\Exception $e) {
            return back()->withErrors(['test_email' => 'Failed to send test email (' . $mailer . '): ' . $e->getMessage()]);
        }
    }

    public function updateTelegram(Request $request)
    {
        $request->validate([
            'telegram_bot_token'  => 'nullable|string',
            'telegram_channel_id' => 'nullable|string',
            'telegram_enabled'    => 'nullable|boolean',
            'telegram_categories' => 'nullable|string',
            'telegram_link'       => 'nullable|url|max:255',
        ]);

        // Save telegram settings to database using key-value pattern
        Setting::set('telegram_bot_token', $request->telegram_bot_token, 'telegram', 'password');
        Setting::set('telegram_channel_id', $request->telegram_channel_id, 'telegram', 'text');
        Setting::set('telegram_enabled', $request->has('telegram_enabled') ? '1' : '0', 'telegram', 'boolean');
        Setting::set('telegram_categories', $request->telegram_categories, 'telegram', 'text');
        Setting::set('telegram_link', $request->telegram_link, 'telegram', 'text');

        // Clear telegram settings cache so changes take effect immediately
        \Cache::forget('telegram_settings');

        return back()->with('success', 'Telegram settings updated successfully!');
    }

    public function testTelegram()
    {
        try {
            $telegramService = new TelegramService();
            $result = $telegramService->testConnection();

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->withErrors(['telegram' => $result['message']]);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['telegram' => 'Connection failed: ' . $e->getMessage()]);
        }
    }
}
