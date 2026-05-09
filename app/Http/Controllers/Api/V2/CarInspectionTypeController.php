<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CarInspectionTypeCollection;
use App\Models\CarInspector;
use App\Models\CarInspectionType;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CarInspectionTypeController extends Controller
{
    /**
     * Public catalogue of inspection types (`GET /api/v2/cars/inspection-types`).
     *
     * When the request includes a valid inspector JWT (same as inspector API login),
     * results are narrowed to inspection types configured for that center in admin
     * Manual examinations permissions. Unauthenticated requests keep the old
     * behaviour (all active types) for other clients.
     */
    public function index(Request $request): ResourceCollection|JsonResponse
    {
        $query = CarInspectionType::with(['sections.fields'])
            ->active()
            ->ordered();

        $token = $request->bearerToken();
        if (is_string($token) && trim($token) !== '') {
            $user = app(JwtService::class)->getUserFromToken($token);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'error' => 'UNAUTHORIZED',
                ], 401);
            }

            if ($user->user_type !== 'car_inspector') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Car inspector privileges required.',
                    'error' => 'FORBIDDEN',
                ], 403);
            }

            $inspector = $user->carInspector;
            if (!$inspector) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car inspector profile not found',
                    'error' => 'FORBIDDEN',
                ], 403);
            }

            if (!$inspector->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car inspector account is inactive',
                    'error' => 'FORBIDDEN',
                ], 403);
            }

            $query->forInspectorManualAssignments($inspector);
        }

        return new CarInspectionTypeCollection($query->get());
    }

    /**
     * Authenticated inspector listing (same filtering as index with JWT),
     * for clients that prefer a dedicated permission-protected endpoint.
     */
    public function manualExaminationInspectionTypes(Request $request): ResourceCollection
    {
        /** @var CarInspector $inspector */
        $inspector = $request->get('auth_inspector');

        $query = CarInspectionType::with(['sections.fields'])
            ->active()
            ->ordered()
            ->forInspectorManualAssignments($inspector);

        return new CarInspectionTypeCollection($query->get());
    }
}
