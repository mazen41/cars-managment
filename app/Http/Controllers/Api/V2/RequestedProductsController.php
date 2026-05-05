<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\RequestedProduct;
use App\Models\Category;
use App\Http\Resources\RequestedProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Validator;

class RequestedProductsController extends Controller
{
    protected $imageUploadService;
    public function __construct(ImageUploadService $imageUploadService){
        $this->imageUploadService = $imageUploadService;
    }
    /**
     * Get all requested products with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = RequestedProduct::with(['category', 'user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $requestedProducts = $query->published()->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RequestedProductResource::collection($requestedProducts->items()),
            'meta' => [
                'current_page' => $requestedProducts->currentPage(),
                'last_page' => $requestedProducts->lastPage(),
                'per_page' => $requestedProducts->perPage(),
                'total' => $requestedProducts->total(),
            ]
        ]);
    }

    /**
     * Get user's requested products
     */
    public function userRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = RequestedProduct::with(['category'])
            ->byUser($user->id);

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $perPage = $request->get('per_page', 15);
        $requestedProducts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RequestedProductResource::collection($requestedProducts->items()),
            'meta' => [
                'current_page' => $requestedProducts->currentPage(),
                'last_page' => $requestedProducts->lastPage(),
                'per_page' => $requestedProducts->perPage(),
                'total' => $requestedProducts->total(),
            ]
        ]);
    }

    /**
     * Store a new requested product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
            'link' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $user = $request->user();

        // Check if user already requested this product
        $existingRequest = RequestedProduct::where('name', $request->name)
            ->where('requested_by', $user->id)
            ->first();

        if ($existingRequest) {
            // Increment request count
            $existingRequest->increment('request_count');

            return response()->json([
                'success' => true,
                'message' => 'Request count updated for existing product request',
                'data' => new RequestedProductResource($existingRequest->load(['category', 'user']))
            ]);
        }

        //upload images
        if(!empty($validated['photos'])){
            $photoIds = $this->imageUploadService->uploadImages($request->photos, 'uploads/requested-products');
            $photoIds = implode(',', $photoIds);
        }

        $requestedProduct = RequestedProduct::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'photos' => $photoIds ?? '',
            'link' => $validated['link'] ?? null,
            'request_count' => 1,
            'status' => 'pending',
            'requested_by' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product request submitted successfully',
            'data' => new RequestedProductResource($requestedProduct->load(['category', 'user']))
        ], 201);
    }

    /**
     * Get a specific requested product
     */
    public function show(RequestedProduct $requestedProduct): JsonResponse
    {
        $requestedProduct->load(['category', 'user']);

        return response()->json([
            'success' => true,
            'data' => new RequestedProductResource($requestedProduct)
        ]);
    }

    /**
     * Update a requested product (only by the requester or admin)
     */
    public function update(Request $request, RequestedProduct $requestedProduct): JsonResponse
    {
        $user = $request->user();

        // Check if user owns this request or is admin
        if ($requestedProduct->requested_by !== $user->id && !$user->hasRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this request'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
            'link' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
        ]);

         //upload images
        if($validated['photos']){
            $newPhotos = $this->imageUploadService->uploadImages($request->photos, 'uploads/requested-products');
            $newPhotos = implode(',', $newPhotos);
        }
        //delete old photos
        $oldPhotos = explode(',', $requestedProduct->photos);
        $this->imageUploadService->deleteImages($oldPhotos);

        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'photos' => $newPhotos ?? '',
            'link' => $request->link,
            'category_id' => $request->category_id,
        ];

        // Only admin can update status
        if ($user->hasRole(['admin', 'staff']) && $request->filled('status')) {
            $request->validate(['status' => 'in:pending,approved,rejected,published']);
            $updateData['status'] = $request->status;
        }

        $requestedProduct->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Product request updated successfully',
            'data' => new RequestedProductResource($requestedProduct->load(['category', 'user']))
        ]);
    }

    /**
     * Delete a requested product (only by the requester or admin)
     */
    public function destroy(Request $request, RequestedProduct $requestedProduct): JsonResponse
    {
        $user = $request->user();

        // Check if user owns this request or is admin
        if ($requestedProduct->requested_by !== $user->id && !$user->hasRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this request'
            ], 403);
        }
        //delete photos
        $this->imageUploadService->deleteImages(explode(',', $requestedProduct->photos));
        $requestedProduct->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product request deleted successfully'
        ]);
    }

    /**
     * Get popular requested products
     */
    public function popular(Request $request): JsonResponse
    {
        $query = RequestedProduct::with(['category'])
            ->where('status', '!=', 'rejected')
            ->orderBy('request_count', 'desc');

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        $perPage = $request->get('per_page', 10);
        $popularProducts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RequestedProductResource::collection($popularProducts->items()),
            'meta' => [
                'current_page' => $popularProducts->currentPage(),
                'last_page' => $popularProducts->lastPage(),
                'per_page' => $popularProducts->perPage(),
                'total' => $popularProducts->total(),
            ]
        ]);
    }

    /**
     * Get categories with requested product counts
     */
    public function categoriesWithCounts(): JsonResponse
    {
        $categories = Category::withCount(['requestedProducts' => function($query) {
            $query->where('status', '!=', 'rejected');
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
