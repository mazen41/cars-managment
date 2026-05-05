<?php

namespace App\Http\Controllers\Api\V2;
use App\Models\CarBrand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class  CarBrandController extends Controller
{
    /**
     * Get All car brands
     */

    public function index(Request $request): JsonResponse | ResourceCollection{
        $query = CarBrand::where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('car_category_id', $categoryId);
            });
        }

        $brands = $query->orderBy('name', 'asc')
        ->get(['id', 'name', 'logo']);
        $brands = $brands->map(function($brand) {
            return [
                'id'    => (int) $brand->id,
                'name'  => $brand->name,
                'logo'  => uploaded_asset($brand->logo),
            ];
        });
        return response()->json(['brands' => $brands]);
    }

     /**
     * Get popular brands.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $brands = CarBrand::withCount('cars')
            ->where('status', 'active')
            ->orderBy('cars_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['brands' => $brands]);
    }

}
