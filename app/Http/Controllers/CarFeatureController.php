<?php

namespace App\Http\Controllers;

use App\Models\CarFeature;
use App\Models\CarFeatureSection;
use App\Models\CarFeatureTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CarFeatureController extends Controller
{
    /**
     * Display a listing of the car features.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarFeature::withCount(['cars']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $features = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(['features' => $features]);
        }

        return view('backend.cars.features.index', compact('features'));
    }

    /**
     * Show the form for creating a new car feature.
     */
    public function create(): View
    {
        $availableLanguages = config('app.available_languages', ['en', 'ar']);
         $carFeatureSections = CarFeatureSection::all();
        return view('backend.cars.features.create', compact('availableLanguages', 'carFeatureSections'));
    }

    /**
     * Store a newly created car feature in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:car_features,name',
            'image' => 'nullable|exists:uploads,id',
            'section_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $featureData = $request->only(['name','image', 'section_id']);


            $feature = CarFeature::create($featureData);



            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car feature created successfully',
                    'feature' => $feature->load('translations')
                ], 201);
            }
            flash()->success('Car feature created successfully');
            return redirect()->route('admin.car-features.index')
                ->with('success', 'Car feature created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car feature'], 500);
            }
            flash()->error('Something went wrong');
            return back()->with('error', 'Failed to create car feature')->withInput();
        }
    }

    /**
     * Display the specified car feature.
     */
    public function show(CarFeature $carFeature)
    {
        //
    }

    /**
     * Show the form for editing the specified car feature.
     */
    public function edit(CarFeature $carFeature): View
    {
        $carFeature->load('translations');
        $availableLanguages = config('app.available_languages', ['en', 'ar']);
        $carFeatureSections = CarFeatureSection::all();
        return view('backend.cars.features.edit', compact('carFeature', 'carFeatureSections' ,'availableLanguages'));
    }

    /**
     * Update the specified car feature in storage.
     */
    public function update(Request $request, CarFeature $carFeature): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('car_features')->ignore($carFeature->id) ],
            'image' => 'nullable|exists:uploads,id',
            'section_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $featureData = $request->only(['name', 'image' ,'section_id']);

            if($request->lang && $request->lang != app()->getLocale()){
                unset($featureData['name']);
            }

            $carFeature->update($featureData);
            $carFeature->translate(
                ['lang' => $request->lang?? app()->getLocale()],
                ['name' => $request->name]
            );

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car feature updated successfully',
                    'feature' => $carFeature->load('translations')
                ]);
            }
            flash()->success('Car feature updated!');
            return redirect()->route('admin.car-features.index')
                ->with('success', 'Car feature updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car feature'], 500);
            }
            flash()->error($e->getMessage());
            return back()->with('error', 'Failed to update car feature')->withInput();
        }
    }

    /**
     * Remove the specified car feature from storage.
     */
    public function destroy(CarFeature $carFeature): RedirectResponse|JsonResponse
    {
        // Check if feature has cars
        if ($carFeature->hasCars()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot delete feature that is used by cars'
                ], 422);
            }

            return back()->with('error', 'Cannot delete feature that is used by cars');
        }

        try {
            DB::beginTransaction();


            // Delete translations
            $carFeature->translations()->delete();

            // Delete the feature
            $carFeature->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car feature deleted successfully']);
            }
            flash()->success('Deleted successfully');
            return redirect()->route('admin.car-features.index')
                ->with('success', 'Car feature deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car feature'], 500);
            }
            flash()->error('Somthing went wrong!');
            return back()->with('error', 'Failed to delete car feature');
        }
    }

    /**
     * Bulk delete features.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'feature_ids' => 'required|array',
            'feature_ids.*' => 'exists:car_features,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $features = CarFeature::whereIn('id', $request->feature_ids)->get();

            // Check if any feature has cars
            foreach ($features as $feature) {
                if ($feature->hasCars()) {
                    return response()->json([
                        'error' => "Cannot delete feature '{$feature->name}' that is used by cars"
                    ], 422);
                }
            }

            // Delete features
            foreach ($features as $feature) {
                // Delete image from storage
                if ($feature->image) {
                    Storage::disk('public_uploads')->delete($feature->image);
                }

                // Delete translations
                $feature->translations()->delete();

                // Delete the feature
                $feature->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Features deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete features'], 500);
        }
    }

    /**
     * Get feature statistics.
     */
    public function statistics(CarFeature $carFeature): JsonResponse
    {
        $stats = [
            'total_cars' => $carFeature->cars()->count(),
            'published_cars' => $carFeature->cars()->published()->count(),
            'draft_cars' => $carFeature->cars()->where('status', 'draft')->count(),
            'new_cars' => $carFeature->cars()->byCondition('new')->count(),
            'used_cars' => $carFeature->cars()->byCondition('used')->count(),
            'average_price' => $carFeature->cars()->published()->avg('price'),
            'recent_cars' => $carFeature->cars()
                ->published()
                ->with(['brand', 'model', 'category'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Toggle feature usage for a car.
     */
    public function toggleForCar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'feature_id' => 'required|exists:car_features,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $feature = CarFeature::findOrFail($request->feature_id);
            $car = \App\Models\Car::findOrFail($request->car_id);

            if ($feature->isUsedByCar($request->car_id)) {
                $car->features()->detach($request->feature_id);
                $message = 'Feature removed from car';
                $attached = false;
            } else {
                $car->features()->attach($request->feature_id);
                $message = 'Feature added to car';
                $attached = true;
            }

            return response()->json([
                'message' => $message,
                'attached' => $attached
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle feature'], 500);
        }
    }
}
