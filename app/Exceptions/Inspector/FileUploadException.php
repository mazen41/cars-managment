<?php

namespace App\Exceptions\Inspector;

class FileUploadException extends InspectorException
{
    protected string $errorCode = 'FILE_UPLOAD_ERROR';
    protected int $statusCode = 422;

    public function __construct(string $message = 'File upload failed', array $details = [])
    {
        parent::__construct($message, $details);
    }

    /**
     * Create exception for invalid file type
     */
    public static function invalidFileType(string $filename, array $allowedTypes): self
    {
        return new self(
            "Invalid file type for '{$filename}'",
            [
                'filename' => $filename,
                'allowed_types' => $allowedTypes,
                'reason' => 'File type not allowed'
            ]
        );
    }

    /**
     * Create exception for file too large
     */
    public static function fileTooLarge(string $filename, int $size, int $maxSize): self
    {
        return new self(
            "File '{$filename}' is too large",
            [
                'filename' => $filename,
                'size' => $size,
                'max_size' => $maxSize,
                'size_human' => self::formatBytes($size),
                'max_size_human' => self::formatBytes($maxSize),
                'reason' => 'File exceeds maximum allowed size'
            ]
        );
    }

    /**
     * Create exception for upload failure
     */
    public static function uploadFailed(string $filename, string $reason = 'Unknown error'): self
    {
        return new self(
            "Failed to upload '{$filename}'",
            [
                'filename' => $filename,
                'reason' => $reason
            ]
        );
    }

    /**
     * Create exception for missing file
     */
    public static function noFileProvided(string $fieldName): self
    {
        return new self(
            'No file provided',
            [
                'field' => $fieldName,
                'reason' => 'File is required but not provided'
            ]
        );
    }

    /**
     * Create exception for corrupted file
     */
    public static function corruptedFile(string $filename): self
    {
        return new self(
            "File '{$filename}' appears to be corrupted",
            [
                'filename' => $filename,
                'reason' => 'File validation failed'
            ]
        );
    }

    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}