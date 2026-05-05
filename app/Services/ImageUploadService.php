<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Exception;

class ImageUploadService
{
    /**
     * Allowed image extensions
     */
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    /**
     * Maximum file size in bytes (2MB)
     */
    protected int $maxFileSize = 2097152;

    /**
     * Maximum image dimensions
     */
    protected int $maxWidth = 1500;
    protected int $maxHeight = 1500;

    /**
     * Upload a single image
     *
     * @param UploadedFile $file
     * @return int Upload ID
     * @throws Exception
     */
    public function uploadImage(UploadedFile $file, string $storePath): int
    {
        $this->validateImage($file);

        try {
            $upload = new Upload();
            $extension = strtolower($file->getClientOriginalExtension());

            // Set original filename
            $upload->file_original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Store the file
            $path = $file->store($storePath, 'local');
            $size = $file->getSize();

            // Process and optimize image if needed
            if (get_setting('disable_image_optimization') != 1) {
                $this->processImage($path);
                // Recalculate size after processing
                $size = filesize(public_path($path));
            }

            // Save upload record
            $upload->extension = $extension;
            $upload->file_name = $path;
            $upload->user_id = auth()->id();
            $upload->type = 'image';
            $upload->file_size = $size;
            $upload->save();

            Log::info('Customer product image uploaded successfully', [
                'upload_id' => $upload->id,
                'file_name' => $upload->file_name,
                'user_id' => auth()->id()
            ]);

            return $upload->id;

        } catch (Exception $e) {
            // Clean up file if upload record creation fails
            if (isset($path) && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            Log::error('Failed to upload customer product image', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            throw new Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple images
     *
     * @param array $files Array of UploadedFile instances
     * @return array Array of upload IDs
     * @throws Exception
     */
    public function uploadImages(array $files, string $storePath): array
    {
        $uploadIds = [];
        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadId = $this->uploadImage($file, $storePath);
                    $uploadIds[] = $uploadId;
                    $uploadedFiles[] = $uploadId;
                }
            }

            return $uploadIds;

        } catch (Exception $e) {
            // Clean up any successfully uploaded files on failure
            foreach ($uploadedFiles as $uploadId) {
                try {
                    $this->deleteImage($uploadId);
                } catch (Exception $cleanupException) {
                    Log::error('Failed to cleanup uploaded image during rollback', [
                        'upload_id' => $uploadId,
                        'error' => $cleanupException->getMessage()
                    ]);
                }
            }

            throw $e;
        }
    }

    /**
     * Delete a single image
     *
     * @param int $uploadId
     * @return bool
     * @throws Exception
     */
    public function deleteImage(int $uploadId): bool
    {
        try {
            $upload = Upload::find($uploadId);

            if (!$upload) {
                Log::warning('Attempted to delete non-existent upload', ['upload_id' => $uploadId]);
                return true; // Consider it successful if already gone
            }

            // Delete physical file
            $this->deletePhysicalFile($upload->file_name);

            // Delete upload record
            $upload->delete();

            Log::info('Customer product image deleted successfully', [
                'upload_id' => $uploadId,
                'file_name' => $upload->file_name
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to delete customer product image', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage()
            ]);

            throw new Exception('Failed to delete image: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple images
     *
     * @param array $uploadIds
     * @return int Number of successfully deleted images
     */
    public function deleteImages(array $uploadIds): int
    {
        $deletedCount = 0;

        foreach ($uploadIds as $uploadId) {
            try {
                if ($this->deleteImage($uploadId)) {
                    $deletedCount++;
                }
            } catch (Exception $e) {
                Log::error('Failed to delete image in batch operation', [
                    'upload_id' => $uploadId,
                    'error' => $e->getMessage()
                ]);
                // Continue with other deletions
            }
        }

        return $deletedCount;
    }

    /**
     * Validate uploaded image
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    public function validateImage(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $this->allowedExtensions));
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }

        // Check if it's actually an image
        $imageInfo = getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image');
        }

        // Check image dimensions
        [$width, $height] = $imageInfo;
        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            // We'll resize it, but log a warning
            Log::info('Image will be resized', [
                'original_dimensions' => "{$width}x{$height}",
                'max_dimensions' => "{$this->maxWidth}x{$this->maxHeight}"
            ]);
        }

        // Check MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new Exception('Invalid MIME type');
        }
    }

    /**
     * Process and optimize image
     *
     * @param string $path
     * @return void
     * @throws Exception
     */
    protected function processImage(string $path): void
    {
        try {
            $fullPath = public_path($path);

            if (!file_exists($fullPath)) {
                throw new Exception('Image file not found for processing');
            }

            $img = Image::make($fullPath);
            $height = $img->height();
            $width = $img->width();

            // Resize if necessary
            if ($width > $height && $width > $this->maxWidth) {
                $img->resize($this->maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($height > $this->maxHeight) {
                $img->resize(null, $this->maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Optimize quality for JPEG
            if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg'])) {
                $img->encode('jpg', 85);
            }

            // Save the processed image
            $img->save($fullPath);

            Log::info('Image processed successfully', [
                'path' => $path,
                'original_size' => "{$width}x{$height}",
                'processed_size' => "{$img->width()}x{$img->height()}"
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process image', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            throw new Exception('Failed to process image: ' . $e->getMessage());
        }
    }

    /**
     * Delete physical file from storage
     *
     * @param string $filePath
     * @return void
     */
    protected function deletePhysicalFile(string $filePath): void
    {
        try {
            if (env('FILESYSTEM_DRIVER') != 'local') {
                // Delete from cloud storage
                Storage::disk(env('FILESYSTEM_DRIVER'))->delete($filePath);

                // Also delete local copy if exists
                if (file_exists(public_path($filePath))) {
                    unlink(public_path($filePath));
                }
            } else {
                // Delete from local storage
                if (file_exists(public_path($filePath))) {
                    unlink(public_path($filePath));
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to delete physical file', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception here as the database record should still be deleted
        }
    }

    /**
     * Get image URL from upload ID
     *
     * @param int $uploadId
     * @return string|null
     */
    public function getImageUrl(int $uploadId): ?string
    {
        $upload = Upload::find($uploadId);

        if (!$upload) {
            return null;
        }

        return uploaded_asset($upload->file_name);
    }

    /**
     * Get multiple image URLs from upload IDs
     *
     * @param array $uploadIds
     * @return array
     */
    public function getImageUrls(array $uploadIds): array
    {
        $uploads = Upload::whereIn('id', $uploadIds)->get();

        return $uploads->map(function ($upload) {
            return my_asset($upload->file_name);
        })->toArray();
    }

    /**
     * Validate multiple images
     *
     * @param array $files
     * @param int $maxCount
     * @throws Exception
     */
    public function validateImages(array $files, int $maxCount = 5): void
    {
        if (count($files) > $maxCount) {
            throw new Exception("Maximum {$maxCount} images allowed");
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->validateImage($file);
            }
        }
    }

    /**
     * Get image info
     *
     * @param int $uploadId
     * @return array|null
     */
    public function getImageInfo(int $uploadId): ?array
    {
        $upload = Upload::find($uploadId);

        if (!$upload) {
            return null;
        }

        return [
            'id' => $upload->id,
            'original_name' => $upload->file_original_name,
            'file_name' => $upload->file_name,
            'extension' => $upload->extension,
            'size' => $upload->file_size,
            'url' => uploaded_asset($upload->file_name),
            'created_at' => $upload->created_at,
        ];
    }

    /**
     * Clean up orphaned images (images not associated with any product)
     *
     * @param int $daysOld
     * @return int Number of cleaned up images
     */
    public function cleanupOrphanedImages(int $daysOld = 7): int
    {
        try {
            // Find uploads that are not referenced by any customer product
            $orphanedUploads = Upload::where('type', 'image')
                ->where('created_at', '<', now()->subDays($daysOld))
                ->whereNotIn('id', function ($query) {
                    $query->select('main_photo')
                        ->from('customer_products')
                        ->whereNotNull('main_photo');
                })
                ->whereNotExists(function ($query) {
                    $query->select('*')
                        ->from('customer_products')
                        ->whereRaw('JSON_CONTAINS(photos, CAST(uploads.id AS JSON))');
                })
                ->get();

            $cleanedCount = 0;
            foreach ($orphanedUploads as $upload) {
                try {
                    $this->deleteImage($upload->id);
                    $cleanedCount++;
                } catch (Exception $e) {
                    Log::error('Failed to cleanup orphaned image', [
                        'upload_id' => $upload->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Orphaned images cleanup completed', [
                'cleaned_count' => $cleanedCount,
                'days_old' => $daysOld
            ]);

            return $cleanedCount;

        } catch (Exception $e) {
            Log::error('Failed to cleanup orphaned images', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }
}
