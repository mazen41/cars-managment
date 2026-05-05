<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\CarCustomField;
use Illuminate\Http\JsonResponse;
class CarCustomFieldController extends Controller
{
     /**
     * Get custom fields.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CarCustomField::with(['options'])->ordered();

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('required')) {
            if ($request->required === '1') {
                $query->required();
            } else {
                $query->optional();
            }
        }

        $customFields = $query->get();

        return response()->json(['custom_fields' => $customFields]);
    }
}
