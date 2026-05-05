<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarCategory;
use App\Models\CarFeature;
use App\Models\CarColor;
use App\Http\Resources\V2\CarResource;
use App\Http\Resources\V2\CarListResource;
use App\Services\CarViewTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Car view tracking service
     */
    protected CarViewTrackingService $viewTrackingService;

    /**
     * Constructor
     */
    public function __construct(CarViewTrackingService $viewTrackingService)
    {
        $this->viewTrackingService = $viewTrackingService;
    }

    /**
     * Get all cars with filtering, searching, and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "page" => "nullable|integer|min:1",
            "per_page" => "nullable|integer|min:1|max:100",
            "sort_by" =>
                "nullable|string|in:price,manufacture_year,created_at,milage,name",
            "sort_order" => "nullable|string|in:asc,desc",
            "search" => "nullable|string|max:255",
            "brand_id" => "nullable|integer|exists:car_brands,id",
            "model_id" => "nullable|integer|exists:car_models,id",
            "category_id" => "nullable|integer|exists:car_categories,id",
            "color_id" => "nullable|integer|exists:car_colors,id",
            "condition" => "nullable|string|in:new,used,certified",
            "transmission" => "nullable|string|in:".implode(",",\App\Enums\CarTransmissionTypeEnum::values()),
            "fuel_type" => "string|in:".implode(",",\App\Enums\CarFuelTypeEnum::values()),
            "min_price" => "nullable|numeric|min:0",
            "max_price" => "nullable|numeric|min:0",
            "min_year" => "nullable|integer|min:1900|max:" . (date("Y") + 1),
            "max_year" => "nullable|integer|min:1900|max:" . (date("Y") + 1),
            "min_milage" => "nullable|numeric|min:0",
            "max_milage" => "nullable|numeric|min:0",
            "features" => "nullable|array",
            "features.*" => "integer|exists:car_features,id",
            "country_id" => "nullable|integer|exists:countries,id",
            "state_id" => "nullable|integer|exists:states,id",
            "city_id" => "nullable|integer|exists:cities,id",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $query = Car::with([
            "brand:id,name,logo",
            "model:id,name,brand_id",
            "category:id,name,image",
            "color:id,name,hex_code",
            "features:id,name,image",
            "country:id,name",
            "state:id,name",
            "city:id,name",
        ])->published()
        ->available();

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                    $q->orWhere("description", "LIKE", "%{$search}%")
                    ->orWhereHas("brand", function ($brandQuery) use ($search) {
                        $brandQuery->where("name", "LIKE", "%{$search}%");
                    })
                    ->orWhereHas("model", function ($modelQuery) use ($search) {
                        $modelQuery->where("name", "LIKE", "%{$search}%");
                    });
            });
        }

        // Brand filter
        if ($request->filled("brand_id")) {
            $query->byBrand($request->brand_id);
        }

        // Model filter
        if ($request->filled("model_id")) {
            $query->byModel($request->model_id);
        }

        // Category filter
        if ($request->filled("category_id")) {
            $query->byCategory($request->category_id);
        }

        // Color filter
        if ($request->filled("color_id")) {
            $query->where("color_id", $request->color_id);
        }

        // Condition filter
        if ($request->filled("condition")) {
            $query->byCondition($request->condition);
        }

        // Transmission filter
        if ($request->filled("transmission")) {
            $query->where("transmission", $request->transmission);
        }

        // Fuel type filter
        if ($request->filled("fuel_type")) {
            $query->where("fuel_type", $request->fuel_type);
        }

        // Price range filter
        if ($request->filled("min_price") || $request->filled("max_price")) {
            $minPrice = $request->get("min_price", 0);
            $maxPrice = $request->get("max_price", PHP_FLOAT_MAX);
            $query->byPriceRange($minPrice, $maxPrice);
        }

        // Year range filter
        if ($request->filled("min_year") || $request->filled("max_year")) {
            $minYear = $request->get("min_year", 1900);
            $maxYear = $request->get("max_year", date("Y") + 1);
            $query->byYearRange($minYear, $maxYear);
        }

        // Milage range filter
        if ($request->filled("min_milage") || $request->filled("max_milage")) {
            $query->whereBetween("milage", [
                $request->get("min_milage", 0),
                $request->get("max_milage", PHP_FLOAT_MAX),
            ]);
        }

        // Features filter
        if ($request->filled("features")) {
            $query->whereHas(
                "features",
                function ($featuresQuery) use ($request) {
                    $featuresQuery->whereIn(
                        "car_features.id",
                        $request->features,
                    );
                },
                "=",
                count($request->features),
            );
        }

        // Location filters
        if ($request->filled("country_id")) {
            $query->where("country_id", $request->country_id);
        }

        if ($request->filled("state_id")) {
            $query->where("state_id", $request->state_id);
        }

        if ($request->filled("city_id")) {
            $query->where("city_id", $request->city_id);
        }

        // Sorting
        $sortBy = $request->get("sort_by", "created_at");
        $sortOrder = $request->get("sort_order", "desc");

        if ($sortBy === "name") {
            $query
                ->orderByRaw(
                    "CONCAT(car_brands.name, ' ', car_models.name) {$sortOrder}",
                )
                ->leftJoin("car_brands", "cars.brand_id", "=", "car_brands.id")
                ->leftJoin("car_models", "cars.model_id", "=", "car_models.id")
                ->select("cars.*");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get("per_page", 15);
        $cars = $query->paginate($perPage);

        return response()->json([
            "result" => true,
            "data" => [
                "cars" => CarListResource::collection($cars),
                "pagination" => [
                    "current_page" => $cars->currentPage(),
                    "last_page" => $cars->lastPage(),
                    "per_page" => $cars->perPage(),
                    "total" => $cars->total(),
                    "from" => $cars->firstItem(),
                    "to" => $cars->lastItem(),
                ],
            ],
            "message" => "Cars retrieved successfully",
        ]);
    }

    /**
     * Get a specific car by ID
     */
    public function show(Request $request, Car $car): JsonResponse
    {
        // Only show published cars to public API
        if (!$car->isPublished()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Car not found",
                ],
                404,
            );
        }

        $car->load([
            "brand:id,name,logo",
            "model:id,name,brand_id",
            "category:id,name,image",
            "color:id,name,hex_code",
            "features:id,name,image,section_id",
            "features.section",
            "country:id,name",
            "state:id,name",
            "city:id,name",
            "customFieldValues.customField:id,name,type",
            "user:id,name,email,phone,user_type",
        ]);

        // Record view with fraud prevention
        $this->viewTrackingService->recordView(
            $car,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent()
        );
        $view_count  = $this->viewTrackingService->getViewCount($car->id);
        $car->views_count = $view_count;

        return response()->json([
            "result" => true,
            "data" => new CarResource($car),
            "message" => "Car retrieved successfully",
        ]);
    }

    /**
     * Get available filter options
     */
    public function filters(Request $request): JsonResponse
    {
        $filters = [
            "brands" => CarBrand::active()
                ->withCount("cars")
                ->having("cars_count", ">", 0)
                ->orderBy("name")
                ->get(["id", "name", "logo"]),

            "categories" => CarCategory::active()
                ->withCount("cars")
                ->having("cars_count", ">", 0)
                ->orderBy("name")
                ->get(["id", "name", "image", "parent_id"]),

            "features" => CarFeature::withCount("cars")
                ->having("cars_count", ">", 0)
                ->orderBy("name")
                ->get(["id", "name", "image"]),

            "colors" => CarColor::withCount("cars")
                ->having("cars_count", ">", 0)
                ->orderBy("name")
                ->get(["id", "name", "hex_code"]),

            "conditions" => DB::table("cars")
                ->select("condition", DB::raw("count(*) as count"))
                ->where("moderation_status", "published")
                ->whereNotNull("condition")
                ->groupBy("condition")
                ->get(),

            "transmissions" => DB::table("cars")
                ->select("transmission", DB::raw("count(*) as count"))
                ->where("moderation_status", "published")
                ->whereNotNull("transmission")
                ->groupBy("transmission")
                ->get(),

            "fuel_types" => DB::table("cars")
                ->select("fuel_type", DB::raw("count(*) as count"))
                ->where("moderation_status", "published")
                ->whereNotNull("fuel_type")
                ->groupBy("fuel_type")
                ->get(),

            "price_range" => [
                "min" => Car::published()->min("price"),
                "max" => Car::published()->max("price"),
            ],

            "year_range" => [
                "min" => Car::published()->min("manufacture_year"),
                "max" => Car::published()->max("manufacture_year"),
            ],

            "milage_range" => [
                "min" => Car::published()->min("milage"),
                "max" => Car::published()->max("milage"),
            ],
        ];

        return response()->json([
            "result" => true,
            "data" => $filters,
            "message" => "Filter options retrieved successfully",
        ]);
    }

    /**
     * Advanced search with autocomplete suggestions
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "query" => "required|string|min:2|max:255",
            "limit" => "integer|min:1|max:50",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $query = $request->input('query');
        $limit = $request->get("limit", 20);

        $suggestions = [
            "cars" => Car::published()
                ->with(["brand:id,name", "model:id,name", "color:id,name"])
                ->where(function ($q) use ($query) {
                    $q->whereHas("brand", function($q) use ($query){
                        $q->where('name', "LIKE", "%{$query}%");
                    })
                    ->orWhereHas(
                        'model',
                        function($q) use($query){
                             $q->where('name', "LIKE", "%{$query}%");
                        }
                    )
                    ->orWhere(
                        "description",
                        "LIKE",
                        "%{$query}%",
                    );
                })
                ->limit($limit)
                ->get([
                    "id",
                    "brand_id",
                    "model_id",
                    "color_id",
                    'manufacture_year',
                    "price",
                    "main_photo",
                ])
                ->map(function ($car) {
                    return [
                        "id" => $car->id,
                        "name" => $car->car_name,
                        "price" => $car->formatted_price,
                        "main_photo_url" => $car->main_photo_url,
                        "type" => "car",
                    ];
                }),

            "brands" => CarBrand::active()
                ->where("name", "LIKE", "%{$query}%")
                ->limit(10)
                ->get(["id", "name", "logo"])
                ->map(function ($brand) {
                    return [
                        "id" => $brand->id,
                        "name" => $brand->name,
                        "logo_url" => $brand->logo_url,
                        "type" => "brand",
                    ];
                }),

            "models" => CarModel::whereHas("brand", function ($q) {
                $q->where("status", "active");
            })
                ->where("name", "LIKE", "%{$query}%")
                ->with("brand:id,name")
                ->limit(10)
                ->get(["id", "name", "brand_id"])
                ->map(function ($model) {
                    return [
                        "id" => $model->id,
                        "name" => $model->name,
                        "brand_name" => $model->brand->name,
                        "type" => "model",
                    ];
                }),
        ];

        return response()->json([
            "result" => true,
            "data" => $suggestions,
            "message" => "Search suggestions retrieved successfully",
        ]);
    }

    /**
     * Get featured/popular cars
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get("limit", 10);

        $featuredCars = Car::published()
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name,image",
                "color:id,name,hex_code",
                "city:id,name",
                "state:id,name",
            ])
            ->orderBy("created_at", "desc")
            ->where('featured', true)
            ->limit($limit > 50 ? 50 : $limit)
            ->get();

        return response()->json([
            "result" => true,
            "data" => CarListResource::collection($featuredCars),
            "message" => "Featured cars retrieved successfully",
        ]);
    }

    /**
     * Get todays deal cars
     */
    public function todaysDeal(Request $request) {
        $limit = $request->get("limit", 10);

        $cars = Car::published()
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name,image",
                "color:id,name,hex_code",
                "city:id,name",
                "state:id,name",
            ])
            ->orderBy("created_at", "desc")
            ->where('todays_deal', true)
            ->limit($limit > 50 ? 50 : $limit)
            ->get();

        return response()->json([
            "result" => true,
            "data" => CarListResource::collection($cars),
            "message" => "Todays Deal cars retrieved successfully",
        ]);
    }

    /**
     * Get similar cars based on brand, model, or category
     */
    public function similar(Request $request, Car $car): JsonResponse
    {
        $limit = $request->get("limit", 6);

        $similarCars = Car::published()
        ->available()
            ->where("id", "!=", $car->id)
            ->where(function ($query) use ($car) {
                $query
                    ->where("model_id", $car->model_id);
            })
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name,image",
                "color:id,name,hex_code",
                "city:id,name",
                "state:id,name",
            ])
            ->orderBy("created_at", "desc")
            ->limit($limit > 50 ? 50 : $limit)
            ->get();

        return response()->json([
            "result" => true,
            "data" => CarListResource::collection($similarCars),
            "message" => "Similar cars retrieved successfully",
        ]);
    }

    /**
     * Get car statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = [
            "total_cars" => Car::published()->count(),
            "total_brands" => CarBrand::active()->count(),
            "total_models" => CarModel::active()->count(),
            "total_categories" => CarCategory::active()->count(),
            "conditions" => Car::published()
                ->select("condition", DB::raw("COUNT(*) as count"))
                ->whereNotNull("condition")
                ->groupBy("condition")
                ->get(),
            "fuel_types" => Car::published()
                ->select("fuel_type", DB::raw("COUNT(*) as count"))
                ->whereNotNull("fuel_type")
                ->groupBy("fuel_type")
                ->get(),
            "transmission_types" => Car::published()
                ->select("transmission", DB::raw("COUNT(*) as count"))
                ->whereNotNull("transmission")
                ->groupBy("transmission")
                ->get(),
            "price_stats" => [
                "min" => Car::published()->min("price"),
                "max" => Car::published()->max("price"),
                "average" => Car::published()->avg("price"),
            ],
            "year_stats" => [
                "min" => Car::published()->min("manufacture_year"),
                "max" => Car::published()->max("manufacture_year"),
            ],
            "recent_additions" => Car::published()
                ->with(["brand:id,name", "model:id,name"])
                ->orderBy("created_at", "desc")
                ->limit(5)
                ->get()
                ->map(function ($car) {
                    return [
                        "id" => $car->id,
                        "name" => $car->car_name,
                        "price" => $car->formatted_price,
                        "main_photo_url" => $car->main_photo_url,
                        "created_at" => $car->created_at,
                    ];
                }),
        ];

        return response()->json([
            "result" => true,
            "data" => $stats,
            "message" => "Statistics retrieved successfully",
        ]);
    }

    /**
     * Get cars by brand
     */
    public function carsByBrand(Request $request, $brandId): JsonResponse
    {
        $validator = Validator::make(
            array_merge($request->all(), ["brand_id" => $brandId]),
            [
                "brand_id" => "required|integer|exists:car_brands,id",
                "page" => "integer|min:1",
                "per_page" => "integer|min:1|max:50",
                "sort_by" =>
                    "string|in:price,manufacture_year,created_at,milage",
                "sort_order" => "string|in:asc,desc",
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $brand = CarBrand::findOrFail($brandId);

        $query = Car::published()
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name,image",
                "color:id,name,hex_code",
            ])
            ->byBrand($brandId);

        // Sorting
        $sortBy = $request->get("sort_by", "created_at");
        $sortOrder = $request->get("sort_order", "desc");
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get("per_page", 15);
        $cars = $query->paginate($perPage);

        return response()->json([
            "result" => true,
            "data" => [
                "brand" => [
                    "id" => $brand->id,
                    "name" => $brand->name,
                    "logo_url" => $brand->logo_url,
                ],
                "cars" => CarListResource::collection($cars),
                "pagination" => [
                    "current_page" => $cars->currentPage(),
                    "last_page" => $cars->lastPage(),
                    "per_page" => $cars->perPage(),
                    "total" => $cars->total(),
                    "from" => $cars->firstItem(),
                    "to" => $cars->lastItem(),
                ],
            ],
            "message" => "Cars by brand retrieved successfully",
        ]);
    }

    /**
     * Get cars by category
     */
    public function carsByCategory(Request $request, $categoryId): JsonResponse
    {
        $validator = Validator::make(
            array_merge($request->all(), ["category_id" => $categoryId]),
            [
                "category_id" => "required|integer|exists:car_categories,id",
                "page" => "integer|min:1",
                "per_page" => "integer|min:1|max:50",
                "sort_by" =>
                    "string|in:price,manufacture_year,created_at,milage",
                "sort_order" => "string|in:asc,desc",
                "include_subcategories" => "boolean",
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $category = CarCategory::findOrFail($categoryId);

        $query = Car::published()->with([
            "brand:id,name,logo",
            "model:id,name",
            "category:id,name,image",
            "color:id,name,hex_code",
        ]);

        // Include subcategories if requested
        if ($request->get("include_subcategories", false)) {
            $categoryIds = [$categoryId];
            $subcategories = CarCategory::where(
                "parent_id",
                $categoryId,
            )->pluck("id");
            $categoryIds = array_merge($categoryIds, $subcategories->toArray());
            $query->whereIn("category_id", $categoryIds);
        } else {
            $query->byCategory($categoryId);
        }

        // Sorting
        $sortBy = $request->get("sort_by", "created_at");
        $sortOrder = $request->get("sort_order", "desc");
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get("per_page", 15);
        $cars = $query->paginate($perPage);

        return response()->json([
            "result" => true,
            "data" => [
                "category" => [
                    "id" => $category->id,
                    "name" => $category->name,
                    "image_url" => $category->image_url,
                    "parent_id" => $category->parent_id,
                ],
                "cars" => CarListResource::collection($cars),
                "pagination" => [
                    "current_page" => $cars->currentPage(),
                    "last_page" => $cars->lastPage(),
                    "per_page" => $cars->perPage(),
                    "total" => $cars->total(),
                    "from" => $cars->firstItem(),
                    "to" => $cars->lastItem(),
                ],
            ],
            "message" => "Cars by category retrieved successfully",
        ]);
    }

    /**
     * Compare multiple cars
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "car_ids" => "required|array|min:2|max:4",
            "car_ids.*" => "integer|exists:cars,id",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $cars = Car::published()
            ->whereIn("id", $request->car_ids)
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name",
                "color:id,name,hex_code",
                "features:id,name",
            ])
            ->get();

        if ($cars->count() !== count($request->car_ids)) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Some cars not found or not published",
                ],
                404,
            );
        }

        $comparisonData = $cars->map(function ($car) {
            return [
                "id" => $car->id,
                "name" => $car->car_name,
                "price" => $car->price,
                "formatted_price" => $car->formatted_price,
                "condition" => $car->condition,
                "milage" => $car->milage,
                "formatted_milage" => $car->formatted_milage,
                "manufacture_year" => $car->manufacture_year,
                "transmission" => $car->transmission,
                "fuel_type" => $car->fuel_type,
                "main_photo_url" => $car->main_photo_url,
                "brand" => $car->brand,
                "model" => $car->model,
                "category" => $car->category,
                "color" => $car->color,
                "features" => $car->features->pluck("name")->toArray(),
                "features_count" => $car->features->count(),
                "can_be_reserved" => $car->canBeReserved(),
            ];
        });

        return response()->json([
            "result" => true,
            "data" => [
                "cars" => $comparisonData,
                "comparison_fields" => [
                    "price",
                    "condition",
                    "milage",
                    "manufacture_year",
                    "transmission",
                    "fuel_type",
                    "features",
                ],
            ],
            "message" => "Car comparison data retrieved successfully",
        ]);
    }

    /**
     * Record car view for analytics (explicit endpoint)
     */
    public function recordView(Request $request, Car $car): JsonResponse
    {
        // Only record views for published cars
        if (!$car->isPublished()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Car not found",
                ],
                404,
            );
        }

        // Record view with fraud prevention
        $recorded = $this->viewTrackingService->recordView(
            $car,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            "result" => true,
            "data" => [
                "recorded" => $recorded,
                "message" => $recorded
                    ? "View recorded successfully"
                    : "View already recorded recently",
            ],
            "message" => "View tracking processed",
        ]);
    }

    /**
     * Get view statistics for a car
     */
    public function viewStatistics(Request $request, Car $car): JsonResponse
    {
        if (!$car->isPublished()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Car not found",
                ],
                404,
            );
        }

        $statistics = $this->viewTrackingService->getViewStatistics($car->id);

        return response()->json([
            "result" => true,
            "data" => $statistics,
            "message" => "View statistics retrieved successfully",
        ]);
    }

    /**
     * Get popular cars based on views and interactions
     */
    public function popular(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "limit" => "integer|min:1|max:50",
            "period" => "string|in:day,week,month,all",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $limit = $request->get("limit", 10);
        $period = $request->get("period", "week");

        $query = Car::published()->with([
            "brand:id,name,logo",
            "model:id,name",
            "category:id,name,image",
            "color:id,name,hex_code",
        ]);

        // Add time-based filtering based on period
        switch ($period) {
            case "day":
                $query->where("created_at", ">=", now()->subDay());
                break;
            case "week":
                $query->where("created_at", ">=", now()->subWeek());
                break;
            case "month":
                $query->where("created_at", ">=", now()->subMonth());
                break;
            // 'all' - no time filter
        }

        // Order by created_at for now - in real implementation, order by view count
        $popularCars = $query
            ->orderBy("created_at", "desc")
            ->limit($limit)
            ->get();

        return response()->json([
            "result" => true,
            "data" => CarListResource::collection($popularCars),
            "message" => "Popular cars retrieved successfully",
        ]);
    }

    /**
     * Get cars near a location (requires coordinates)
     */
    public function nearbyCars(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "latitude" => "required|numeric|between:-90,90",
            "longitude" => "required|numeric|between:-180,180",
            "radius" => "integer|min:1|max:100", // km
            "limit" => "integer|min:1|max:50",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->get("radius", 10); // Default 10km
        $limit = $request->get("limit", 20);

        // Using Haversine formula for distance calculation
        // Note: This requires latitude/longitude columns in cars table
        $cars = Car::published()
            ->with([
                "brand:id,name,logo",
                "model:id,name",
                "category:id,name,image",
                "color:id,name,hex_code",
            ])
            ->selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat],
            )
            ->having("distance", "<=", $radius)
            ->orderBy("distance")
            ->limit($limit)
            ->get();

        return response()->json([
            "result" => true,
            "data" => [
                "cars" => CarListResource::collection($cars),
                "search_params" => [
                    "latitude" => $lat,
                    "longitude" => $lng,
                    "radius_km" => $radius,
                ],
            ],
            "message" => "Nearby cars retrieved successfully",
        ]);
    }

    /**
     * Get price suggestions for similar cars
     */
    public function priceSuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "brand_id" => "integer|exists:car_brands,id",
            "model_id" => "integer|exists:car_models,id",
            "category_id" => "integer|exists:car_categories,id",
            "manufacture_year" => "integer|min:1900|max:" . (date("Y") + 1),
            "condition" => "string|in:new,used,certified",
            "milage" => "numeric|min:0",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $query = Car::published();

        // Apply filters to find similar cars
        if ($request->filled("brand_id")) {
            $query->where("brand_id", $request->brand_id);
        }
        if ($request->filled("model_id")) {
            $query->where("model_id", $request->model_id);
        }
        if ($request->filled("category_id")) {
            $query->where("category_id", $request->category_id);
        }
        if ($request->filled("condition")) {
            $query->where("condition", $request->condition);
        }

        // Year range (±2 years)
        if ($request->filled("manufacture_year")) {
            $year = $request->manufacture_year;
            $query->whereBetween("manufacture_year", [$year - 2, $year + 2]);
        }

        $prices = $query->pluck("price")->filter()->values();

        if ($prices->count() < 3) {
            return response()->json([
                "result" => true,
                "data" => [
                    "message" =>
                        "Not enough similar cars found for price comparison",
                    "sample_count" => $prices->count(),
                ],
                "message" => "Insufficient data for price suggestions",
            ]);
        }

        $suggestions = [
            "sample_count" => $prices->count(),
            "price_range" => [
                "min" => $prices->min(),
                "max" => $prices->max(),
                "average" => round($prices->avg(), 2),
                "median" => $prices->median(),
            ],
            "percentiles" => [
                "25th" => $prices->percentile(25),
                "50th" => $prices->percentile(50),
                "75th" => $prices->percentile(75),
                "90th" => $prices->percentile(90),
            ],
            "suggested_range" => [
                "competitive" => round($prices->percentile(25), 2),
                "market_average" => round($prices->avg(), 2),
                "premium" => round($prices->percentile(75), 2),
            ],
        ];

        return response()->json([
            "result" => true,
            "data" => $suggestions,
            "message" => "Price suggestions retrieved successfully",
        ]);
    }

    /**
     * Get car availability calendar (for rentals/test drives)
     */
    public function availability(Request $request, Car $car): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "start_date" => "date|after_or_equal:today",
            "end_date" => "date|after:start_date",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        if (!$car->isPublished()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Car not available",
                ],
                404,
            );
        }

        $startDate = $request->get("start_date", now()->format("Y-m-d"));
        $endDate = $request->get(
            "end_date",
            now()->addDays(30)->format("Y-m-d"),
        );

        // Check reservations
        $unavailableDates = [];

        // Get reserved dates
        $reservations = $car
            ->reservations()
            ->whereBetween("reserved_at", [$startDate, $endDate])
            ->get(["reserved_at", "status"]);

        foreach ($reservations as $reservation) {
            if ($reservation->status === "confirmed") {
                $unavailableDates[] = $reservation->reserved_at->format(
                    "Y-m-d",
                );
            }
        }

        // Generate availability calendar
        $calendar = [];
        $current = now()->parse($startDate);
        $end = now()->parse($endDate);

        while ($current <= $end) {
            $dateStr = $current->format("Y-m-d");
            $calendar[] = [
                "date" => $dateStr,
                "available" => !in_array($dateStr, $unavailableDates),
                "day_name" => $current->format("l"),
                "is_weekend" => $current->isWeekend(),
            ];
            $current->addDay();
        }

        return response()->json([
            "result" => true,
            "data" => [
                "car_id" => $car->id,
                "car_name" => $car->car_name,
                "period" => [
                    "start_date" => $startDate,
                    "end_date" => $endDate,
                ],
                "calendar" => $calendar,
                "summary" => [
                    "total_days" => count($calendar),
                    "available_days" => count(
                        array_filter($calendar, fn($day) => $day["available"]),
                    ),
                    "unavailable_days" => count($unavailableDates),
                ],
            ],
            "message" => "Car availability retrieved successfully",
        ]);
    }
}
