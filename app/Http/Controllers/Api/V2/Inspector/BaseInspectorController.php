<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Exceptions\Inspector\InspectorAccessDeniedException;
use App\Exceptions\Inspector\InspectionNotFoundException;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Traits\PaginationFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseInspectorController extends Controller
{
    use ApiResponseTrait, PaginationFilterTrait;

    /**
     * Get the authenticated inspector
     */
    protected function getAuthenticatedInspector()
    {
        return auth('api')->user();
    }

    /**
     * Get the inspector's CarInspector model
     */
    protected function getInspectorProfile()
    {
        return $this->getAuthenticatedInspector()->carInspector;
    }

    /**
     * Ensure the user is a car inspector
     */
    protected function ensureInspectorAccess()
    {
        $user = $this->getAuthenticatedInspector();
        
        if (!$user || $user->user_type !== 'car_inspector') {
            throw new InspectorAccessDeniedException('Access denied. Only car inspectors can access this resource.', [
                'user_type' => $user?->user_type ?? 'unknown'
            ]);
        }

        if (!$user->carInspector) {
            throw new InspectorAccessDeniedException('Inspector profile not found.', [
                'user_id' => $user->id
            ]);
        }

        if (!$user->carInspector->is_active) {
            throw new InspectorAccessDeniedException('Inspector account is inactive.', [
                'inspector_id' => $user->carInspector->id,
                'is_active' => false
            ]);
        }

        return $user;
    }

    /**
     * Apply search filter for inspections
     */
    protected function applySearchFilter(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('inspection_number', 'like', "%{$searchTerm}%")
              ->orWhere('inspector_notes', 'like', "%{$searchTerm}%")
              ->orWhere('recommendations', 'like', "%{$searchTerm}%")
              ->orWhereHas('car', function ($carQuery) use ($searchTerm) {
                  $carQuery->where('name', 'like', "%{$searchTerm}%")
                           ->orWhere('vin_number', 'like', "%{$searchTerm}%")
                           ->orWhere('license_plate', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('requester', function ($requesterQuery) use ($searchTerm) {
                  $requesterQuery->where('name', 'like', "%{$searchTerm}%")
                                 ->orWhere('email', 'like', "%{$searchTerm}%")
                                 ->orWhere('phone', 'like', "%{$searchTerm}%");
              });
        });
    }

    /**
     * Handle common exceptions and return appropriate responses
     */
    protected function handleException(\Exception $e, string $defaultMessage = 'An error occurred')
    {
        // Log the exception
        \Log::error('Inspector API Error: ' . $e->getMessage(), [
            'exception' => $e,
            'user_id' => auth('api')->id(),
            'request_url' => request()->fullUrl(),
            'request_data' => request()->all(),
        ]);

        // Return appropriate response based on exception type
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Resource not found');
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->unauthorizedResponse('Authentication required');
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbiddenResponse('Access denied');
        }

        // For production, don't expose internal error details
        if (app()->environment('production')) {
            return $this->serverErrorResponse($defaultMessage);
        }

        return $this->serverErrorResponse($e->getMessage());
    }

    /**
     * Validate that an inspection belongs to the authenticated inspector
     */
    protected function validateInspectionOwnership($inspection)
    {
        $inspector = $this->getInspectorProfile();
        
        if ($inspection->inspector_id !== $inspector->id) {
            throw new InspectionNotFoundException('You do not have permission to access this inspection.', [
                'inspection_id' => $inspection->id,
                'inspector_id' => $inspector->id,
                'assigned_inspector_id' => $inspection->inspector_id
            ]);
        }

        return true;
    }

    /**
     * Get common response metadata
     */
    protected function getResponseMetadata(Request $request): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'api_version' => 'v2',
        ];
    }
}