<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
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
use App\Exports\CarsExport;
use App\Traits\HandlesExports;
use App\Events\CarStatusChanged;
use App\Events\CarModerationStatusChanged;
use App\Events\CarDeleted;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    use HandlesExports;

    public function __construct()
    {
        $this->middleware('permission:view_cars');
        $this->middleware('permission:edit_cars')->only(['edit', 'store', 'create','update', 'bulk-update-status', 'updateFeaturedAndTodaysDeal']);
        $this->middleware('permission:delete_cars')->only(['destroy', 'bulk-destroy']);
    }
    /**
     * Display a listing of the cars.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Car::with(["brand", "model", "category", "user", "features"]);

        // Apply filters
        if ($request->filled("brand_id")) {
            $query->byBrand($request->brand_id);
        }

        if ($request->filled("model_id")) {
            $query->byModel($request->model_id);
        }

        if ($request->filled("category_id")) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled("condition")) {
            $query->byCondition($request->condition);
        }

        if ($request->filled("min_price") && $request->filled("max_price")) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        if ($request->filled("min_year") && $request->filled("max_year")) {
            $query->byYearRange($request->min_year, $request->max_year);
        }

        if ($request->filled("moderation_status")) {
            $query->where("moderation_status", $request->moderation_status);
        }

        if ($request->filled("car_status")) {
            $query->where("car_status", $request->car_status);
        }

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vin', "LIKE", "%{$search}%")
                    ->orWhere("description", "LIKE", "%{$search}%")
                    ->orWhereHas("color", function($query) use($search){
                        $query->where('name', "LIKE", "%{$search}%");
                    })
                    ->orWhere("location", "LIKE", "%{$search}%");
            });
        }

        // Filter by Seller
        if ($request->filled("seller_id")) {
            $query->where("user_id", $request->seller_id);
        }

        // Filter by user type Seller
        if ($request->filled("user_type")) {
            if($request->user_type == 'seller'){
                $query->whereHas('user', function($q){
                    $q->where('user_type', 'seller');
                });
            }
        } else {
                // Default to Admin and Staff Cars
                $query->whereHas('user', function($q){
                    $q->where('user_type', 'admin')->orWhere('user_type', 'staff');
                });
            }
        // Sorting
        $sortBy = $request->get("sort_by", "created_at");
        $sortOrder = $request->get("sort_order", "desc");
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get("per_page", 15);
        $cars = $query->paginate($perPage);

        // Load filter data for dropdowns
        $brands = CarBrand::active()->get();
        $categories = CarCategory::active()->parents()->get();
        $features = CarFeature::active()->get();

        if ($request->wantsJson()) {
            return response()->json([
                "cars" => $cars,
                "brands" => $brands,
                "categories" => $categories,
                "features" => $features,
            ]);
        }

        return view(
            "backend.cars.cars.index",
            compact("cars", "brands", "categories", "features"),
        );
    }

    /**
     * Show the form for creating a new car.
     */
    public function create(): View
    {
        $brands = CarBrand::active()->get();
        $categories = CarCategory::active()->parents()->get();
        $features = CarFeature::with('section')->active()->get()->groupBy(function($feature){
            return $feature->section->name ?? translate('Genearal');
        });

        $customFields = CarCustomField::ordered()->get();
        $countries = Country::isEnabled()->get();
        $colors = CarColor::active()->get();

        return view(
            "backend.cars.cars.create",
            compact(
                "brands",
                "categories",
                "features",
                "customFields",
                "countries",
                "colors",
            ),
        );
    }

    /**
     * Store a newly created car in storage.
     */
    public function store(CarRequest $request): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            $carData = $request->only([
                "vin",
                "description",
                "brand_id",
                "model_id",
                "category_id",
                "color_id",
                "condition",
                "milage",
                "manufacture_year",
                "transmission",
                "fuel_type",
                "location",
                "price",
                "country_id",
                "state_id",
                "city_id",
                "moderation_status",
                "car_status",
                "main_photo",
                "photos",
            ]);

            $carData["user_id"] = Auth::id();

            $car = Car::create($carData);

            // Attach features
            if ($request->filled("features")) {
                $car->features()->sync($request->features);
            }

            // Handle custom fields
            $this->storeCustomFieldValues($car, $request);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "message" => "Car created successfully",
                        "car" => $car->load([
                            "brand",
                            "model",
                            "category",
                            "user",
                            "features",
                        ]),
                    ],
                    201,
                );
            }
            flash(translate("Car created successfully"))->success();
            return redirect()->route("admin.cars.index");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to create car"],
                    500,
                );
            }

            return back()->with("error", "Failed to create car")->withInput();
        }
    }

    /**
     * Display the specified car.
     */
    public function show(Car $car): View|JsonResponse
    {
        $car->load([
            "brand",
            "model",
            "category",
            "user",
            "features",
            "features.section",
            "inspections",
            "customFieldValues.customField",
            "customFieldValues.customField.options",
        ]);
        $feature_sections = $car->features->groupBy(function($feature){
            return $feature->section->name ?? translate('Genearal');
        });

        // Get related cars
        $relatedCars = Car::published()
            ->where("id", "!=", $car->id)
            ->where(function ($query) use ($car) {
                $query
                    ->where("brand_id", $car->brand_id)
                    ->orWhere("model_id", $car->model_id)
                    ->orWhere("category_id", $car->category_id);
            })
            ->limit(6)
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                "car" => $car,
                "related_cars" => $relatedCars,
            ]);
        }

        return view("backend.cars.cars.show", compact("car", "relatedCars", "feature_sections"));
    }

    /**
     * Show the form for editing the specified car.
     */
    public function edit(Car $car): View
    {
        $this->authorize("update", $car);

        $brands = CarBrand::active()->get();
        $categories = CarCategory::active()->parents()->get();
        $features = CarFeature::with('section')->active()->get()->groupBy(function($feature){
            return $feature->section->name ?? translate('Genearal');
        });
        $customFields = CarCustomField::ordered()->get();
        $countries = Country::isEnabled()->get();
        $colors = CarColor::active()->get();

        $car->load(["features", "customFieldValues.customField"]);

        return view(
            "backend.cars.cars.edit",
            compact(
                "car",
                "brands",
                "categories",
                "features",
                "customFields",
                "countries",
                "colors",
            ),
        );
    }

    /**
     * Update the specified car in storage.
     */
    public function update(
        CarRequest $request,
        Car $car,
    ): RedirectResponse|JsonResponse {
        $this->authorize("update", $car);

        try {
            DB::beginTransaction();

            // Store old values for event dispatching
            $oldModerationStatus = $car->moderation_status->getValue();
            $oldCarStatus = $car->car_status->getValue();

            $carData = $request->only([
                "vin",
                "description",
                "brand_id",
                "model_id",
                "category_id",
                "color_id",
                "condition",
                "milage",
                "manufacture_year",
                "transmission",
                "fuel_type",
                "location",
                "price",
                "country_id",
                "state_id",
                "city_id",
                "moderation_status",
                "car_status",
                "main_photo",
                "photos",
            ]);

            $car->update($carData);

            // Sync features
            if ($request->has("features")) {
                $car->features()->sync($request->features ?? []);
            }

            // Handle custom fields
            $this->updateCustomFieldValues($car, $request);

            DB::commit();

            // Dispatch events if status changed
            if (isset($carData['moderation_status']) && $oldModerationStatus !== $carData['moderation_status']) {
                event(new CarModerationStatusChanged(
                    $car,
                    $oldModerationStatus,
                    $carData['moderation_status'],
                    $request->input('moderation_notes')
                ));
            }

            if (isset($carData['car_status']) && $oldCarStatus !== $carData['car_status']) {
                event(new CarStatusChanged(
                    $car,
                    $oldCarStatus,
                    $carData['car_status'],
                    $request->input('status_reason')
                ));
            }

            if ($request->wantsJson()) {
                return response()->json([
                    "message" => "Car updated successfully",
                    "car" => $car->load([
                        "brand",
                        "model",
                        "category",
                        "user",
                        "features",
                    ]),
                ]);
            }
            flash(translate("Car updated successfully"))->success();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to update car"],
                    500,
                );
            }
            flash($e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Remove the specified car from storage.
     */
    public function destroy(Car $car): RedirectResponse|JsonResponse
    {
        if (!$car->canBeDeleted()) {
            if (request()->wantsJson()) {
                return response()->json([
                    "success" => false,
                    "message" => "Can not delete car!",
                ]);
            }
            flash(translate("Can not delete car!"))->error();
            return redirect()->route("admin.cars.index");
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

            // Dispatch event to notify seller of deletion
            event(new CarDeleted(
                $carData,
                request('deletion_reason'),
                Auth::user()->name ?? 'Admin'
            ));

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Car deleted successfully",
                ]);
            }
            flash(translate("Car deleted successfully"))->success();
            return redirect()
                ->route("cars.index")
                ->with("success", "Car deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to delete car"],
                    500,
                );
            }

            return back()->with("error", "Failed to delete car");
        }
    }

    /**
     * Get models by brand.
     */
    public function getModelsByBrand(Request $request, $brandId): JsonResponse
    {
        $models = CarModel::byBrand($brandId)->active()->get();

        return response()->json(["models" => $models]);
    }

    /**
     * Toggle car moderation status.
     */
    public function toggleStatus(Car $car): JsonResponse
    {
        $this->authorize("update", $car);

        $oldStatus = $car->moderation_status->getValue();
        $newStatus = $oldStatus === CarModerationStatusEnum::PUBLISHED
            ? CarModerationStatusEnum::PENDING
            : CarModerationStatusEnum::PUBLISHED;

        $car->update(["moderation_status" => $newStatus]);

        // Dispatch event to notify seller
        event(new CarModerationStatusChanged(
            $car,
            $oldStatus,
            $newStatus,
            'Status toggled by admin'
        ));

        return response()->json([
            "success" => true,
            "message" => "Car moderation status updated successfully",
            "moderation_status" => $newStatus,
        ]);
    }

    /**
     * Update featured and todays deals
    */

    public function updateFeaturedAndTodaysDeal(Request $request, Car $car): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:featured,todays_deal',
            'status' => 'required|boolean',
        ]);
        $field = $request->get('type');
        $status = $request->get('status');

        if($validator->fails()){
            return response()->json([
                "success" => false,
                "message" => "Invalid request data",
            ], 400);
        }



        $car->update([$field => $status]);

        return response()->json([
            "success" => true,
            "message" => ucfirst(str_replace('_', ' ', $field)) . " updated successfully",
            $field => $status,
        ]);
     }

    /**
     * Get user's cars.
     */
    public function myCars(Request $request): View|JsonResponse
    {
        $query = Car::where("user_id", Auth::id())->with([
            "brand",
            "model",
            "category",
            "features",
        ]);

        // Apply filters
        if ($request->filled("moderation_status")) {
            $query->where("moderation_status", $request->moderation_status);
        }

        if ($request->filled("car_status")) {
            $query->where("car_status", $request->car_status);
        }

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "LIKE", "%{$search}%")->orWhere(
                    "description",
                    "LIKE",
                    "%{$search}%",
                );
            });
        }

        // Sorting
        $sortBy = $request->get("sort_by", "created_at");
        $sortOrder = $request->get("sort_order", "desc");
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get("per_page", 15);
        $cars = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(["cars" => $cars]);
        }

        return view("cars.my-cars", compact("cars"));
    }

    /**
     * Store custom field values for a car.
     */
    private function storeCustomFieldValues(Car $car, Request $request): void
    {
        $customFields = CarCustomField::all();

        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";

            if ($request->has($fieldKey)) {
                $value = $request->get($fieldKey);

                // Handle checkbox fields (multiple values)
                if (
                    $field->type === CarCustomField::TYPE_CHECKBOX &&
                    is_array($value)
                ) {
                    $value = json_encode($value);
                }

                if (!empty($value) || $field->required) {
                    CarCustomFieldValue::create([
                        "car_id" => $car->id,
                        "custom_field_id" => $field->id,
                        "value" => $value,
                    ]);
                }
            }
        }
    }

    /**
     * Update custom field values for a car.
     */
    private function updateCustomFieldValues(Car $car, Request $request): void
    {
        $customFields = CarCustomField::all();

        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";

            if ($request->filled($fieldKey)) {
                $value = $request->get($fieldKey);

                // Handle checkbox fields (multiple values)
                if (
                    $field->type === CarCustomField::TYPE_CHECKBOX &&
                    is_array($value)
                ) {
                    $value = json_encode($value);
                }

                CarCustomFieldValue::updateOrCreate(
                    [
                        "car_id" => $car->id,
                        "custom_field_id" => $field->id,
                    ],
                    [
                        "value" => $value,
                    ],
                );
            } else {
                // Remove value if not provided and not required
                if (!$field->required) {
                    CarCustomFieldValue::where("car_id", $car->id)
                        ->where("custom_field_id", $field->id)
                        ->delete();
                }
            }
        }
    }

    /**
     * Update Status for many cars
     */

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'numeric',
            'moderation_status' => 'required|in:'.implode(',',CarModerationStatusEnum::values())
        ]);

        try{
            DB::beginTransaction();

            // Get cars before update to dispatch events
            $cars = Car::whereIn('id', $request->ids)->get();

            Car::whereIn('id', $request->ids)->update([
                'moderation_status' => $request->moderation_status
            ]);

            DB::commit();

            // Dispatch events for each car
            foreach ($cars as $car) {
                $oldStatus = $car->moderation_status->getValue();
                if ($oldStatus !== $request->moderation_status) {
                    // Refresh car to get updated status
                    $car->refresh();
                    event(new CarModerationStatusChanged(
                        $car,
                        $oldStatus,
                        $request->moderation_status,
                        $request->input('notes', 'Bulk status update')
                    ));
                }
            }

            return response()->json([
                'success'=> true,
                'message' => translate('Cars Updated Successfully')
            ]);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong')
            ]);
        }
    }

    /**
     * Delete Many cars
     */

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'numeric',
        ]);

        $cars = Car::whereIn('id', $request->ids)->get();

        $error_count = 0;

        foreach($cars as $car){
            if(!$car->canBeDeleted()){
                $error_count++;
                continue;
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

            // Dispatch event to notify seller of deletion
            event(new CarDeleted(
                $carData,
                $request->input('deletion_reason', 'Bulk deletion'),
                Auth::user()->name ?? 'Admin'
            ));

        } catch (\Exception $e) {
            DB::rollBack();
            $error_count++;

        }
    }
    if($error_count > 0){
         return response()->json([
            'success'=> true,
            'message' => $error_count .' ' . translate('Cars can not be deleted')
        ]);
    }
    return response()->json([
            'success'=> true,
            'message' => translate('Cars deleted successfully')
        ]);

    }
    public function export(Request $request){
         return $this->handleBulkExport(
            $request,
            CarsExport::class,
            'cars_export'
        );
    }
}
