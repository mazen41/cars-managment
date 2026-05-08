<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InspectorAuthController extends Controller
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Authenticate car inspector and return JWT token
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Verify user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'error' => [
                        'message' => 'Invalid email or password',
                        'code' => 'INVALID_CREDENTIALS'
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

            // Load car inspector profile
            $user->load('carInspector');

            // Verify inspector profile exists
            if (!$user->carInspector) {
                return response()->json([
                    'error' => [
                        'message' => 'Car inspector profile not found',
                        'code' => 'PROFILE_NOT_FOUND'
                    ]
                ], 403);
            }

            // Verify inspector is active
            if (!$user->carInspector->is_active) {
                return response()->json([
                    'error' => [
                        'message' => 'Car inspector account is inactive',
                        'code' => 'ACCOUNT_INACTIVE'
                    ]
                ], 403);
            }

            // Generate tokens
            $accessToken = $this->jwtService->generateToken($user);
            $refreshToken = $this->jwtService->generateRefreshToken($user);

            return response()->json([
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400, // 24 hours in seconds
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'user_type' => $user->user_type,
                        'inspector' => [
                            'id' => $user->carInspector->id,
                            'shop_name' => $user->carInspector->shop_name,
                            'address' => $user->carInspector->address,
                            'phone' => $user->carInspector->phone,
                            'email' => $user->carInspector->email,
                            'is_active' => $user->carInspector->is_active,
                            'certification_number' => $user->carInspector->certification_number,
                            'experience_years' => $user->carInspector->experience_years,
                            'image_url' => $user->carInspector->image_url,
                            'permissions' => [
                                'can_manual_examination' => $user->carInspector->canUseManualExaminations(),
                            ],
                        ]
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => $e->errors()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'An error occurred during login',
                    'code' => 'LOGIN_ERROR'
                ]
            ], 500);
        }
    }

    /**
     * Logout inspector (invalidate token)
     */
    public function logout(Request $request)
    {
        // Note: With JWT, we can't truly invalidate tokens without a blacklist
        // For now, we'll just return success and rely on client-side token removal
        // In production, you might want to implement a token blacklist
        
        return response()->json([
            'data' => [
                'message' => 'Successfully logged out'
            ]
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refresh(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            // Validate refresh token
            $token = $this->jwtService->validateToken($request->refresh_token);
            
            if (!$token) {
                return response()->json([
                    'error' => [
                        'message' => 'Invalid or expired refresh token',
                        'code' => 'INVALID_REFRESH_TOKEN'
                    ]
                ], 401);
            }

            // Check if it's actually a refresh token
            $tokenType = $token->claims()->get('type');
            if ($tokenType !== 'refresh') {
                return response()->json([
                    'error' => [
                        'message' => 'Invalid token type',
                        'code' => 'INVALID_TOKEN_TYPE'
                    ]
                ], 401);
            }

            // Get user from token
            $userId = $token->claims()->get('user_id');
            $user = User::with('carInspector')->find($userId);

            if (!$user || $user->user_type !== 'car_inspector' || !$user->carInspector || !$user->carInspector->is_active) {
                return response()->json([
                    'error' => [
                        'message' => 'User not found or inactive',
                        'code' => 'USER_INACTIVE'
                    ]
                ], 401);
            }

            // Generate new tokens
            $accessToken = $this->jwtService->generateToken($user);
            $refreshToken = $this->jwtService->generateRefreshToken($user);

            return response()->json([
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400, // 24 hours in seconds
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => $e->errors()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'An error occurred during token refresh',
                    'code' => 'REFRESH_ERROR'
                ]
            ], 500);
        }
    }

    /**
     * Get current authenticated inspector profile
     */
    public function me(Request $request)
    {
        try {
            $user = $request->auth_user;
            $inspector = $request->auth_inspector;

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'city' => $user->city,
                        'postal_code' => $user->postal_code,
                        'country' => $user->country,
                        'user_type' => $user->user_type,
                        'email_verified_at' => $user->email_verified_at,
                        'phone_verified_at' => $user->phone_verified_at,
                        'inspector' => [
                            'id' => $inspector->id,
                            'shop_name' => $inspector->shop_name,
                            'address' => $inspector->address,
                            'phone' => $inspector->phone,
                            'email' => $inspector->email,
                            'is_active' => $inspector->is_active,
                            'permissions' => [
                                'can_manual_examination' => $inspector->canUseManualExaminations(),
                            ],
                        ],
                    ],
                    'inspector' => [
                        'id' => $inspector->id,
                        'shop_name' => $inspector->shop_name,
                        'address' => $inspector->address,
                        'latitude' => $inspector->latitude,
                        'longitude' => $inspector->longitude,
                        'phone' => $inspector->phone,
                        'email' => $inspector->email,
                        'is_active' => $inspector->is_active,
                        'description' => $inspector->description,
                        'working_hours' => $inspector->working_hours,
                        'services_offered' => $inspector->services_offered,
                        'certification_number' => $inspector->certification_number,
                        'experience_years' => $inspector->experience_years,
                        'admin_to_pay' => $inspector->admin_to_pay,
                        'image_url' => $inspector->image_url,
                        'banner_image_url' => $inspector->banner_image_url,
                        'stats' => $inspector->stats,
                        'permissions' => [
                            'can_manual_examination' => $inspector->canUseManualExaminations(),
                        ],
                        'country' => $inspector->country,
                        'state' => $inspector->state,
                        'city' => $inspector->city,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'An error occurred while fetching profile',
                    'code' => 'PROFILE_ERROR'
                ]
            ], 500);
        }
    }
}
