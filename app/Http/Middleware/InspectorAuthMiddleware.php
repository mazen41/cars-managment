<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\JwtService;
use App\Models\User;

class InspectorAuthMiddleware
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => [
                    'message' => 'Authorization token required',
                    'code' => 'UNAUTHORIZED'
                ]
            ], 401);
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        // Validate token and get user
        $user = $this->jwtService->getUserFromToken($token);

        if (!$user) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid or expired token',
                    'code' => 'UNAUTHORIZED'
                ]
            ], 401);
        }

        // Verify user is a car inspector
        if ($user->user_type !== 'car_inspector') {
            return response()->json([
                'error' => [
                    'message' => 'Access denied. Car inspector privileges required.',
                    'code' => 'FORBIDDEN'
                ]
            ], 403);
        }

        // Verify user has car inspector profile
        if (!$user->carInspector) {
            return response()->json([
                'error' => [
                    'message' => 'Car inspector profile not found',
                    'code' => 'FORBIDDEN'
                ]
            ], 403);
        }

        // Verify inspector is active
        if (!$user->carInspector->is_active) {
            return response()->json([
                'error' => [
                    'message' => 'Car inspector account is inactive',
                    'code' => 'FORBIDDEN'
                ]
            ], 403);
        }

        // Add user and inspector to request for use in controllers
        $request->merge([
            'auth_user' => $user,
            'auth_inspector' => $user->carInspector
        ]);

        // Set the authenticated user for Laravel's auth system
        auth()->setUser($user);

        return $next($request);
    }
}