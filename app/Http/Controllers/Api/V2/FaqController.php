<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\FaqTypeEnum;
use App\Http\Resources\V2\FaqResource;
use App\Http\Resources\V2\FaqCategoryResource;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\FaqSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class FaqController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Display a listing of published FAQs.
     *
     * @param Request $requestd
     */
    public function index(Request $request)
    {
        try {
            $query = Faq::published()
                ->with(['translations'])
                ->ordered();

            // Filter by type if provided
            if ($request->has('type') && $request->type) {
                $query->byType($request->type);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
            $faqs = $query->paginate($perPage);

            return FaqResource::collection($faqs);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Failed to retrieve FAQs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }


    /**
     * Get FAQ categories with optional FAQ counts.
     *
     * @param Request $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function types(Request $request)
    {
       $types  = FaqTypeEnum::options();
            return response()->json([
                'result' => true,
                'data' => $types
            ], 200);
    }
}
