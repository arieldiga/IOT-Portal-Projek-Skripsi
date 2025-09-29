<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SessionAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('user_logged_in')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}