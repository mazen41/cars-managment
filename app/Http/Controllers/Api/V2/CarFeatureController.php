<?php

namespace App\Http\Controllers\Api\V2;
use App\Models\CarFeature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class CarFeatureController extends Controller
{

    /**
     * Get Car features
     */

    public function index(Request $request): JsonResponse
    {
        $query = CarFeature::active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $features = $query->ordered()->get();

        return response()->json(['features' => $features]);
    }

    /**
     * Get popular features.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $features = CarFeature::getPopular($limit);

        return response()->json(['features' => $features]);
    }

     /**
     * Search features with autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $limit = $request->get('limit', 10);

        $features = CarFeature::where('name', 'LIKE', "%{$query}%")
            ->active()
            ->ordered()
            ->limit($limit)
            ->get();

        return response()->json(['features' => $features]);
    }

     /**
     * Get features by car.
     */
    public function getByCarId(Request $request, $carId): JsonResponse
    {
        $features = CarFeature::whereHas('cars', function ($query) use ($carId) {
            $query->where('car_id', $carId);
        })->get();

        return response()->json(['features' => $features]);
    }


    /**
     * Get features with their usage count.
     */
    public function withUsageCount(Request $request): JsonResponse
    {
        $features = CarFeature::withCount('cars')
            ->active()
            ->ordered()
            ->get();

        return response()->json(['features' => $features]);
    }

}
