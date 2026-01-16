<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class isSupplier
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
        $user = Auth::user();
      
        if (!$user) {
            // Return an unauthorized response if the user is not authenticated
            return response()->json(['message' => 'Unauthorized'], 401);  
        }

        // Check if the authenticated user is an supplier
        if (Auth::user()->role !== 'supplier' || $user->isDeleted == 1) {
            // Return a forbidden response if the user is not an supplier
            return response()->json(['message' => 'Forbidden. Supplier access only.'], 403);  // Explicitly returning JsonResponse
        }

        // Allow the request to proceed to the next middleware or controller
        return $next($request);  // This is a valid return, it doesn't return a JsonResponse
    }
}
