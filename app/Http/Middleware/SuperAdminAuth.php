<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First ensure they are logged in as admin
        if (!session()->has('admin_id')) {
            return redirect()->route('admin.login')->with('error', 'Please login to access admin panel');
        }

        // Then check if they are super_admin
        if (session('admin_role') !== 'super_admin') {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access that area.');
        }

        return $next($request);
    }
}
