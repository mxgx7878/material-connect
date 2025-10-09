<?php
// app/Http/Middleware/AuthMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $isAuthenticated = Auth::check();
        
        Log::info('AuthMiddleware Debug', [
            'path' => $path,
            'authenticated' => $isAuthenticated,
            'user_id' => $isAuthenticated ? Auth::id() : null,
            'session_id' => session()->getId(),
            'cookie_received' => $request->cookie(config('session.cookie'))
        ]);

        // Public routes that don't require authentication
        $publicRoutes = ['login', 'register', 'login-submit', 'register-submit', 'logout', '', 'debug-auth', 'debug-sessions', 'reset-sessions', 'debug-session-full'];
        
        // If not authenticated and trying to access protected route
        if (!$isAuthenticated && !in_array($path, $publicRoutes)) {
            Log::warning('Redirecting to login - not authenticated', [
                'session_match' => $request->cookie(config('session.cookie')) === session()->getId()
            ]);
            return redirect()->route('login')->withErrors(['message' => 'Please log in to access this page.']);
        }

        Log::info('Allowing request to proceed');
        return $next($request);
    }
}