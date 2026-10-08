<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminArticleCategoryController;
use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMetadataController;
use App\Http\Controllers\Admin\AdminPerformanceController;
use App\Http\Controllers\Admin\AdminPredictionController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminScriptController;
use App\Http\Controllers\Admin\AdminSeoPageController; // Added this line
use App\Http\Controllers\Admin\AdminSiteLinkController;
use App\Http\Controllers\Admin\AdminVipPlanController;
use App\Http\Controllers\Admin\AdminVipResultController;
use App\Http\Controllers\Admin\AdminVipSubscriptionController;
use App\Http\Controllers\Admin\FixturesController;
use App\Http\Controllers\Admin\ManageAdminController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\FixtureSearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\RecentWinningsController;
use App\Http\Controllers\SeoPageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\VipController;
use App\Http\Controllers\VipResultController;
use App\Models\Prediction;
use Illuminate\Support\Facades\Route;

$mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
$rootDomain = preg_replace('/^www\./', '', $mainDomain);

// Redirect non-www (apex domain) to www
Route::domain($rootDomain)->group(function () {
    Route::any('/{any?}', function ($any = '') {
        $query = request()->getQueryString();
        $redirect = 'https://'.parse_url(config('app.url'), PHP_URL_HOST).'/'.$any;
        if ($query) {
            $redirect .= '?'.$query;
        }

        return redirect()->away($redirect, 301);
    })->where('any', '.*');
});

// Public routes (main domain only)
Route::domain($mainDomain)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home/{date}', [HomeController::class, 'index'])->where('date', '\d{4}-\d{2}-\d{2}')->name('home.date');
    // Redirect old /category/ URLs to new short URLs
    Route::get('/category/{categorySlug}', function ($categorySlug) {
        return redirect()->to('/'.$categorySlug, 301);
    });
    Route::get('/category/{categorySlug}/{date}', function ($categorySlug, $date) {
        return redirect()->to('/'.$categorySlug.'/'.$date, 301);
    })->where('date', '\d{4}-\d{2}-\d{2}');

    // Banker Predictions (must be before the category catch-all)
    Route::get('/banker-tips', [HomeController::class, 'bankerPredictions'])->name('banker.show');
    Route::get('/banker-tips/{date}', [HomeController::class, 'bankerPredictions'])->where('date', '\d{4}-\d{2}-\d{2}')->name('banker.date');

    // Short category URLs (e.g. /double-chance instead of /category/double-chance)
    Route::get('/{categorySlug}', [HomeController::class, 'category'])->where('categorySlug', 'double-chance|over-1-5-goals|over-2-5-goals|btts-gg|draws|home-wins|away-wins|correct-score')->name('category.show');
    Route::get('/{categorySlug}/{date}', [HomeController::class, 'category'])->where('categorySlug', 'double-chance|over-1-5-goals|over-2-5-goals|btts-gg|draws|home-wins|away-wins|correct-score')->where('date', '\d{4}-\d{2}-\d{2}')->name('category.date');
    // Match detail pages - dated canonical URL
    Route::get('/match/{slug}/{date}', [FixtureController::class, 'show'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('fixture.show');

    // Legacy /match/{slugOrId} redirect to dated canonical URL
    Route::get('/match/{slugOrId}', function ($slugOrId) {
        $prediction = Prediction::where('slug', $slugOrId)->first();

        if (! $prediction && ctype_digit((string) $slugOrId)) {
            $prediction = Prediction::canonicalForFixture($slugOrId)
                ?? Prediction::where('id', $slugOrId)->first();
        }

        if ($prediction && $prediction->slug && $prediction->match_date) {
            return redirect()->to(
                '/match/'.$prediction->slug.'/'.$prediction->match_date->format('Y-m-d'),
                301
            );
        }

        // A URL that never represented a real match must 404, not soft-redirect home.
        abort(404);
    });
    Route::get('/fixtures/search', [FixtureSearchController::class, 'search'])->name('fixtures.search');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{slug}', function ($slug) {
        return redirect()->to('/blog/'.$slug, 301);
    });
    Route::get('/blog/{slug}', [BlogController::class, 'resolve'])->name('blog.show');
    Route::get('/vip', [VipController::class, 'index'])->name('vip.index');
    Route::get('/vip-results', [VipResultController::class, 'index'])->name('vip-results.index');
    Route::get('/vip/games', [VipController::class, 'games'])->name('vip.games')->middleware('auth');
    Route::get('/vip/{vipPlan}/checkout', [VipController::class, 'checkout'])->name('vip.checkout');
    Route::post('/vip/{vipPlan}/pay', [VipController::class, 'initializePayment'])->name('vip.pay');
    Route::post('/vip/{vipPlan}/crypto', [VipController::class, 'cryptoCheckout'])->name('vip.crypto');
    Route::get('/vip/payment/callback', [VipController::class, 'paymentCallback'])->name('vip.payment.callback');
    Route::get('/recent-winnings', [RecentWinningsController::class, 'index'])->name('recent-winnings.index');
    Route::get('/league/{id}/{slug?}', [LeagueController::class, 'show'])->name('league.show');
    Route::get('/predictions/all', [HomeController::class, 'allPredictions'])->name('predictions.all');
    Route::get('/predictions/all/{date}', [HomeController::class, 'allPredictions'])->where('date', '\d{4}-\d{2}-\d{2}')->name('predictions.all.date');
    Route::get('/country/{name}', [CountryController::class, 'show'])->name('country.show');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms-and-conditions', [HomeController::class, 'terms'])->name('terms');

    Route::get('/worldcup-2026', [HomeController::class, 'worldcup'])->name('worldcup.index');
    Route::get('/worldcup-2026/{date}', [HomeController::class, 'worldcup'])->where('date', '\d{4}-\d{2}-\d{2}')->name('worldcup.date');

    // Sitemaps
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('/sitemaps/main.xml', [SitemapController::class, 'main'])->name('sitemap.main');
    Route::get('/sitemaps/blog.xml', [SitemapController::class, 'blog'])->name('sitemap.blog');
    Route::get('/sitemaps/seo.xml', [SitemapController::class, 'seo'])->name('sitemap.seo');
    Route::get('/sitemaps/predictions.xml', [SitemapController::class, 'predictions'])->name('sitemap.predictions');

    Route::get('/cookie/pay', [CookieConsentController::class, 'payToReject'])->name('cookie.pay');
    Route::get('/cookie/payment/callback', [CookieConsentController::class, 'paymentCallback'])->name('cookie.payment.callback');

    Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe.store');
    Route::post('/blog/{slug}/comment', [CommentController::class, 'store'])->name('blog.comment.store');
    Route::post('/match/{slugOrId}/comment', [CommentController::class, 'storeForPrediction'])->name('prediction.comment.store');

    // User Authentication routes
    Route::middleware('guest')->group(function () {
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    });

    // SEO catch-all route (updated to support dates)
    Route::get('/{slug}/{date?}', [SeoPageController::class, 'show'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('seo.show');
}); // End main domain group

// Admin routes (subdomain only)
Route::domain('admin.'.$rootDomain)->name('admin.')->group(function () {
    // Redirect root to login
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });

    // Login routes (accessible without auth)
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Performance
        Route::get('/performance', [AdminPerformanceController::class, 'index'])->name('performance');

        // Fixtures
        Route::get('/fixtures', [FixturesController::class, 'index'])->name('fixtures.index');
        Route::post('/fixtures/refresh', [FixturesController::class, 'refresh'])->name('fixtures.refresh');

        // Prediction management
        Route::post('/predictions/bulk-delete', [AdminPredictionController::class, 'bulkDestroy'])->name('predictions.bulk-delete');
        Route::post('/predictions/bulk-banker', [AdminPredictionController::class, 'bulkBanker'])->name('predictions.bulk-banker');

        // Super Admin Routes
        Route::middleware(['super.admin'])->group(function () {
            Route::resource('admins', ManageAdminController::class)->except(['show']);
        });
        Route::resource('predictions', AdminPredictionController::class)->except(['show']);
        Route::post('/predictions/{prediction}/check-status', [AdminPredictionController::class, 'checkStatus'])->name('predictions.check-status');
        Route::get('/predictions/update-status', [AdminPredictionController::class, 'showUpdateStatusPage'])->name('predictions.update-status');
        Route::post('/predictions/update-status', [AdminPredictionController::class, 'updateAllStatuses'])->name('predictions.update-status.run');
        Route::post('/predictions/send-notifications', [AdminPredictionController::class, 'sendBatchedNotifications'])->name('predictions.send-notifications');
        Route::get('/predictions/bankers', [AdminPredictionController::class, 'bankers'])->name('predictions.bankers');
        Route::post('/predictions/{prediction}/toggle-banker', [AdminPredictionController::class, 'toggleBanker'])->name('predictions.toggle-banker');

        // Article management
        Route::resource('articles', AdminArticleController::class)->except(['show']);
        Route::resource('article-categories', AdminArticleCategoryController::class)->except(['show']);

        // VIP Plans management
        Route::resource('vip-plans', AdminVipPlanController::class)->except(['show']);
        Route::post('vip-results/{vipResult}/publish', [AdminVipResultController::class, 'publish'])->name('vip-results.publish');
        Route::resource('vip-results', AdminVipResultController::class)->except(['show']);
        Route::post('vip-subscriptions/bulk-delete', [AdminVipSubscriptionController::class, 'bulkDestroy'])->name('vip-subscriptions.bulk-delete');
        Route::resource('vip-subscriptions', AdminVipSubscriptionController::class)->except(['show']);

        Route::resource('site-links', AdminSiteLinkController::class);
        Route::resource('scripts', AdminScriptController::class);

        // SEO and Metadata
        Route::resource('seo-pages', AdminSeoPageController::class)->except(['show']);
        Route::post('/metadata/flush', [AdminMetadataController::class, 'flush'])->name('metadata.flush');
        Route::resource('metadata', AdminMetadataController::class)->except(['show']);

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::put('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
        Route::post('/settings/email/test', [SettingsController::class, 'sendTestEmail'])->name('settings.email.test');
        Route::put('/settings/telegram', [SettingsController::class, 'updateTelegram'])->name('settings.telegram.update');
        Route::post('/settings/telegram/test', [SettingsController::class, 'testTelegram'])->name('settings.telegram.test');
        Route::put('/settings/prediction', [SettingsController::class, 'updatePrediction'])->name('settings.prediction.update');

        Route::post('/fetch-fixtures', [AdminPredictionController::class, 'fetchFixtures'])->name('fetch-fixtures');
        Route::post('/fetch-odds', [AdminPredictionController::class, 'fetchOdds'])->name('fetch-odds');
        Route::post('/fetch-team-form', [AdminPredictionController::class, 'fetchTeamForm'])->name('fetch-team-form');
    });
});
