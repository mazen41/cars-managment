<?php

namespace App\Http\Controllers;

use App\Models\CarFeatureSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarFeatureSectionController extends Controller
{
    /**
     * Display a listing of the car feature sections.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarFeatureSection::withCount(['features']);

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
        $sections = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(['sections' => $sections]);
        }

        return view('backend.cars.features.section.index', compact('sections'));
    }

    /**
     * Show the form for creating a new car feature section.
     */
    public function create(): View
    {
        return view('backend.cars.features.section.create');
    }

    /**
     * Store a newly created car feature section in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:car_feature_sections,name',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $sectionData = $request->only(['name']);
            $section = CarFeatureSection::create($sectionData);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car feature section created successfully',
                    'section' => $section
                ], 201);
            }

            flash()->success('Car feature section created successfully');
            return redirect()->route('admin.car-features.section.index');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car feature section'], 500);
            }

            flash()->error('Failed to create car feature section');
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified car feature section.
     */
    public function edit(CarFeatureSection $carFeatureSection): View
    {
        return view('backend.cars.features.section.edit', compact('carFeatureSection'));
    }

    /**
     * Update the specified car feature section in storage.
     */
    public function update(Request $request, CarFeatureSection $carFeatureSection): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:car_feature_sections,name,' . $carFeatureSection->id,
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $sectionData = $request->only(['name']);
            $carFeatureSection->update($sectionData);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car feature section updated successfully',
                    'section' => $carFeatureSection
                ]);
            }

            flash()->success('Car feature section updated successfully');
            return redirect()->route('admin.car-features.section.index');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car feature section'], 500);
            }

            flash()->error('Failed to update car feature section');
            return back()->withInput();
        }
    }

    /**
     * Remove the specified car feature section from storage.
     */
    public function delete(CarFeatureSection $carFeatureSection): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if section has features
            if ($carFeatureSection->features()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'error' => 'Cannot delete section that has features assigned to it'
                    ], 422);
                }

                flash()->error('Cannot delete section that has features assigned to it');
                return back();
            }

            $carFeatureSection->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Car feature section deleted successfully'
                ]);
            }

            flash()->success('Car feature section deleted successfully');
            return redirect()->route('admin.car-features.section.index');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car feature section'], 500);
            }

            flash()->error('Failed to delete car feature section');
            return back();
        }
    }

    /**
     * Get statistics for the specified car feature section.
     */
    public function statistics(CarFeatureSection $carFeatureSection): JsonResponse
    {
        $stats = [
            'features_count' => $carFeatureSection->features()->count(),
            'active_features_count' => $carFeatureSection->features()->where('status', 'active')->count(),
            'total_cars_using_features' => $carFeatureSection->features()
                ->withCount('cars')
                ->get()
                ->sum('cars_count'),
        ];

        return response()->json($stats);
    }
}
