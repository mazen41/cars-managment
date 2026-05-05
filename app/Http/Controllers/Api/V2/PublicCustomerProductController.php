<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CustomerProductCollection;
use App\Http\Resources\V2\CustomerProductResource;
use App\Models\CustomerProduct;
use App\Services\CustomerProductService;
use App\Services\CustomerProductViewTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class PublicCustomerProductController extends Controller
{
    protected $customerProductService;
    protected $viewTrackingService;

    public function __construct(
        CustomerProductService $customerProductService,
        CustomerProductViewTrackingService $viewTrackingService
    ) {
        $this->customerProductService = $customerProductService;
        $this->viewTrackingService = $viewTrackingService;
        // No authentication required for public endpoints
    }

    /**
     * Display a listing of approved customer products for public browsing.
     *
     * @param Request $request
     * @return CustomerProductCollection
     */
    public function index(Request $request): CustomerProductCollection
    {
        try {
            $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'state_id' => 'nullable|exists:states,id',
                'city_id' => 'nullable|exists:cities,id',
                'condition' => 'nullable|in:new,used',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|in:created_at,price,name',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $filters = [
                'category_id' => $request->input('category_id'),
                'state_id' => $request->input('state_id'),
                'city_id' => $request->input('city_id'),
                'condition' => $request->input('condition'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $query = $this->customerProductService->getPublicProducts($filters);

            $perPage = min($request->input('per_page', 15), 50); // Max 50 items per page
            $products = $query->paginate($perPage);

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to fetch public customer products', [
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }

    /**
     * Display the specified approved customer product for public viewing.
     *
     * @param CustomerProduct $customerProduct
     * @return JsonResponse
     */
    public function show(CustomerProduct $customerProduct): JsonResponse
    {
        try {
            // Only show approved and available products to public
            if ($customerProduct->moderation_status !== 'approved' ||
                $customerProduct->availability_status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or not available.'
                ], 404);
            }

            $customerProduct->load(['category', 'state', 'city', 'user']);

            // Record view (fraud-protected, Redis-backed)
            $this->viewTrackingService->recordView(
                $customerProduct,
                auth('api')->id() ? (string) auth('api')->id() : null,
                request()->ip(),
                request()->userAgent()
            );
            $view_count  = $this->viewTrackingService->getViewCount($customerProduct->id);
            $customerProduct->views_count = $view_count;

            return response()->json([
                'success' => true,
                'data' => new CustomerProductResource($customerProduct)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch public customer product', [
                'product_id' => $customerProduct->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details.'
            ], 500);
        }
    }

    /**
     * Search approved customer products for public browsing.
     *
     * @param Request $request
     * @return CustomerProductCollection
     */
    public function search(Request $request): CustomerProductCollection
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'state_id' => 'nullable|exists:states,id',
                'city_id' => 'nullable|exists:cities,id',
                'condition' => 'nullable|in:new,used',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|in:created_at,price,name',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $searchQuery = $request->input('q');
            $filters = [
                'category_id' => $request->input('category_id'),
                'state_id' => $request->input('state_id'),
                'city_id' => $request->input('city_id'),
                'condition' => $request->input('condition'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $query = $this->customerProductService->searchProducts($searchQuery, $filters);

            $perPage = min($request->input('per_page', 15), 50);
            $products = $query->paginate($perPage);

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to search public customer products', [
                'search_query' => $request->input('q'),
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }

    /**
     * Get approved products by category for public browsing.
     *
     * @param Request $request
     * @param int $categoryId
     * @return CustomerProductCollection
     */
    public function byCategory(Request $request, int $categoryId): CustomerProductCollection
    {
        try {
            $request->validate([
                'state_id' => 'nullable|exists:states,id',
                'city_id' => 'nullable|exists:cities,id',
                'condition' => 'nullable|in:new,used',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|in:created_at,price,name',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $filters = [
                'category_id' => $categoryId,
                'state_id' => $request->input('state_id'),
                'city_id' => $request->input('city_id'),
                'condition' => $request->input('condition'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $query = $this->customerProductService->getPublicProducts($filters);

            $perPage = min($request->input('per_page', 15), 50);
            $products = $query->paginate($perPage);

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to fetch public products by category', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }

    /**
     * Get approved products by location for public browsing.
     *
     * @param Request $request
     * @param int $stateId
     * @param int|null $cityId
     * @return CustomerProductCollection
     */
    public function byLocation(Request $request, int $stateId, int $cityId = null): CustomerProductCollection
    {
        try {
            $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'condition' => 'nullable|in:new,used',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|in:created_at,price,name',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $filters = [
                'state_id' => $stateId,
                'city_id' => $cityId,
                'category_id' => $request->input('category_id'),
                'condition' => $request->input('condition'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $query = $this->customerProductService->getPublicProducts($filters);

            $perPage = min($request->input('per_page', 15), 50);
            $products = $query->paginate($perPage);

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to fetch public products by location', [
                'state_id' => $stateId,
                'city_id' => $cityId,
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }

    /**
     * Get product statistics for public viewing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'state_id' => 'nullable|exists:states,id',
                'city_id' => 'nullable|exists:cities,id',
            ]);

            $query = CustomerProduct::approved()->available();

            // Apply filters
            if ($request->filled('category_id')) {
                $query->byCategory($request->input('category_id'));
            }

            if ($request->filled('state_id')) {
                $query->byLocation($request->input('state_id'), $request->input('city_id'));
            }

            $stats = [
                'total_products' => $query->count(),
                'total_categories' => $query->distinct('category_id')->count('category_id'),
                'total_locations' => $query->distinct('state_id')->count('state_id'),
                'condition_breakdown' => [
                    'new' => (clone $query)->where('condition', 'new')->count(),
                    'used' => (clone $query)->where('condition', 'used')->count(),
                ],
                'price_range' => [
                    'min' => $query->min('price') ?? 0,
                    'max' => $query->max('price') ?? 0,
                    'average' => round($query->avg('price') ?? 0, 2),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch public product statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get featured/recommended products for public browsing.
     *
     * @param Request $request
     * @return CustomerProductCollection
     */
    public function featured(Request $request): CustomerProductCollection
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:20',
            ]);

            $limit = $request->input('limit', 10);

            // Get recently added approved products as featured
            $products = CustomerProduct::approved()
                ->available()
                ->with(['category', 'state', 'city', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return new CustomerProductCollection($products);

        } catch (Exception $e) {
            Log::error('Failed to fetch featured customer products', [
                'error' => $e->getMessage()
            ]);

            return new CustomerProductCollection(collect([]));
        }
    }
}
