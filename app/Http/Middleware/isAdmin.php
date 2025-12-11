<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Ensure the user is authenticated before checking role
        $user = auth()->user();
      
        if (!$user) {
            // Return an unauthorized response if the user is not authenticated
            return response()->json(['message' => 'Unauthorized'], 401);  // Explicitly returning JsonResponse, but it's fine in this context
        }

        // Check if the authenticated user is an admin
        if ($user->role !== 'admin' && $user->role !== "support" && $user->role !== "accountant") {
            // Return a forbidden response if the user is not an admin
            return response()->json(['message' => 'Forbidden. Admin access only.'], 403);  // Explicitly returning JsonResponse
        }

        // Allow the request to proceed to the next middleware or controller
        return $next($request);  // This is a valid return, it doesn't return a JsonResponse
    }
}
