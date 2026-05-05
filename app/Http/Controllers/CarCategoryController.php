<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarCategory;
use App\Models\CarCategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarCategoryController extends Controller
{
    /**
     * Display a listing of the car categories.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarCategory::with(['parent', 'children'])->withCount(['cars', 'children']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('parent_id')) {
            if ($request->parent_id === '0') {
                $query->parents();
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

        // Get parent categories for filter dropdown
        $parentCategories = CarCategory::parents()->active()->ordered()->get();

        if ($request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'parent_categories' => $parentCategories
            ]);
        }

        return view('backend.cars.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Show the form for creating a new car category.
     */
    public function create(): View
    {
        $parentCategories = CarCategory::parents()->active()->ordered()->get();
        $availableLanguages = config('app.available_languages', ['en', 'ar']);
         $categories = CarCategory::with(['children' => function ($query) {
            $query->active()->ordered();
        }])
            ->parents()
            ->active()
            ->ordered()
            ->get();
            $brands = CarBrand::active()->get();
        return view('backend.cars.categories.create', compact('parentCategories', 'availableLanguages', 'categories', 'brands'));
    }

    /**
     * Store a newly created car category in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
            'status' => 'required|in:active,inactive',
            'order' => 'required|integer|min:0',
            'parent_id' => 'sometimes|nullable|exists:car_categories,id',
            'is_default' => 'boolean',
            'image' => 'nullable|nullable|exists:uploads,id',
        ]);

        // Custom validation to prevent circular parent-child relationships
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('parent_id') && $request->parent_id != 0) {
                $parent = CarCategory::find($request->parent_id);
                if ($parent && $parent->parent_id != 0) {
                    $validator->errors()->add('parent_id', 'Cannot create a subcategory under another subcategory.');
                }
            }
        });

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            flash($validator->errors()->first())->error();
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $categoryData = $request->only([
                'name', 'description', 'status', 'order', 'parent_id', 'is_default'
            ]);


            // Set parent_id to 0 if not provided
            $categoryData['parent_id'] = $categoryData['parent_id'] ?? 0;

            // Ensure only one default category
            if ($request->is_default) {
                CarCategory::where('is_default', true)->update(['is_default' => false]);
            }

            $category = CarCategory::create($categoryData);
            $category->brands()->sync($request->brand_ids ?? []);
            // Handle translations
            if ($request->filled('translations')) {
                foreach ($request->translations as $translation) {
                    CarCategoryTranslation::create([
                        'car_category_id' => $category->id,
                        'lang' => $translation['lang'],
                        'name' => $translation['name'],
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car category created successfully',
                    'category' => $category->load(['parent', 'translations'])
                ], 201);
            }
            flash('Car category created successfully')->success();
            return redirect()->route('admin.car-categories.index')
                ->with('success');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car category'], 500);
            }
            flash(translate($e->getMessage()))->error();
            return back()->withInput();
        }
    }

    /**
     * Display the specified car category.
     */
    public function show(CarCategory $carCategory)
    {
       //
    }

    /**
     * Show the form for editing the specified car category.
     */
    public function edit(CarCategory $carCategory): View
    {
        $carCategory->load('translations', 'brands');
        $parentCategories = CarCategory::parents()
            ->active()
            ->where('id', '!=', $carCategory->id)
            ->ordered()
            ->get();
        $availableLanguages = config('app.available_languages', ['en', 'ar']);
        $categories = CarCategory::with(['children' => function ($query) {
            $query->active()->ordered();
        }])
            ->parents()
            ->active()
            ->ordered()
            ->get();
            $brands = CarBrand::active()->get();
        return view('backend.cars.categories.edit', compact('carCategory', 'parentCategories', 'availableLanguages', 'categories', 'brands'));
    }

    /**
     * Update the specified car category in storage.
     */
    public function update(Request $request, CarCategory $carCategory): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
            'status' => 'required|in:active,inactive',
            'order' => 'required|integer|min:0',
            'parent_id' => 'nullable',
            'is_default' => 'boolean',
            'image' => 'integer|nullable|exists:uploads,id',
        ]);

        // Custom validation to prevent circular parent-child relationships
        $validator->after(function ($validator) use ($request, $carCategory) {
            if ($request->filled('parent_id') && $request->parent_id != 0) {
                // Cannot set parent to itself
                if ($request->parent_id == $carCategory->id) {
                    $validator->errors()->add('parent_id', 'Category cannot be its own parent.');
                    return;
                }

                // Cannot set parent to one of its children
                $descendants = $carCategory->getAllDescendants();
                if ($descendants->contains('id', $request->parent_id)) {
                    $validator->errors()->add('parent_id', 'Cannot set a child category as parent.');
                    return;
                }

                // Cannot create subcategory under another subcategory
                $parent = CarCategory::find($request->parent_id);
                if ($parent && $parent->parent_id != 0) {
                    $validator->errors()->add('parent_id', 'Cannot create a subcategory under another subcategory.');
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

            $categoryData = $request->only([
                'name', 'description', 'status', 'order', 'parent_id', 'is_default'
            ]);

            // Set parent_id to 0 if not provided
            $categoryData['parent_id'] = $categoryData['parent_id'] ?? 0;

            // Ensure only one default category
            if ($request->is_default) {
                CarCategory::where('is_default', true)
                    ->where('id', '!=', $carCategory->id)
                    ->update(['is_default' => false]);
            }
            //exclude name if lang is not default
            if($request->lang && $request->lang != app()->getLocale()){
                unset($categoryData['name']);
            }

            $carCategory->update($categoryData);
            $carCategory->brands()->sync($request->brand_ids ?? []);
            $carCategory->translate(
            ['lang' => $request->lang ?? app()->getLocale()],
                ['name' => $request->name]
            );

            // Handle translations
            if ($request->has('translations')) {
                // Delete existing translations
                $carCategory->translations()->delete();

                // Create new translations
                if ($request->filled('translations')) {
                    foreach ($request->translations as $translation) {
                        CarCategoryTranslation::create([
                            'car_category_id' => $carCategory->id,
                            'lang' => $translation['lang'],
                            'name' => $translation['name'],
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car category updated successfully',
                    'category' => $carCategory->load(['parent', 'translations'])
                ]);
            }
            flash('Car category updated successfully')->success();
            return redirect()->route('admin.car-categories.index');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car category'], 500);
            }
            flash(translate($e->getMessage()))->error();
            return back()->with('error', translate($e->getMessage()))->withInput();
        }
    }

    /**
     * Remove the specified car category from storage.
     */
    public function destroy(CarCategory $carCategory): RedirectResponse|JsonResponse
    {
        // Check if category can be deleted
        if (!$carCategory->canBeDeleted()) {
            $message = 'Cannot delete category that has cars, subcategories, or is set as default.';

            if (request()->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }
            flash($message)->error();
            return back()->with('error', $message);
        }

        try {
            DB::beginTransaction();


            // Delete translations
            $carCategory->translations()->delete();

            // Delete the category
            $carCategory->delete();
            // detach brands
            $carCategory->brands()->detach();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car category deleted successfully']);
            }
            flash('Car category deleted successfully')->success();
            return redirect()->route('admin.car-categories.index')
                ->with('success', 'Car category deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car category'], 500);
            }
            flash('Failed to delete car category')->error();
            return back()->with('error', 'Failed to delete car category');
        }
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $statusMap = [
            'active' => 'active',
            'inactive' => 'inactive'
        ];

        $newStatus = $statusMap[$request->status] ?? 'active';
        $carCategory  = CarCategory::find($request->id);
        $carCategory->update(['status' => $newStatus]);

        return response()->json([
            'success'   => true,
            'message' => 'Category status updated successfully',
            'status' => $newStatus
        ]);
    }

    /**
     * Get popular categories.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $categories = CarCategory::withCount('cars')
            ->active()
            ->orderBy('cars_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['categories' => $categories]);
    }

    /**
     * Bulk update categories status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:car_categories,id',
            'status' => 'required|in:published,draft,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            CarCategory::whereIn('id', $request->category_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Categories status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update categories status'], 500);
        }
    }

    /**
     * Update categories order.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:car_categories,id',
            'categories.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->categories as $categoryData) {
                CarCategory::where('id', $categoryData['id'])
                    ->update(['order' => $categoryData['order']]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Categories order updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update categories order'], 500);
        }
    }

    /**
     * Get category statistics.
     */
    public function statistics(CarCategory $carCategory): JsonResponse
    {
        $stats = [
            'total_cars' => $carCategory->cars()->count(),
            'published_cars' => $carCategory->cars()->published()->count(),
            'draft_cars' => $carCategory->cars()->where('status', 'draft')->count(),
            'new_cars' => $carCategory->cars()->byCondition('new')->count(),
            'used_cars' => $carCategory->cars()->byCondition('used')->count(),
            'average_price' => $carCategory->cars()->published()->avg('price'),
            'subcategories_count' => $carCategory->children()->count(),
            'level' => $carCategory->level,
            'parent_info' => $carCategory->parent,
            'recent_cars' => $carCategory->cars()
                ->published()
                ->with(['brand', 'model'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Get subcategories by parent category.
     */
    public function getSubcategories(Request $request, $parentId): JsonResponse
    {
        $subcategories = CarCategory::where('parent_id', $parentId)
            ->active()
            ->ordered()
            ->get();

        return response()->json(['subcategories' => $subcategories]);
    }

    /**
     * Get categories for dropdowns.
     */
    public function dropdown(Request $request): JsonResponse
    {
        $categories = CarCategory::active()->ordered()->get();

        return response()->json(['categories' => $categories]);
    }
}
