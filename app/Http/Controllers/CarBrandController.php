<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarBrandTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarBrandController extends Controller
{
    /**
     * Display a listing of the car brands.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarBrand::withCount(['models', 'cars']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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
        $brands = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(['brands' => $brands]);
        }

        return view('backend.cars.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new car brand.
     */
    public function create(): View
    {
        $availableLanguages = config('app.available_languages', ['en', 'ar']);

        return view('backend.cars.brands.create', compact('availableLanguages'));
    }

    /**
     * Store a newly created car brand in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:car_brands,name',
            'logo' => 'required|integer|exists:uploads,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $brandData = $request->only(['name', 'status','logo']);

            $brand = CarBrand::create($brandData);


            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car brand created successfully',
                    'brand' => $brand->load('translations')
                ], 201);
            }
            flash()->success('Car brand created successfully');
            return redirect()->route('admin.car-brands.index')
                ->with('success', 'Car brand created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car brand'], 500);
            }
            flash()->error('Failed to create car brand');
            return back()->with('error', 'Failed to create car brand')->withInput();
        }
    }

    /**
     * Display the specified car brand.
     */
    public function show(CarBrand $carBrand)
    {
       //
    }

    /**
     * Show the form for editing the specified car brand.
     */
    public function edit(CarBrand $carBrand): View
    {
        $carBrand->load('translations');
        $availableLanguages = config('app.available_languages', ['en', 'ar']);

        return view('backend.cars.brands.edit', compact('carBrand', 'availableLanguages'));
    }

    /**
     * Update the specified car brand in storage.
     */
    public function update(Request $request, CarBrand $carBrand): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'required|integer|exists:uploads,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $brandData = $request->only(['name', 'status', 'logo']);


            //exclude name if lang is not default
            if($request->lang && $request->lang != app()->getLocale()){
                unset( $brandData['name']);
            }
            $carBrand->update($brandData);
            $carBrand->translate(
            ['lang' => $request->lang ?? app()->getLocale()],
                ['name' => $request->name]
            );

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car brand updated successfully',
                    'brand' => $carBrand->load('translations')
                ]);
            }
            flash()->success('Car brand updated successfully');
            return redirect()->route('admin.car-brands.index')
                ->with('success', 'Car brand updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car brand'], 500);
            }
            flash()->error($e->getMessage());
            return back()->with('error', 'Failed to update car brand')->withInput();
        }
    }

    /**
     * Remove the specified car brand from storage.
     */
    public function destroy(CarBrand $carBrand): RedirectResponse|JsonResponse
    {
        // Check if brand has models or cars
        if ($carBrand->hasModels() || $carBrand->hasCars()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot delete brand that has models or cars'
                ], 422);
            }

            return back()->with('error', 'Cannot delete brand that has models or cars');
        }

        try {
            DB::beginTransaction();

            // Delete translations
            $carBrand->translations()->delete();

            // Delete the brand
            $carBrand->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car brand deleted successfully']);
            }
            flash()->success('Car brand deleted successfully');
            return redirect()->route('car-brands.index')
                ->with('success', 'Car brand deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car brand'], 500);
            }
            flash()->error('Failed to delete car brand');
            return back()->with('error', 'Failed to delete car brand');
        }
    }

    /**
     * Toggle brand status.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $statusMap = [
            'active' => 'active',
            'inactive' => 'inactive'
        ];

        $newStatus = $statusMap[$request->status] ?? 'active';
        $carBrand  = CarBrand::find($request->id);
        $carBrand->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Brand status updated successfully',
            'status' => $newStatus
        ]);
    }


    /**
     * Bulk update brands status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'brand_ids' => 'required|array',
            'brand_ids.*' => 'exists:car_brands,id',
            'status' => 'required|in:active,pending,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            CarBrand::whereIn('id', $request->brand_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Brands status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update brands status'], 500);
        }
    }

    /**
     * Get brand statistics.
     */
    public function statistics(CarBrand $carBrand): JsonResponse
    {
        $stats = [
            'total_models' => $carBrand->models()->count(),
            'total_cars' => $carBrand->cars()->count(),
            'published_cars' => $carBrand->cars()->published()->count(),
            'draft_cars' => $carBrand->cars()->where('status', 'draft')->count(),
            'new_cars' => $carBrand->cars()->byCondition('new')->count(),
            'used_cars' => $carBrand->cars()->byCondition('used')->count(),
            'average_price' => $carBrand->cars()->published()->avg('price'),
            'recent_cars' => $carBrand->cars()
                ->published()
                ->with(['model', 'category'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(['statistics' => $stats]);
    }
}
