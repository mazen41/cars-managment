<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarRequest;
use App\Http\Resources\V2\Seller\CarResource;
use App\Http\Resources\V2\Seller\CarFormDataResource;
use App\Http\Resources\V2\Seller\CarStatsResource;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarCategory;
use App\Models\CarColor;
use App\Models\CarFeature;
use App\Models\CarCustomField;
use App\Models\CarCustomFieldValue;
use App\Models\Country;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use App\Http\Requests\Api\V2\Seller\CreateCarRequest;
use App\Http\Requests\Api\V2\Seller\UpdateCarRequest;
use App\Events\CarAdded;
use App\Events\CarDeleted;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of seller's cars.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Car::where('user_id', Auth::id())
            ->with(['brand', 'model', 'category', 'color', 'features']);

        // Apply filters
        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }

        if ($request->filled('status')) {
            $query->where('car_status', $request->status);
        }

         if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('brand_id')) {
            $query->byBrand($request->brand_id);
        }

        if ($request->filled('model_id')) {
            $query->byModel($request->model_id);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('condition')) {
            $query->byCondition($request->condition);
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        if ($request->filled('min_year') && $request->filled('max_year')) {
            $query->byYearRange($request->min_year, $request->max_year);
        }

        if($request->filled('has_inspection')){
            $query->whereHas('inspections');
        }

        if($request->filled('has_reservations')){
            $query->whereHas('reservations');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vin', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('color', function($query) use($search){
                        $query->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $cars = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'  => CarResource::collection($cars),
            'pagination' => [
                'current_page' => $cars->currentPage(),
                'last_page' => $cars->lastPage(),
                'per_page' => $cars->perPage(),
                'total' => $cars->total()
            ]
        ]);
    }

    /**
     * Get custom fields for car creation/editing.
     */
    public function getCustomFields(): JsonResponse
    {
        $customFields = CarCustomField::with('options')->ordered()->get();

        $formattedFields = $customFields->map(function ($field) {
            return [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type,
                'required' => $field->required,
                'placeholder' => $field->placeholder,
                'options' => $field->options ? $field->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'value' => $option->value,
                        'label' => $option->label,
                    ];
                }) : [],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedFields,
            'message' => 'Custom fields retrieved successfully'
        ]);
    }

    /**
     * Get form data for car creation.
     */
    public function getFormData(): JsonResponse
    {
        $formData = [
            'brands' => CarBrand::active()->get(),
            'categories' => CarCategory::active()->parents()->get(),
            'features' => CarFeature::active()->with('section')->get(),
            'custom_fields' => CarCustomField::with('options')->ordered()->get(),
            'countries' => Country::isEnabled()->get(),
            'colors' => CarColor::get(),
        ];

        return (new CarFormDataResource($formData))->response()->setStatusCode(200);
    }

    /**
     * Get Category Brands
     */

    public function getCategoryBrands(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:car_categories,id']
        ]);
        $category_id = $request->category_id;
        $brands = CarBrand::whereHas('categories', function($query) use($category_id)  {
            $query->where('car_category_id', $category_id);
        })->get();
        $brands = $brands->map(function($brand) {
            return [
                'id'    => (int) $brand->id,
                'name'  => $brand->name,
                'logo_url'  => uploaded_asset($brand->logo),
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Store a newly created car in storage.
     */
    public function store(CreateCarRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $carData = $request->validated();

            // Set seller as owner and default statuses
            $carData['user_id'] = Auth::id();
            $carData['moderation_status'] = CarModerationStatusEnum::PENDING;
            $carData['car_status'] = CarStatusEnum::AVAILABLE;

            $car = Car::create($carData);

            // Attach features
            if ($request->filled('features')) {
                $car->features()->sync($request->features);
            }

            // Handle custom fields
            $this->storeCustomFieldValues($car, $request->validated());

            DB::commit();

            // Dispatch event to notify admin/staff of new car
            event(new CarAdded($car));

            return response()->json([
                'success' => true,
                'data' => new CarResource($car->load(['brand', 'model', 'category', 'color', 'features'])),
                'message' => 'Car created successfully and submitted for review'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create car',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified car.
     */
    public function show($id): JsonResponse
    {
        $car = Car::where('user_id', Auth::id())
            ->with([
                'brand', 'model', 'category', 'color', 'user', 'features','features.section','inspections','reservations',
                'customFieldValues.customField', 'customFieldValues.customField.options',
                'country', 'state', 'city'
            ])
            ->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CarResource($car),
            'message'   => 'Car details fetched successfully'
        ]);
    }

    /**
     * Show the form for editing the specified car.
     */
    public function edit($id): JsonResponse
    {
        $car = Car::where('user_id', Auth::id())
             ->with([
                'brand', 'model', 'category', 'color', 'user', 'features','features.section','inspections','reservations',
                'customFieldValues.customField', 'customFieldValues.customField.options',
                'country', 'state', 'city'
            ])
            ->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $formData = [
            'car' => $car,
            'brands' => CarBrand::active()->get(),
            'categories' => CarCategory::active()->parents()->get(),
            'features' => CarFeature::active()->get(),
            'custom_fields' => CarCustomField::with('options')->ordered()->get(),
            'countries' => Country::isEnabled()->get(),
            'colors' => CarColor::get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'car' => new CarResource($car),
                'form_data' => new CarFormDataResource($formData),
            ],
            'message' => 'Car edit data retrieved successfully'
        ]);
    }

    /**
     * Update the specified car in storage.
     */
    public function update(UpdateCarRequest $request, Car $car): JsonResponse
    {

        try {
            DB::beginTransaction();

            $carData = $request->validated();

            // Reset moderation status to pending if car was rejected or if significant changes
            if ($car->moderation_status === CarModerationStatusEnum::REJECTED) {
                $carData['moderation_status'] = CarModerationStatusEnum::PENDING;
            }
            $car->update($carData);

            // Sync features
            if ($request->has('features')) {
                $car->features()->sync($request->features ?? []);
            }

            // Handle custom fields
            $this->updateCustomFieldValues($car, $request->validated());

            DB::commit();

            return response()->json([
                'success'=> true,
                'message' => 'Car updated successfully',
                'data' => new CarResource($car->load(['brand', 'model', 'category', 'color', 'features']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update car',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified car from storage.
     */
    public function destroy($id): JsonResponse
    {
        $car = Car::where('user_id', Auth::id())->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        if (!$car->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete car. It has associated inspections or reservations.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Prepare car data before deletion for notification
            $carData = [
                'id' => $car->id,
                'name' => $car->name ?? $car->car_name,
                'car_name' => $car->car_name ?? 'Car #' . $car->id,
                'seller_id' => $car->user_id,
                'price' => $car->price,
            ];

            // Detach features
            $car->features()->detach();

            // Delete custom field values
            $car->customFieldValues()->delete();

            // Delete the car
            $car->delete();

            DB::commit();

            // Dispatch event to notify seller of deletion (self-deletion)
            event(new CarDeleted(
                $carData,
                'Deleted by seller',
                Auth::user()->name ?? 'Seller'
            ));

            return response()->json([
                'success' => true,
                'message' => 'Car deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete car',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get models by brand.
     */
    public function getModelsByBrand($brandId): JsonResponse
    {
        $models = CarModel::byBrand($brandId)->active()->get();

        $formattedModels = $models->map(function ($model) {
            return [
                'id' => $model->id,
                'name' => $model->getTranslation('name'),
                'brand_id' => $model->brand_id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedModels,
            'message' => 'Models retrieved successfully'
        ]);
    }

    /**
     * Get seller's car statistics.
     */
    public function getStats(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total_cars' => Car::where('user_id', $userId)->count(),
            'published_cars' => Car::where('user_id', $userId)
                ->where('moderation_status', CarModerationStatusEnum::PUBLISHED)->count(),
            'pending_cars' => Car::where('user_id', $userId)
                ->where('moderation_status', CarModerationStatusEnum::PENDING)->count(),
            'rejected_cars' => Car::where('user_id', $userId)
                ->where('moderation_status', CarModerationStatusEnum::REJECTED)->count(),
            'available_cars' => Car::where('user_id', $userId)
                ->where('car_status', CarStatusEnum::AVAILABLE)->count(),
            'reserved_cars' => Car::where('user_id', $userId)
                ->where('car_status', CarStatusEnum::RESERVED)->count(),
            'sold_cars' => Car::where('user_id', $userId)
                ->where('car_status', CarStatusEnum::SOLD)->count(),
            'in_auction_cars' => Car::where('user_id', $userId)
                ->where('car_status', CarStatusEnum::IN_AUCTION)->count(),
        ];

        return (new CarStatsResource($stats))->response()->setStatusCode(200);
    }

    /**
     * Store custom field values for a car.
     */
    private function storeCustomFieldValues(Car $car, array $validatedRequest): void
    {
        $customFields = CarCustomField::all();

        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";

            if (isset($validatedRequest[$fieldKey])) {
                $value = $validatedRequest[$fieldKey];
                if (!empty($value) || $field->required) {
                    CarCustomFieldValue::create([
                        'car_id' => $car->id,
                        'custom_field_id' => $field->id,
                        'value' => $value,
                    ]);
                }
            }
        }
    }

    /**
     * Update custom field values for a car.
     */
    private function updateCustomFieldValues(Car $car, array $validatedRequest): void
    {
        $customFields = CarCustomField::all();

        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";

            if (isset($validatedRequest[$fieldKey])) {
                $value = $validatedRequest[$fieldKey];
                CarCustomFieldValue::updateOrCreate(
                    [
                        'car_id' => $car->id,
                        'custom_field_id' => $field->id,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            } else {
                // Remove value if not provided and not required
                if (!$field->required) {
                    CarCustomFieldValue::where('car_id', $car->id)
                        ->where('custom_field_id', $field->id)
                        ->delete();
                }
            }
        }
    }
}
