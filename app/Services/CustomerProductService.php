<?php

namespace App\Services;

use App\Models\CustomerProduct;
use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class CustomerProductService
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Create a new customer product
     *
     * @param array $data
     * @param int $userId
     * @return CustomerProduct
     * @throws Exception
     */
    public function createProduct(array $data, int $userId): CustomerProduct
    {
        try {
            DB::beginTransaction();

            // Handle main photo upload
            $mainPhotoId = null;
            if (isset($data['main_photo']) && $data['main_photo'] instanceof UploadedFile) {
                $mainPhotoId = $this->imageService->uploadImage($data['main_photo'], 'uploads/customer-products');
            }

            // Handle additional photos upload
            $photoIds = [];
            if (isset($data['photos']) && is_array($data['photos'])) {
                $photoIds = $this->imageService->uploadImages($data['photos'], 'uploads/customer-products');
            }

            // Prepare product data
            $productData = [
                'user_id' => $userId,
                'name' => $data['name'],
                'description' => $data['description'],
                'condition' => $data['condition'] ?? 'used',
                'price' => $data['price'],
                'category_id' => $data['category_id'],
                'main_photo' => $mainPhotoId,
                'photos' => $photoIds,
                'address' => $data['address'],
                'state_id' => $data['state_id'],
                'city_id' => $data['city_id'],
                'longitude' => $data['longitude'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'moderation_status' => 'pending',
                'availability_status' => 'available',
            ];

            $product = CustomerProduct::create($productData);

            // Handle translations if provided
            if (isset($data['translations']) && is_array($data['translations'])) {
                $this->handleTranslations($product, $data['translations']);
            }

            DB::commit();

            Log::info('Customer product created successfully', [
                'product_id' => $product->id,
                'user_id' => $userId
            ]);

            return $product;

        } catch (Exception $e) {
            DB::rollBack();

            // Clean up uploaded images on failure
            if ($mainPhotoId) {
                $this->imageService->deleteImage($mainPhotoId);
            }
            if (!empty($photoIds)) {
                $this->imageService->deleteImages($photoIds);
            }

            Log::error('Failed to create customer product', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing customer product
     *
     * @param CustomerProduct $product
     * @param array $data
     * @return CustomerProduct
     * @throws Exception
     */
    public function updateProduct(CustomerProduct $product, array $data): CustomerProduct
    {
        try {
            DB::beginTransaction();

            $oldMainPhoto = $product->main_photo;
            $oldPhotos = $product->photos_array;

            // Handle main photo update
            if (isset($data['main_photo']) && $data['main_photo'] instanceof UploadedFile) {
                $newMainPhotoId = $this->imageService->uploadImage($data['main_photo'],'uploads/customer-products');
                $data['main_photo'] = $newMainPhotoId;

                // Delete old main photo
                if ($oldMainPhoto) {
                    $this->imageService->deleteImage($oldMainPhoto);
                }
            } else {
                unset($data['main_photo']);
            }

            // Handle additional photos update
            if (isset($data['photos']) && is_array($data['photos'])) {
                $newPhotoIds = $this->imageService->uploadImages($data['photos'],'uploads/customer-products');
                $data['photos'] = $newPhotoIds;

                // Delete old photos
                if (!empty($oldPhotos)) {
                    $this->imageService->deleteImages($oldPhotos);
                }
            } else {
                unset($data['photos']);
            }

            // If product was approved and is being updated, reset to pending
            if ($product->isApproved() && $this->hasContentChanges($data)) {
                $data['moderation_status'] = 'pending';
                $data['rejection_reason'] = null;
            }

            // Update product
            $product->update($data);

            // Handle translations if provided
            if (isset($data['translations']) && is_array($data['translations'])) {
                $this->handleTranslations($product, $data['translations']);
            }

            DB::commit();

            Log::info('Customer product updated successfully', [
                'product_id' => $product->id,
                'user_id' => $product->user_id
            ]);

            return $product->fresh();

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to update customer product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Delete a customer product
     *
     * @param CustomerProduct $product
     * @return bool
     * @throws Exception
     */
    public function deleteProduct(CustomerProduct $product): bool
    {
        try {
            DB::beginTransaction();

            // Delete main photo
            if ($product->main_photo) {
                $this->imageService->deleteImage($product->main_photo);
            }

            // Delete additional photos
            if (!empty($product->photos_array)) {
                $this->imageService->deleteImages($product->photos_array);
            }

            // Delete translations
            $product->translations()->delete();

            // Delete the product
            $productId = $product->id;
            $userId = $product->user_id;
            $product->delete();

            DB::commit();

            Log::info('Customer product deleted successfully', [
                'product_id' => $productId,
                'user_id' => $userId
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete customer product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Moderate a customer product (approve/reject)
     *
     * @param CustomerProduct $product
     * @param string $status
     * @param string|null $reason
     * @return CustomerProduct
     * @throws Exception
     */
    public function moderateProduct(CustomerProduct $product, string $status, string $reason = null): CustomerProduct
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            throw new Exception('Invalid moderation status. Must be "approved" or "rejected".');
        }

        try {
            $updateData = [
                'moderation_status' => $status,
                'rejection_reason' => $status === 'rejected' ? $reason : null
            ];

            $product->update($updateData);

            Log::info('Customer product moderated', [
                'product_id' => $product->id,
                'status' => $status,
                'reason' => $reason
            ]);

            return $product->fresh();

        } catch (Exception $e) {
            Log::error('Failed to moderate customer product', [
                'product_id' => $product->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get public products with filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getPublicProducts(array $filters = [])
    {
        $query = CustomerProduct::approved()->available()
            ->with(['category', 'state', 'city', 'mainPhoto', 'user']);

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['state_id'])) {
            $query->byLocation($filters['state_id'], $filters['city_id'] ?? null);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if (in_array($sortBy, ['created_at', 'price', 'name'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    /**
     * Search products
     *
     * @param string $searchQuery
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchProducts(string $searchQuery, array $filters = [])
    {
        $query = $this->getPublicProducts($filters);

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('description', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('address', 'LIKE', "%{$searchQuery}%");
            });
        }

        return $query;
    }

    /**
     * Handle product translations
     *
     * @param CustomerProduct $product
     * @param array $translations
     * @return void
     */
    protected function handleTranslations(CustomerProduct $product, array $translations): void
    {
        foreach ($translations as $lang => $translationData) {
            $product->translations()->updateOrCreate(
                ['lang' => $lang],
                [
                    'name' => $translationData['name'] ?? null,
                    'description' => $translationData['description'] ?? null,
                ]
            );
        }
    }

    /**
     * Check if the update contains content changes that require re-moderation
     *
     * @param array $data
     * @return bool
     */
    protected function hasContentChanges(array $data): bool
    {
        $contentFields = ['name', 'description', 'price', 'category_id', 'main_photo', 'photos'];

        foreach ($contentFields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get products by user
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getUserProducts(int $userId, array $filters = [])
    {
        $query = CustomerProduct::where('user_id', $userId)
            ->with(['category', 'state', 'city', 'mainPhoto']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('moderation_status', $filters['status']);
        }

        if (isset($filters['availability'])) {
            $query->where('availability_status', $filters['availability']);
        }

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        // Default sorting by creation date
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Bulk moderate products
     *
     * @param array $productIds
     * @param string $status
     * @param string|null $reason
     * @return int Number of products moderated
     * @throws Exception
     */
    public function bulkModerateProducts(array $productIds, string $status, string $reason = null): int
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            throw new Exception('Invalid moderation status. Must be "approved" or "rejected".');
        }

        try {
            $updateData = [
                'moderation_status' => $status,
                'rejection_reason' => $status === 'rejected' ? $reason : null
            ];

            $count = CustomerProduct::whereIn('id', $productIds)->update($updateData);

            Log::info('Bulk moderation completed', [
                'product_ids' => $productIds,
                'status' => $status,
                'count' => $count
            ]);

            return $count;

        } catch (Exception $e) {
            Log::error('Failed to bulk moderate products', [
                'product_ids' => $productIds,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
