<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user(); // Retrieve the authenticated user

        // Check if the user's role matches the required role
        if (!$user || $user->role !== $role) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return $next($request);
    }
}
