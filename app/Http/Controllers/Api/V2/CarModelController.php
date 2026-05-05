<?php

namespace App\Http\Controllers\Api\V2;
use illuminate\Http\Request;
use App\Models\CarModel;
use Illuminate\Http\JsonResponse;
use Validator;

class CarModelController extends Controller
{

    /**
     * Get Car Models
     */

    public function index(Request $request): JsonResponse{
        $query = CarModel::with('brand')->where('status', 'active');

        if ($request->filled('brand_id')) {
            $query->byBrand($request->brand_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $models = $query->orderBy('name', 'asc')->get();

        return response()->json(['models' => $models]);
    }

     /**
     * Get popular models.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $models = CarModel::withCount('cars')
            ->with('brand')
            ->where('status', 'active')
            ->orderBy('cars_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['models' => $models]);
    }
     /**
     * Get models by multiple brands.
     */
    public function getByBrands(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'brand_ids' => 'required|array',
            'brand_ids.*' => 'exists:car_brands,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $models = CarModel::whereIn('brand_id', $request->brand_ids)
            ->where('status', 'active')
            ->with('brand')
            ->orderBy('name')
            ->get()
            ->groupBy('brand_id');

        return response()->json(['models' => $models]);
    }

     /**
     * Get models by brand
     */
    public function getByBrand(Request $request, $brandId): JsonResponse
    {
        $models = CarModel::byBrand($brandId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Models retrieved successfully",
            'models' => $models
        ]);
    }

}
