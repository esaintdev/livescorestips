<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        Paginator::useBootstrap();

        try {
            if (\Schema::hasTable('settings')) {
                // Cache settings for 1 hour to reduce DB calls
                $settings = \Cache::remember('email_settings', 3600, function () {
                    return \App\Models\Setting::getGroup('email');
                });

                // Load General Settings
                $generalSettings = \Cache::remember('general_settings', 3600, function () {
                    return \App\Models\Setting::getGroup('general');
                });

                // Apply Website Name
                if (!empty($generalSettings['site_name'])) {
                    \Config::set('app.name', $generalSettings['site_name']);
                }

                // Share Website Logo with all views
                \View::share('siteLogo', $generalSettings['site_logo'] ?? 'images/logo.png');
                \View::share('siteLogoLight', $generalSettings['site_logo_light'] ?? 'images/logo.png');
                \View::share('ogImage', $generalSettings['og_image'] ?? 'images/icon.png');

                // Load Telegram Settings and share the public link globally
                $telegramSettings = \Cache::remember('telegram_settings', 3600, function () {
                    return \App\Models\Setting::getGroup('telegram');
                });
                \View::share('telegramLink', $telegramSettings['telegram_link'] ?? 'https://t.me/sportvillaprediction');


                if (!empty($settings['mail_driver'])) {
                    \Config::set('mail.default', $settings['mail_driver']);
                }

                if (!empty($settings['mail_host'])) {
                    \Config::set('mail.mailers.smtp.host', $settings['mail_host']);
                }

                if (!empty($settings['mail_port'])) {
                    \Config::set('mail.mailers.smtp.port', $settings['mail_port']);
                }

                if (!empty($settings['mail_username'])) {
                    \Config::set('mail.mailers.smtp.username', $settings['mail_username']);
                }

                if (!empty($settings['mail_password'])) {
                    \Config::set('mail.mailers.smtp.password', $settings['mail_password']);
                }

                if (!empty($settings['mail_encryption'])) {
                    \Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption']);
                }

                if (!empty($settings['mail_from_address'])) {
                    \Config::set('mail.from.address', $settings['mail_from_address']);
                }

                if (!empty($settings['mail_from_name'])) {
                    \Config::set('mail.from.name', $settings['mail_from_name']);
                }

                // --- Configure Secondary Mailers ---

                // Advert Mailer
                if (!empty($settings['mail_advert_username'])) {
                    \Config::set('mail.mailers.smtp_advert', [
                        'transport' => 'smtp',
                        'host' => $settings['mail_host'] ?? \Config::get('mail.mailers.smtp.host'),
                        'port' => $settings['mail_port'] ?? \Config::get('mail.mailers.smtp.port'),
                        'encryption' => $settings['mail_encryption'] ?? \Config::get('mail.mailers.smtp.encryption'),
                        'username' => $settings['mail_advert_username'],
                        'password' => $settings['mail_advert_password'] ?? '',
                        'timeout' => null,
                        'local_domain' => \Config::get('mail.mailers.smtp.local_domain'),
                    ]);
                }

                // Info/Support Mailer
                if (!empty($settings['mail_info_username'])) {
                    \Config::set('mail.mailers.smtp_info', [
                        'transport' => 'smtp',
                        'host' => $settings['mail_host'] ?? \Config::get('mail.mailers.smtp.host'),
                        'port' => $settings['mail_port'] ?? \Config::get('mail.mailers.smtp.port'),
                        'encryption' => $settings['mail_encryption'] ?? \Config::get('mail.mailers.smtp.encryption'),
                        'username' => $settings['mail_info_username'],
                        'password' => $settings['mail_info_password'] ?? '',
                        'timeout' => null,
                        'local_domain' => \Config::get('mail.mailers.smtp.local_domain'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log error or ignore if DB is not ready
        }

        // Share site links with footer view
        View::composer('components.layout.footer', function ($view) {
            try {
                if (\Schema::hasTable('site_links')) {
                    $partnerLinks = \App\Models\SiteLink::partners()->active()->get();
                    $footerLinks = \App\Models\SiteLink::footer()->active()->get();
                    $view->with(compact('partnerLinks', 'footerLinks'));
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        });

        // Share scripts with layout views
        View::composer(['components.layout.head', 'layouts.app'], function ($view) {
            try {
                if (\Schema::hasTable('scripts')) {
                    $headScripts = \App\Models\Script::head()->active()->get();
                    $view->with('headScripts', $headScripts);
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        });

        View::composer(['components.layout.footer', 'layouts.app'], function ($view) {
            try {
                if (\Schema::hasTable('scripts')) {
                    $bodyScripts = \App\Models\Script::body()->active()->get();
                    $view->with('bodyScripts', $bodyScripts);
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        });

        View::composer(['layouts.app'], function ($view) {
            try {
                if (\Schema::hasTable('scripts')) {
                    $bannerScripts = \App\Models\Script::banner()->active()->get();
                    $view->with('bannerScripts', $bannerScripts);
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        });

        // Share Sidebar data
        View::composer('components.layout.sidebar', function ($view) {
            $apiService = app(\App\Services\ApiFootballService::class);
            
            try {
                $topLeagues = $apiService->getPopularLeagues() ?? [];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Sidebar TopLeagues Error: ' . $e->getMessage());
                $topLeagues = [];
            }
            
            try {
                $countries = $apiService->fetchCountries() ?? [];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Sidebar Countries Error: ' . $e->getMessage());
                $countries = [];
            }
            
            $view->with([
                'topLeagues' => $topLeagues,
                'countries' => $countries,
            ]);
        });
    }
}
