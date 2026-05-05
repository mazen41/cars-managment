<?php
namespace App\Http\Controllers\Api\V2;

use App\Models\CarCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CarCategoryController extends Controller
{
    /**
     * Get Car Category
     */

    public function index(Request $request): JsonResponse
    {
        $query = CarCategory::active();

        if ($request->filled('parent_only')) {
            $query->parents();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $categories = $query->ordered()->get();

        return response()->json(['categories' => $categories]);
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
     * Get category tree structure.
     */
    public function tree(Request $request): JsonResponse
    {
        $categories = CarCategory::with(['children' => function ($query) {
            $query->active()->ordered();
        }])
        ->parents()
        ->active()
        ->ordered()
        ->get();

        return response()->json(['categories' => $categories]);
    }
}
