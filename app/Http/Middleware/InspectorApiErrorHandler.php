<?php

namespace App\Http\Middleware;

use App\Services\Inspector\ErrorLoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class InspectorApiErrorHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Handle exceptions and return appropriate JSON responses
     */
    protected function handleException(Request $request, Throwable $e): JsonResponse
    {
        // Log the exception with comprehensive context
        ErrorLoggingService::logError($e, $request);

        // Handle specific exception types
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->handleAuthorizationException($e);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->handleNotFoundException($e);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->handleMethodNotAllowedException($e);
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return $this->handleTooManyRequestsException($e);
        }

        // Handle general exceptions
        return $this->handleGeneralException($e);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Validation failed',
                'code' => 'VALIDATION_ERROR',
                'details' => $e->errors(),
            ]
        ], 422);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Authentication required',
                'code' => 'UNAUTHORIZED',
                'details' => [
                    'reason' => 'Invalid or missing authentication token'
                ]
            ]
        ], 401);
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(AuthorizationException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Access denied',
                'code' => 'FORBIDDEN',
                'details' => [
                    'reason' => $e->getMessage() ?: 'Insufficient permissions'
                ]
            ]
        ], 403);
    }

    /**
     * Handle not found exceptions
     */
    protected function handleNotFoundException(Throwable $e): JsonResponse
    {
        $message = 'Resource not found';
        
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            $message = "{$model} not found";
        }

        return response()->json([
            'error' => [
                'message' => $message,
                'code' => 'NOT_FOUND',
                'details' => [
                    'resource' => $e instanceof ModelNotFoundException ? class_basename($e->getModel()) : 'unknown'
                ]
            ]
        ], 404);
    }

    /**
     * Handle method not allowed exceptions
     */
    protected function handleMethodNotAllowedException(MethodNotAllowedHttpException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Method not allowed',
                'code' => 'METHOD_NOT_ALLOWED',
                'details' => [
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? 'Unknown'
                ]
            ]
        ], 405);
    }

    /**
     * Handle too many requests exceptions
     */
    protected function handleTooManyRequestsException(TooManyRequestsHttpException $e): JsonResponse
    {
        $retryAfter = $e->getHeaders()['Retry-After'] ?? null;
        $retryAfterInSeconds = is_numeric($retryAfter) ? (int) $retryAfter : null;

        return response()->json([
            'error' => [
                'message' => 'Too many requests',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'details' => [
                    'retry_after' => $retryAfterInSeconds,
                    'message' => $retryAfterInSeconds 
                        ? "Please wait {$retryAfterInSeconds} seconds before retrying"
                        : 'Rate limit exceeded'
                ]
            ]
        ], 429);
    }

    /**
     * Handle general exceptions
     */
    protected function handleGeneralException(Throwable $e): JsonResponse
    {
        // In production, don't expose internal error details
        if (app()->environment('production')) {
            return response()->json([
                'error' => [
                    'message' => 'An unexpected error occurred',
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'details' => []
                ]
            ], 500);
        }

        // In development, provide more details
        return response()->json([
            'error' => [
                'message' => $e->getMessage() ?: 'An unexpected error occurred',
                'code' => 'INTERNAL_SERVER_ERROR',
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(5)->toArray()
                ]
            ]
        ], 500);
    }


}