<?php

namespace App\Services\Inspector;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorLoggingService
{
    /**
     * Log inspector API errors with comprehensive context
     */
    public static function logError(Throwable $exception, Request $request, array $additionalContext = []): void
    {
        $context = self::buildErrorContext($exception, $request, $additionalContext);
        
        // Determine log level based on exception type
        $logLevel = self::determineLogLevel($exception);
        
        // Log with appropriate level
        Log::log($logLevel, 'Inspector API Error: ' . $exception->getMessage(), $context);
        
        // Log to specific inspector error log if configured
        if (config('logging.channels.inspector')) {
            Log::channel('inspector')->log($logLevel, $exception->getMessage(), $context);
        }
    }

    /**
     * Log business logic errors
     */
    public static function logBusinessLogicError(string $operation, array $context, ?Throwable $exception = null): void
    {
        $logContext = [
            'type' => 'business_logic_error',
            'operation' => $operation,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'user_id' => auth('api')->id(),
            'inspector_id' => auth('api')->user()?->carInspector?->id,
        ];

        if ($exception) {
            $logContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        Log::warning("Inspector Business Logic Error: {$operation}", $logContext);
    }

    /**
     * Log file upload errors
     */
    public static function logFileUploadError(string $filename, string $context, Throwable $exception, array $fileInfo = []): void
    {
        $logContext = [
            'type' => 'file_upload_error',
            'filename' => $filename,
            'upload_context' => $context,
            'file_info' => $fileInfo,
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
            'timestamp' => now()->toISOString(),
            'user_id' => auth('api')->id(),
            'inspector_id' => auth('api')->user()?->carInspector?->id,
        ];

        Log::error("Inspector File Upload Error: {$filename}", $logContext);
    }

    /**
     * Log inspection status transition errors
     */
    public static function logStatusTransitionError(int $inspectionId, string $currentStatus, string $newStatus, Throwable $exception): void
    {
        $logContext = [
            'type' => 'status_transition_error',
            'inspection_id' => $inspectionId,
            'current_status' => $currentStatus,
            'requested_status' => $newStatus,
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ],
            'timestamp' => now()->toISOString(),
            'user_id' => auth('api')->id(),
            'inspector_id' => auth('api')->user()?->carInspector?->id,
        ];

        Log::warning("Inspector Status Transition Error: {$currentStatus} -> {$newStatus}", $logContext);
    }

    /**
     * Log successful operations for audit trail
     */
    public static function logSuccess(string $operation, array $context = []): void
    {
        $logContext = [
            'type' => 'success_audit',
            'operation' => $operation,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'user_id' => auth('api')->id(),
            'inspector_id' => auth('api')->user()?->carInspector?->id,
        ];

        Log::info("Inspector Operation Success: {$operation}", $logContext);
    }

    /**
     * Build comprehensive error context
     */
    private static function buildErrorContext(Throwable $exception, Request $request, array $additionalContext): array
    {
        return [
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => self::formatStackTrace($exception->getTrace()),
            ],
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => self::sanitizeHeaders($request->headers->all()),
                'input' => self::sanitizeInput($request->all()),
                'files' => self::getFileInfo($request),
            ],
            'user' => [
                'id' => auth('api')->id(),
                'inspector_id' => auth('api')->user()?->carInspector?->id,
                'user_type' => auth('api')->user()?->user_type,
                'email' => auth('api')->user()?->email,
            ],
            'system' => [
                'timestamp' => now()->toISOString(),
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ],
            'additional' => $additionalContext,
        ];
    }

    /**
     * Determine appropriate log level based on exception type
     */
    private static function determineLogLevel(Throwable $exception): string
    {
        // Client errors (4xx) - info level
        if ($exception instanceof \Illuminate\Validation\ValidationException ||
            $exception instanceof \Illuminate\Auth\AuthenticationException ||
            $exception instanceof \Illuminate\Auth\Access\AuthorizationException ||
            $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
            $exception instanceof \App\Exceptions\Inspector\InspectorException) {
            return 'info';
        }

        // Server errors (5xx) - error level
        return 'error';
    }

    /**
     * Format stack trace for logging
     */
    private static function formatStackTrace(array $trace): array
    {
        return collect($trace)
            ->take(10) // Limit to first 10 frames
            ->map(function ($frame) {
                return [
                    'file' => $frame['file'] ?? 'unknown',
                    'line' => $frame['line'] ?? 0,
                    'function' => $frame['function'] ?? 'unknown',
                    'class' => $frame['class'] ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Sanitize headers for logging
     */
    private static function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
            'authentication',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize input for logging
     */
    private static function sanitizeInput(array $input): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'pin',
            'cvv',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '***REDACTED***';
            }
        }

        return $input;
    }

    /**
     * Get file information from request
     */
    private static function getFileInfo(Request $request): array
    {
        $files = [];
        
        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $index => $singleFile) {
                    $files["{$key}[{$index}]"] = [
                        'name' => $singleFile->getClientOriginalName(),
                        'size' => $singleFile->getSize(),
                        'mime_type' => $singleFile->getMimeType(),
                    ];
                }
            } else {
                $files[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        return $files;
    }
}