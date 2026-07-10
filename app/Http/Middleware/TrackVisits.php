<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

class TrackVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclude admin routes and assets
        if ($request->is('admin*') || $request->is('storage*') || $request->is('css*') || $request->is('js*') || $request->is('images*') || $request->is('fonts*')) {
            return $next($request);
        }

        $ip = $request->ip();
        $date = now()->toDateString(); // YYYY-MM-DD
        $cacheKey = "visit_{$ip}_{$date}";

        // Check cache first to avoid DB hit
        if (Cache::has($cacheKey)) {
            return $next($request);
        }

        // Use updateOrInsert to reduce queries from 2 to 1
        // This is more efficient and reduces connection time
        try {
            DB::table('site_visits')->updateOrInsert(
                [
                    'ip_address' => $ip,
                    'visit_date' => $date,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Cache until end of day to minimize DB hits
            $endOfDay = now()->endOfDay();
            Cache::put($cacheKey, true, $endOfDay);
        } catch (\Exception $e) {
            // Log error but don't block the request
            \Log::warning('Failed to track visit', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }

        return $next($request);
    }
}
