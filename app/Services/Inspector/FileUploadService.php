<?php

namespace App\Services\Inspector;

use App\Exceptions\Inspector\FileUploadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Allowed file types for different upload contexts
     */
    private const ALLOWED_TYPES = [
        'inspection_photos' => ['jpg', 'jpeg', 'png', 'webp'],
        'documents' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'avatar' => ['jpg', 'jpeg', 'png', 'webp'],
    ];

    /**
     * Maximum file sizes in bytes for different contexts
     */
    private const MAX_SIZES = [
        'inspection_photos' => 5 * 1024 * 1024, // 5MB
        'documents' => 10 * 1024 * 1024, // 10MB
        'avatar' => 2 * 1024 * 1024, // 2MB
    ];

    /**
     * Upload and validate a file
     */
    public function uploadFile(UploadedFile $file, string $context, ?int $inspectorId = null): array
    {
        $this->validateFile($file, $context);

        $filename = $this->generateFilename($file, $context, $inspectorId);
        $path = $this->getStoragePath($context, $inspectorId);

        try {
            // Store the file
            $storedPath = $file->storeAs($path, $filename, 'public_uploads');

            // Process image if needed
            if ($this->isImage($file) && $context === 'inspection_photos') {
                $this->processInspectionPhoto($storedPath);
            } elseif ($this->isImage($file) && $context === 'avatar') {
                $this->processAvatar($storedPath);
            }

            return [
                'filename' => $filename,
                'path' => $storedPath,
                'url' => asset('uploads/' . ltrim($storedPath, '/')),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
            ];

        } catch (\Exception $e) {
            throw FileUploadException::uploadFailed(
                $file->getClientOriginalName(),
                $e->getMessage()
            );
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultipleFiles(array $files, string $context, ?int $inspectorId = null): array
    {
        $uploadedFiles = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $uploadedFiles[] = $this->uploadFile($file, $context, $inspectorId);
            } catch (FileUploadException $e) {
                $errors[$index] = [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'details' => $e->getDetails(),
                ];
            }
        }

        if (!empty($errors)) {
            throw new FileUploadException(
                'Some files failed to upload',
                [
                    'uploaded_count' => count($uploadedFiles),
                    'failed_count' => count($errors),
                    'errors' => $errors,
                    'uploaded_files' => $uploadedFiles,
                ]
            );
        }

        return $uploadedFiles;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file, string $context): void
    {
        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            throw FileUploadException::uploadFailed(
                $file->getClientOriginalName(),
                'File upload failed during transfer'
            );
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedTypes = self::ALLOWED_TYPES[$context] ?? [];

        if (!in_array($extension, $allowedTypes)) {
            throw FileUploadException::invalidFileType(
                $file->getClientOriginalName(),
                $allowedTypes
            );
        }

        // Check file size
        $maxSize = self::MAX_SIZES[$context] ?? 5 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            throw FileUploadException::fileTooLarge(
                $file->getClientOriginalName(),
                $file->getSize(),
                $maxSize
            );
        }

        // Additional validation for images
        if ($this->isImage($file)) {
            $this->validateImage($file);
        }
    }

    /**
     * Validate image file
     */
    private function validateImage(UploadedFile $file): void
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            
            if ($imageInfo === false) {
                throw FileUploadException::corruptedFile($file->getClientOriginalName());
            }

            // Check minimum dimensions
            [$width, $height] = $imageInfo;
            if ($width < 100 || $height < 100) {
                throw new FileUploadException(
                    "Image '{$file->getClientOriginalName()}' is too small",
                    [
                        'filename' => $file->getClientOriginalName(),
                        'width' => $width,
                        'height' => $height,
                        'min_width' => 100,
                        'min_height' => 100,
                        'reason' => 'Image dimensions too small'
                    ]
                );
            }

            // Check maximum dimensions
            if ($width > 4000 || $height > 4000) {
                throw new FileUploadException(
                    "Image '{$file->getClientOriginalName()}' is too large",
                    [
                        'filename' => $file->getClientOriginalName(),
                        'width' => $width,
                        'height' => $height,
                        'max_width' => 4000,
                        'max_height' => 4000,
                        'reason' => 'Image dimensions too large'
                    ]
                );
            }

        } catch (FileUploadException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw FileUploadException::corruptedFile($file->getClientOriginalName());
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file, string $context, ?int $inspectorId = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        $prefix = $context;
        if ($inspectorId) {
            $prefix .= "_{$inspectorId}";
        }

        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get storage path for context
     */
    private function getStoragePath(string $context, ?int $inspectorId = null): string
    {
        $basePath = 'inspector';
        
        if ($inspectorId) {
            $basePath .= "/{$inspectorId}";
        }

        return "{$basePath}/{$context}";
    }

    /**
     * Check if file is an image
     */
    private function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Process inspection photo (resize and optimize)
     */
    private function processInspectionPhoto(string $path): void
    {
        try {
            $fullPath = Storage::disk('public_uploads')->path($path);
            
            $image = Image::make($fullPath);
            
            // Resize if too large, maintaining aspect ratio
            if ($image->width() > 1920 || $image->height() > 1920) {
                $image->resize(1920, 1920, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Optimize quality
            $image->save($fullPath, 85);
            
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Failed to process inspection photo', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process avatar (resize to standard size)
     */
    private function processAvatar(string $path): void
    {
        try {
            $fullPath = Storage::disk('public_uploads')->path($path);
            
            $image = Image::make($fullPath);
            
            // Resize to standard avatar size
            $image->fit(300, 300);
            $image->save($fullPath, 90);
            
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Failed to process avatar', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('public_uploads')->delete($path);
        } catch (\Exception $e) {
            \Log::error('Failed to delete file', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get allowed file types for a context
     */
    public function getAllowedTypes(string $context): array
    {
        return self::ALLOWED_TYPES[$context] ?? [];
    }

    /**
     * Get maximum file size for a context
     */
    public function getMaxSize(string $context): int
    {
        return self::MAX_SIZES[$context] ?? 5 * 1024 * 1024;
    }
}