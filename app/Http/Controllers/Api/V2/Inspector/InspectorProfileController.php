<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Inspector\UpdateProfileRequest;
use App\Http\Requests\Api\V2\Inspector\ChangePasswordRequest;
use App\Http\Requests\Api\V2\Inspector\UpdateBusinessSettingsRequest;
use App\Http\Requests\Api\V2\Inspector\UploadAvatarRequest;
use App\Http\Requests\Api\V2\Inspector\UploadCoverPhotoRequest;
use App\Http\Resources\V2\Inspector\InspectorProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectorProfileController extends Controller
{
    /**
     * Get inspector profile information
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $inspector = $user->carInspector;
        
        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        return response()->json([
            'data' => new InspectorProfileResource($user)
        ]);
    }

    /**
     * Update inspector profile information
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $inspector = $user->carInspector;
        
        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Update user information
            $userFields = $request->only([
                'name', 'email', 'phone', 'address', 'city', 'postal_code', 'country'
            ]);
            
            if (!empty($userFields)) {
                $user->update(array_filter($userFields));
            }

            DB::commit();

            return response()->json([
                'data' => new InspectorProfileResource($user->fresh()),
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => [
                    'message' => 'Failed to update profile',
                    'code' => 'UPDATE_FAILED'
                ]
            ], 500);
        }
    }

    /**
     * Change inspector password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        
        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to change password',
                    'code' => 'PASSWORD_CHANGE_FAILED'
                ]
            ], 500);
        }
    }

    /**
     * Update business settings
     */
    public function updateBusinessSettings(UpdateBusinessSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        $inspector = $user->carInspector;
        
        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Update inspector business information
            $inspectorFields = $request->only([
                'shop_name', 'description', 'address', 'latitude', 'longitude',
                'country_id', 'state_id', 'city_id', 'phone', 'email',
                'working_hours', 'services_offered', 'certification_number', 'experience_years'
            ]);
            
            if (!empty($inspectorFields)) {
                $inspector->update(array_filter($inspectorFields, function($value) {
                    return $value !== null;
                }));
            }

            DB::commit();

            return response()->json([
                'data' => new InspectorProfileResource($user->fresh()),
                'message' => 'Business settings updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => [
                    'message' => 'Failed to update business settings',
                    'code' => 'UPDATE_FAILED'
                ]
            ], 500);
        }
    }

    /**
     * Upload avatar image
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $inspector = $user->carInspector;
        
        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        try {
            $file = $request->file('avatar');
            
            // Generate unique filename
            $filename = 'inspector_avatars/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file
            $path = $file->storeAs('public/uploads', $filename);
            
            // Delete old avatar if exists
            if ($inspector->image && Storage::exists('public/uploads/' . $inspector->image)) {
                Storage::delete('public/uploads/' . $inspector->image);
            }
            
            // Update inspector with new image path
            $inspector->update([
                'image' => $filename
            ]);

            return response()->json([
                'data' => [
                    'image_url' => $inspector->fresh()->image_url,
                    'image_path' => $filename
                ],
                'message' => 'Avatar uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to upload avatar',
                    'code' => 'UPLOAD_FAILED'
                ]
            ], 500);
        }
    }

    /**
     * Upload cover photo image
     */
    public function uploadCoverPhoto(UploadCoverPhotoRequest $request): JsonResponse
    {
        $user = $request->user();
        $inspector = $user->carInspector;
        
        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        try {
            $file = $request->file('cover_photo');
            
            // Generate unique filename
            $filename = 'inspector_cover_photos/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file
            $path = $file->storeAs('public/uploads', $filename);
            
            // Delete old cover photo if exists
            if ($inspector->banner_image && Storage::exists('public/uploads/' . $inspector->banner_image)) {
                Storage::delete('public/uploads/' . $inspector->banner_image);
            }
            
            // Update inspector with new banner image path
            $inspector->update([
                'banner_image' => $filename
            ]);

            return response()->json([
                'data' => [
                    'banner_image_url' => $inspector->fresh()->banner_image_url,
                    'banner_image_path' => $filename
                ],
                'message' => 'Cover photo uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to upload cover photo',
                    'code' => 'UPLOAD_FAILED'
                ]
            ], 500);
        }
    }
}