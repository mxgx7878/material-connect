<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'client' && ! $user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'Please verify your email address before placing an order.',
                'code'  => 'email_unverified',
            ], 403);
        }

        return $next($request);
    }
}