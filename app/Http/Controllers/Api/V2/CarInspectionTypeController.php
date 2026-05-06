<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CarInspectionTypeCollection;
use App\Models\CarInspectionType;
use App\Models\CarInspectionSection;
use App\Models\CarInspectionField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CarInspectionTypeController extends Controller
{
    /**
     * Get All inspection types for API
     */

    public function index(): ResourceCollection
    {
        $inspectionTypes = CarInspectionType::with(['sections.fields'])
            ->active()
            ->ordered()
            ->get();
        return new CarInspectionTypeCollection($inspectionTypes);
    }
}


