<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\CarBrand;
use App\Models\CarModelTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarModelController extends Controller
{
    /**
     * Display a listing of the car models.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarModel::with(['brand'])->withCount(['cars']);

        // Apply filters
        if ($request->filled('brand_id')) {
            $query->byBrand($request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('brand', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy ?? 'name', $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $models = $query->paginate($perPage);

        // Get brands for filter dropdown
        $brands = CarBrand::active()->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'models' => $models,
                'brands' => $brands
            ]);
        }

        return view('backend.cars.models.index', compact('models', 'brands'));
    }

    /**
     * Show the form for creating a new car model.
     */
    public function create(): View
    {
        $brands = CarBrand::active()->orderBy('name')->get();
        $availableLanguages = config('app.available_languages', ['en', 'ar']);

        return view('backend.cars.models.create', compact('brands', 'availableLanguages'));
    }

    /**
     * Store a newly created car model in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:car_brands,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Custom validation to check for duplicate model name within the same brand
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('name') && $request->filled('brand_id')) {
                $exists = CarModel::where('name', $request->name)
                    ->where('brand_id', $request->brand_id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'This model name already exists for the selected brand.');
                }
            }
        });

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $modelData = $request->only(['name', 'brand_id', 'status']);
            $model = CarModel::create($modelData);

            // Handle translations
            if ($request->filled('translations')) {
                foreach ($request->translations as $translation) {
                    CarModelTranslation::create([
                        'car_model_id' => $model->id,
                        'lang' => $translation['lang'],
                        'name' => $translation['name'],
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car model created successfully',
                    'model' => $model->load(['brand', 'translations'])
                ], 201);
            }
            flash()->success('Car model created successfully');
            return redirect()->route('admin.car-models.index')
                ->with('success', 'Car model created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car model'], 500);
            }
            flash()->error('Failed to create car model');
            return back()->with('error', 'Failed to create car model')->withInput();
        }
    }

    /**
     * Display the specified car model.
     */
    public function show(CarModel $carModel)
    {
       //
    }

    /**
     * Show the form for editing the specified car model.
     */
    public function edit(CarModel $carModel): View
    {
        $carModel->load('translations');
        $brands = CarBrand::active()->orderBy('name')->get();
        $availableLanguages = config('app.available_languages', ['en', 'ar']);

        return view('backend.cars.models.edit', compact('carModel', 'brands', 'availableLanguages'));
    }

    /**
     * Update the specified car model in storage.
     */
    public function update(Request $request, CarModel $carModel): RedirectResponse|JsonResponse
    {
          $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:car_brands,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Custom validation to check for duplicate model name within the same brand
        $validator->after(function ($validator) use ($request, $carModel) {
            if ($request->filled('name') && $request->filled('brand_id')) {
                $exists = CarModel::where('name', $request->name)
                    ->where('brand_id', $request->brand_id)
                    ->where('id', '!=', $carModel->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'This model name already exists for the selected brand.');
                }
            }
        });

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $modelData = $request->only(['name', 'brand_id', 'status']);

             if($request->lang && $request->lang != app()->getLocale()){
                unset( $modelData['name']);
            }
            $carModel->update($modelData);

            $carModel->translate(
            ['lang' => $request->lang ?? app()->getLocale()],
                ['name' => $request->name]
            );


            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car model updated successfully',
                    'model' => $carModel->load(['brand', 'translations'])
                ]);
            }
            flash()->success('Car model updated successfully');
            return redirect()->route('admin.car-models.index')
                ->with('success', 'Car model updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car model'], 500);
            }
            flash()->error('Failed to update car model');
            return back()->with('error', 'Failed to update car model')->withInput();
        }
    }

    /**
     * Remove the specified car model from storage.
     */
    public function destroy(CarModel $carModel): RedirectResponse|JsonResponse
    {
        // Check if model has cars
        if ($carModel->hasCars()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot delete model that has cars'
                ], 422);
            }
            flash()->error('Cannot delete model that has cars');
            return back()->with('error', 'Cannot delete model that has cars');
        }

        try {
            DB::beginTransaction();

            // Delete translations
            $carModel->translations()->delete();

            // Delete the model
            $carModel->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car model deleted successfully']);
            }

            return redirect()->route('car-models.index')
                ->with('success', 'Car model deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car model'], 500);
            }
            flash()->error('Failed to delete car model');
            return back()->with('error', 'Failed to delete car model');
        }
    }

    /**
     * Get models by brand for AJAX requests.
     */
    public function getByBrand(Request $request, $brandId): JsonResponse
    {
        $models = CarModel::byBrand($brandId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(['models' => $models]);
    }

    /**
     * Toggle model status.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $statusMap = [
            'active' => 'active',
            'inactive' => 'inactive'
        ];

        $newStatus = $statusMap[$request->status] ?? 'active';
        $carModel  = CarModel::find($request->id);
        $carModel->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Model status updated successfully',
            'status' => $newStatus
        ]);
    }

    /**
     * Bulk update models status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'model_ids' => 'required|array',
            'model_ids.*' => 'exists:car_models,id',
            'status' => 'required|in:active,pending,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            CarModel::whereIn('id', $request->model_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Models status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update models status'], 500);
        }
    }

    /**
     * Get model statistics.
     */
    public function statistics(CarModel $carModel): JsonResponse
    {
        $stats = [
            'total_cars' => $carModel->cars()->count(),
            'published_cars' => $carModel->cars()->published()->count(),
            'draft_cars' => $carModel->cars()->where('status', 'draft')->count(),
            'new_cars' => $carModel->cars()->byCondition('new')->count(),
            'used_cars' => $carModel->cars()->byCondition('used')->count(),
            'average_price' => $carModel->cars()->published()->avg('price'),
            'brand_info' => $carModel->brand,
            'recent_cars' => $carModel->cars()
                ->published()
                ->with(['brand', 'category'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(['statistics' => $stats]);
    }
}
