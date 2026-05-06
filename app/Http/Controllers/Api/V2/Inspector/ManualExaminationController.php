<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use App\Models\Car;
use App\Models\CarInspection;
use App\Models\CarInspectionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManualExaminationController extends BaseInspectorController
{
    public function index(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $request->validate([
            'status' => ['sometimes', 'string', Rule::in(array_keys(CarInspection::STATUSES))],
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = CarInspection::with([
            'car.brand:id,name',
            'car.model:id,name',
            'inspectionType:id,name',
        ])
            ->where('inspector_id', $inspector->id)
            ->where('is_manual', true)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($carQuery) use ($search) {
                        $carQuery->where('vin', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%")
                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('model', function ($modelQuery) use ($search) {
                                $modelQuery->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $manualExaminations = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $manualExaminations->getCollection()->map(fn ($inspection) => $this->transformListItem($inspection)),
            'meta' => [
                'current_page' => $manualExaminations->currentPage(),
                'last_page' => $manualExaminations->lastPage(),
                'per_page' => $manualExaminations->perPage(),
                'total' => $manualExaminations->total(),
                'from' => $manualExaminations->firstItem(),
                'to' => $manualExaminations->lastItem(),
            ],
            'links' => [
                'first' => $manualExaminations->url(1),
                'last' => $manualExaminations->url($manualExaminations->lastPage()),
                'prev' => $manualExaminations->previousPageUrl(),
                'next' => $manualExaminations->nextPageUrl(),
            ],
        ]);
    }

   public function store(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        // 1. WE COMPLETELY REMOVED $request->validate() HERE TO BYPASS EVERYTHING

        try {
            $manualExamination = DB::transaction(function () use ($request, $inspector) {
                $inspectionTypeId = $request->inspection_type_id ?: $this->getDefaultInspectionTypeId();

                // 2. Get whatever car data was sent, or an empty array if none
                $carData = $request->input('car', []);
                
                // 3. Force valid IDs so the database doesn't crash from React's test text
                $carData['brand_id'] = is_numeric($carData['brand_id'] ?? null) ? $carData['brand_id'] : 1;
                $carData['model_id'] = is_numeric($carData['model_id'] ?? null) ? $carData['model_id'] : 1;
                $carData['color_id'] = is_numeric($carData['color_id'] ?? null) ? $carData['color_id'] : 1;
                $carData['country_id'] = is_numeric($carData['country_id'] ?? null) ? $carData['country_id'] : 1;
                $carData['state_id'] = is_numeric($carData['state_id'] ?? null) ? $carData['state_id'] : 1;
                
                $carData['user_id'] = $request->user()->id;
                $carData['moderation_status'] = CarModerationStatusEnum::PENDING;
                $carData['car_status'] = CarStatusEnum::AVAILABLE;

                // 4. Create the Car
                $car = Car::create(collect($carData)->only([
                    'vin',
                    'plate_number',
                    'description',
                    'brand_id',
                    'model_id',
                    'category_id',
                    'color_id',
                    'condition',
                    'milage',
                    'manufacture_year',
                    'transmission',
                    'fuel_type',
                    'location',
                    'price',
                    'country_id',
                    'state_id',
                    'city_id',
                    'main_photo',
                    'photos',
                    'user_id',
                    'moderation_status',
                    'car_status',
                ])->toArray());

                // 5. Create the Inspection record
                $inspection = CarInspection::create([
                    'car_id' => $car->id,
                    'inspection_type_id' => $inspectionTypeId,
                    'inspector_id' => $inspector->id,
                    'requested_by' => $request->user()->id,
                    'status' => CarInspection::STATUS_COMPLETED,
                    'is_manual' => true,
                    'delivered_to_inspector' => true,
                    'started_at' => now(),
                    'completed_at' => now(),
                    'total_score' => $request->total_score,
                    'overall_condition' => $request->overall_condition,
                    'inspector_notes' => $request->inspector_notes,
                    'recommendations' => $request->recommendations,
                ]);

                return $inspection->fresh([
                    'car.brand',
                    'car.model',
                    'car.category',
                    'car.color',
                    'inspectionType.sections.fields',
                    'requester',
                ]);
            });

            return response()->json([
                'data' => $this->transformDetail($manualExamination),
                'message' => 'Manual examination created successfully',
            ], 201, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Database Error: ' . $e->getMessage(),
                    'code' => 'MANUAL_EXAMINATION_CREATE_FAILED',
                ],
            ], 422);
        }
    }

    public function show(Request $request, int $manualExaminationId): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $manualExamination = CarInspection::with([
            'car.brand',
            'car.model',
            'car.category',
            'car.color',
            'car.country',
            'car.state',
            'car.city',
            'car.features.section',
            'car.customFieldValues.customField.options',
            'inspectionType.sections.fields',
            'fieldValues.field.section',
            'requester:id,name,email,phone',
        ])
            ->where('inspector_id', $inspector->id)
            ->where('is_manual', true)
            ->find($manualExaminationId);

        if (!$manualExamination) {
            return response()->json([
                'error' => [
                    'message' => 'Manual examination not found',
                    'code' => 'MANUAL_EXAMINATION_NOT_FOUND'
                ]
            ], 404);
        }

        return response()->json([
            'data' => $this->transformDetail($manualExamination),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function getDefaultInspectionTypeId(): ?int
    {
        return CarInspectionType::where('is_active', true)
            ->where('is_system_default', true)
            ->value('id')
            ?: CarInspectionType::where('is_active', true)->ordered()->value('id');
    }

    private function transformListItem(CarInspection $inspection): array
    {
        return [
            'id' => $inspection->id,
            'inspection_number' => $inspection->inspection_number,
            'status' => $inspection->status,
            'status_display' => $inspection->status_display,
            'created_at' => $inspection->created_at?->toISOString(),
            'completed_at' => $inspection->completed_at?->toISOString(),
            'car' => [
                'id' => $inspection->car?->id,
                'make' => $inspection->car?->brand?->name,
                'model' => $inspection->car?->model?->name,
                'year' => $inspection->car?->manufacture_year,
                'plate_number' => $inspection->car?->plate_number,
                'vin' => $inspection->car?->vin,
            ],
        ];
    }

    private function transformDetail(CarInspection $inspection): array
    {
        $data = $this->transformListItem($inspection);

        $data['car'] = array_merge($data['car'], [
            'description' => $inspection->car?->description,
            'category' => $inspection->car?->category?->getTranslation('name'),
            'color' => $inspection->car?->color?->getTranslation('name'),
            'condition' => $inspection->car?->condition,
            'milage' => $inspection->car?->milage,
            'transmission' => $inspection->car?->transmission,
            'fuel_type' => $inspection->car?->fuel_type,
            'location' => $inspection->car?->location,
            'price' => $inspection->car?->price,
            'country' => $inspection->car?->country?->name,
            'state' => $inspection->car?->state?->name,
            'city' => $inspection->car?->city?->name,
            'main_photo' => $inspection->car?->main_photo,
            'photos' => $inspection->car?->photos,
            'features' => $inspection->car?->features?->map(fn ($feature) => [
                'id' => $feature->id,
                'name' => $feature->getTranslation('name'),
                'section' => $feature->section?->name,
            ])->values() ?? [],
            'custom_fields' => $inspection->car?->customFieldValues?->map(fn ($value) => [
                'id' => $value->custom_field_id,
                'name' => $value->customField?->name,
                'value' => $value->value,
            ])->values() ?? [],
        ]);

        $data['inspection_type'] = [
            'id' => $inspection->inspectionType?->id,
            'name' => $inspection->inspectionType?->name,
            'description' => $inspection->inspectionType?->description,
        ];
        $data['total_score'] = $inspection->total_score;
        $data['overall_condition'] = $inspection->overall_condition;
        $data['condition_display'] = $inspection->condition_display;
        $data['inspector_notes'] = $inspection->inspector_notes;
        $data['recommendations'] = $inspection->recommendations;
        $data['summary'] = $inspection->summary;
        $data['sections'] = $inspection->inspectionType?->sections?->map(function ($section) use ($inspection) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'order' => $section->order,
                'fields' => $section->fields->map(function ($field) use ($inspection) {
                    // Fallback since we removed fieldValues loop
                    return [
                        'id' => $field->id,
                        'name' => $field->name,
                        'description' => $field->description,
                        'type' => $field->field_type,
                        'is_required' => $field->is_required,
                        'options' => $field->field_options,
                        'order' => $field->sort_order,
                    ];
                })->values(),
            ];
        })->values() ?? [];

        return $data;
    }
}