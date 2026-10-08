<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves fully-rendered public pages to anonymous visitors from cache.
 *
 * Crawlers and first-time visitors get millisecond responses, which removes
 * the cold API-cache spikes that throttle Googlebot. The cached HTML has the
 * CSRF token replaced by a placeholder and is re-hydrated with the current
 * request's token on serve, so forms and modals keep working. Authenticated
 * users, POST/AJAX requests and live views always bypass the cache.
 */
class CacheGuestPages
{
    private const PLACEHOLDER = '___LST_CSRF_TOKEN___';

    /** Route names eligible for guest full-page caching. */
    private array $allowed = [
        'home', 'home.date',
        'category.show', 'category.date',
        'fixture.show',
        'predictions.all', 'predictions.all.date',
        'banker.show', 'banker.date',
        'blog.index', 'blog.show',
        'league.show',
        'country.show',
        'recent-winnings.index',
        'worldcup.index', 'worldcup.date',
        'about', 'contact', 'privacy', 'terms',
        'seo.show',
        'vip.index', 'vip-results.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCache($request)) {
            return $next($request);
        }

        $store = Cache::store(config('cache.page_cache.store', 'file'));
        $key = 'pagecache:'.sha1($request->fullUrl());
        $ttl = (int) config('cache.page_cache.ttl', 120);

        $cached = $store->get($key);
        if (is_array($cached) && array_key_exists('html', $cached)) {
            $html = $cached['html'];
            $decoded = is_string($html) ? @gzuncompress($html) : false;
            if ($decoded !== false) {
                $html = $decoded;
            }
            $html = str_replace(self::PLACEHOLDER, csrf_token(), $html);

            return response($html, 200)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Page-Cache', 'HIT');
        }

        $response = $next($request);

        if ($this->isCacheableResponse($response)) {
            $html = $response->getContent();
            $token = csrf_token();

            if ($token) {
                $html = str_replace($token, self::PLACEHOLDER, $html);
            }

            // Compress so the file cache stays small on disk.
            $store->put($key, ['html' => gzcompress($html, 6)], now()->addSeconds($ttl));

            $response->header('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function shouldCache(Request $request): bool
    {
        if (! config('cache.page_cache.enabled', true)) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if (Auth::check()) {
            return false;
        }

        // Live views change every minute — never cache them.
        if ($request->has('live')) {
            return false;
        }

        $name = $request->route()?->getName();
        if (! $name || ! in_array($name, $this->allowed, true)) {
            return false;
        }

        // Do not serve cached HTML alongside validation/flash messages.
        $session = $request->session();
        foreach (['errors', '_old_input', 'success', 'error', 'status'] as $flash) {
            if ($session && $session->has($flash)) {
                return false;
            }
        }

        return true;
    }

    private function isCacheableResponse(Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }
}
