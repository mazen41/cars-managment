<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\CustomerProductRequest;
use App\Http\Resources\V2\CustomerProductCollection;
use App\Http\Resources\V2\CustomerProductResource;
use App\Models\CustomerProduct;
use App\Services\CustomerProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class CustomerProductController extends Controller
{
    protected $customerProductService;

    public function __construct(CustomerProductService $customerProductService)
    {
        $this->customerProductService = $customerProductService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the authenticated user's customer products.
     *
     * @param Request $request
     * @return CustomerProductCollection
     */
    public function index(Request $request): CustomerProductCollection
    {
        try {
            $filters = [
                'condition' => $request->input('condition'),
                'availability' => $request->input('availability_status'),
                'category_id' => $request->input('category_id'),
            ];

            $query = $this->customerProductService->getUserProducts(auth()->id(), $filters);

            // Apply search if provided
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            if (in_array($sortBy, ['created_at', 'updated_at', 'price', 'name'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = min($request->input('per_page', 15), 50); // Max 50 items per page
            $products = $query->paginate($perPage);

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to fetch user customer products', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }

    /**
     * Store a newly created customer product.
     *
     * @param CustomerProductRequest $request
     * @return JsonResponse
     */
    public function store(CustomerProductRequest $request): JsonResponse
    {
        try {
            $product = $this->customerProductService->createProduct(
                $request->validated(),
                auth()->id()
            );

            $product->load(['category', 'state', 'city', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully and is pending moderation.',
                'data' => new CustomerProductResource($product)
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to create customer product via API', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified customer product.
     *
     * @param CustomerProduct $customerProduct
     * @return JsonResponse
     */
    public function show(CustomerProduct $customerProduct): JsonResponse
    {
        try {
            // Check if user owns this product
            if ($customerProduct->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this product.'
                ], 403);
            }

            $customerProduct->load(['category', 'state', 'city', 'user']);

            return response()->json([
                'success' => true,
                'data' => new CustomerProductResource($customerProduct)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch customer product via API', [
                'product_id' => $customerProduct->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details.'
            ], 500);
        }
    }

    /**
     * Update the specified customer product.
     *
     * @param CustomerProductRequest $request
     * @param CustomerProduct $customerProduct
     * @return JsonResponse
     */
    public function update(CustomerProductRequest $request, CustomerProduct $customerProduct): JsonResponse
    {
        try {
            // Authorization is handled by CustomerProductRequest
            $updatedProduct = $this->customerProductService->updateProduct(
                $customerProduct,
                $request->validated()
            );

            $updatedProduct->load(['category', 'state', 'city', 'user']);

            $message = $updatedProduct->moderation_status === 'pending'
                ? 'Product updated successfully and is pending re-moderation.'
                : 'Product updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new CustomerProductResource($updatedProduct)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update customer product via API', [
                'product_id' => $customerProduct->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified customer product.
     *
     * @param CustomerProduct $customerProduct
     * @return JsonResponse
     */
    public function destroy(CustomerProduct $customerProduct): JsonResponse
    {
        try {
            // Check if user owns this product
            if ($customerProduct->user_id != auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this product.'
                ], 403);
            }

            $this->customerProductService->deleteProduct($customerProduct);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete customer product via API', [
                'product_id' => $customerProduct->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
