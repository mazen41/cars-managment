<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use App\Models\Car;
use App\Models\CarInspection;
use App\Models\CarInspectionFieldValue;
use App\Models\CarInspectionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;

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

        $validated = $request->validate([
            'car.vin' => 'required|min:17|max:17|unique:cars,vin',
            'car.plate_number' => 'nullable|string|max:255',
            'car.description' => 'required|string|min:10',
            'car.brand_id' => 'required|exists:car_brands,id',
            'car.model_id' => 'required|exists:car_models,id',
            'car.category_id' => 'nullable|exists:car_categories,id',
            'car.color_id' => 'required|exists:car_colors,id',
            'car.condition' => 'required|in:new,used',
            'car.milage' => 'required|numeric|min:0|max:999999.99',
            'car.manufacture_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'car.transmission' => 'required|string|max:255',
            'car.fuel_type' => 'required|string|max:255',
            'car.location' => 'required|string|max:255',
            'car.price' => 'nullable|numeric|min:0|max:99999999.99',
            'car.country_id' => 'required|exists:countries,id',
            'car.state_id' => 'required|exists:states,id',
            'car.city_id' => 'nullable|exists:cities,id',
            'car.main_photo' => 'required|integer|min:0',
            'car.photos' => 'nullable',
            'car.features' => 'nullable|array',
            'car.features.*' => 'exists:car_features,id',
            'car.custom_fields' => 'nullable|array',
            'car.custom_fields.*.field_id' => 'required|integer|exists:car_custom_fields,id',
            'car.custom_fields.*.value' => 'nullable',
            'inspection_type_id' => 'nullable|integer|exists:car_inspection_types,id',
            'field_values' => 'required|array',
            'field_values.*.field_id' => 'required|integer|exists:car_inspection_fields,id',
            'field_values.*.value' => 'nullable',
            'field_values.*.score' => 'nullable|numeric|min:0|max:100',
            'field_values.*.notes' => 'nullable|string|max:1000',
            'field_values.*.is_flagged' => 'sometimes|boolean',
            'field_values.*.flag_reason' => 'nullable|string|max:500',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'overall_condition' => ['nullable', 'string', Rule::in(array_keys(CarInspection::CONDITIONS))],
            'inspector_notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
            'photo_front'          => 'nullable|image|max:5120',
            'photo_back'           => 'nullable|image|max:5120',
            'photo_left'           => 'nullable|image|max:5120',
            'photo_right'          => 'nullable|image|max:5120',
            'photo_interior_front' => 'nullable|image|max:5120',
            'photo_interior_back'  => 'nullable|image|max:5120',
            'photo_engine'         => 'nullable|image|max:5120',
            'photo_trunk'          => 'nullable|image|max:5120',
            'photo_odometer'       => 'nullable|image|max:5120',
            'photo_dashboard'      => 'nullable|image|max:5120',
            'photo_vin_plate'      => 'nullable|image|max:5120',
            'photo_tires'          => 'nullable|image|max:5120',
            'photo_undercarriage'  => 'nullable|image|max:5120',
        ]);

        try {
            $manualExamination = DB::transaction(function () use ($request, $validated, $inspector) {
                $inspectionTypeId = $validated['inspection_type_id'] ?? $this->getDefaultInspectionTypeId();

                if (!$inspectionTypeId) {
                    throw new \Exception('No active inspection type found');
                }

                $carData = $validated['car'];
                $carData['user_id'] = $request->user()->id;
                $carData['moderation_status'] = CarModerationStatusEnum::PENDING;
                $carData['car_status'] = CarStatusEnum::AVAILABLE;

                // Create the Car
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

                if (!empty($carData['features'])) {
                    $car->features()->sync($carData['features']);
                }

                $inspectionData = [
                    'car_id' => $car->id,
                    'inspection_type_id' => $inspectionTypeId,
                    'inspector_id' => $inspector->id,
                    'requested_by' => $request->user()->id,
                    'status' => CarInspection::STATUS_COMPLETED,
                    'is_manual' => true,
                    'delivered_to_inspector' => true,
                    'started_at' => now(),
                    'completed_at' => now(),
                    'total_score' => $validated['total_score'] ?? null,
                    'overall_condition' => $validated['overall_condition'] ?? null,
                    'inspector_notes' => $validated['inspector_notes'] ?? null,
                    'recommendations' => $validated['recommendations'] ?? null,
                ];

                $photoFields = [
                    'photo_front', 'photo_back', 'photo_left', 'photo_right',
                    'photo_interior_front', 'photo_interior_back', 'photo_engine',
                    'photo_trunk', 'photo_odometer', 'photo_dashboard',
                    'photo_vin_plate', 'photo_tires', 'photo_undercarriage',
                ];
                foreach ($photoFields as $field) {
                    if ($request->hasFile($field)) {
                        $inspectionData[$field] = $request->file($field)->store('car-inspections/photos', 'public');
                    }
                }

                $inspection = CarInspection::create($inspectionData);

                foreach ($validated['field_values'] as $fieldData) {
                    CarInspectionFieldValue::create([
                        'inspection_id' => $inspection->id,
                        'field_id' => $fieldData['field_id'],
                        'value' => $fieldData['value'] ?? null,
                        'score' => $fieldData['score'] ?? null,
                        'notes' => $fieldData['notes'] ?? null,
                        'is_flagged' => $fieldData['is_flagged'] ?? false,
                        'flag_reason' => $fieldData['flag_reason'] ?? null,
                    ]);
                }

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

    public function uploadVehiclePhotos(Request $request, int $manualExaminationId): JsonResponse
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

        $manualExamination = CarInspection::where('inspector_id', $inspector->id)
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

        $request->validate([
            'photos'   => 'required|array|min:1',
            'photos.*' => 'required|image|max:5120',
        ]);

        $photoFields = [
            'photo_front', 'photo_back', 'photo_left', 'photo_right',
            'photo_interior_front', 'photo_interior_back', 'photo_engine',
            'photo_trunk', 'photo_odometer', 'photo_dashboard',
            'photo_vin_plate', 'photo_tires', 'photo_undercarriage',
        ];

        $updates = [];
        foreach ($request->file('photos') as $index => $file) {
            $field = $photoFields[$index] ?? null;
            if (!$field) break;
            $updates[$field] = $file->store('car-inspections/photos', 'public');
        }

        if (!empty($updates)) {
            $manualExamination->update($updates);
        }

        return response()->json([
            'message' => 'Vehicle photos uploaded successfully',
            'data'    => $updates,
        ], 200);
    }

    public function uploadSectionPhotos(Request $request, int $manualExaminationId): JsonResponse
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

        $manualExamination = CarInspection::with('inspectionType.sections')
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

        $validated = $request->validate([
            'section_id' => 'required|integer',
            'photos' => 'required|array|min:1|max:15',
            'photos.*' => 'required|image|max:5120',
        ]);

        $allowedSectionIds = $manualExamination->inspectionType?->sections?->pluck('id')->all() ?? [];

        if (!in_array((int) $validated['section_id'], array_map('intval', $allowedSectionIds), true)) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid section for this inspection type',
                    'code' => 'INVALID_SECTION_ID',
                ],
            ], 422);
        }

        $metadata = $manualExamination->metadata ?? [];
        $metadata['section_photos'] ??= [];

        $sectionKey = (string) $validated['section_id'];
        $metadata['section_photos'][$sectionKey] ??= [];

        $stored = [];

        foreach ($request->file('photos') as $file) {
            $path = $file->store('car-inspections/section-photos', 'public');
            $stored[] = ['path' => $path];
            $metadata['section_photos'][$sectionKey][] = ['path' => $path];
        }

        $manualExamination->metadata = $metadata;
        $manualExamination->save();

        return response()->json([
            'message' => 'Section photos uploaded successfully',
            'data' => [
                'section_id' => (int) $validated['section_id'],
                'uploaded' => $stored,
            ],
        ], 200);
    }

    public function downloadPdf(Request $request, int $manualExaminationId)
{
    $inspector = $request->user()->carInspector;

    if (!$inspector) {
        return response()->json([
            'error' => [
                'message' => 'Inspector profile not found',
                'code'    => 'INSPECTOR_NOT_FOUND',
            ]
        ], 404);
    }

    $manualExamination = CarInspection::where('inspector_id', $inspector->id)
        ->where('is_manual', true)
        ->find($manualExaminationId);

    if (!$manualExamination) {
        return response()->json([
            'error' => [
                'message' => 'Manual examination not found',
                'code'    => 'MANUAL_EXAMINATION_NOT_FOUND',
            ]
        ], 404);
    }

    $manualExamination->load([
        'car.brand',
        'car.model',
        'car.category',
        'car.color',
        'inspectionType.sections.fields',
        'inspector.user',
        'requester',
        'fieldValues.field.section',
    ]);

    $sectionData = [];
    if ($manualExamination->inspectionType) {
        foreach ($manualExamination->inspectionType->sections as $section) {
            $sectionData[$section->id] = [
                'section'    => $section,
                'fields'     => [],
                'completion' => $manualExamination->getSectionCompletion($section->id),
            ];
            foreach ($section->fields as $field) {
                $sectionData[$section->id]['fields'][] = [
                    'field' => $field,
                    'value' => $manualExamination->fieldValues->where('field_id', $field->id)->first(),
                ];
            }
        }
    }

    $options = get_pdf_options();
    $pdf = PDF::loadView('backend.cars.inspections.pdf-report', [
        'carInspection'  => $manualExamination,
        'sectionData'    => $sectionData,
        'font_family'    => $options['font_family'],
        'direction'      => $options['direction'],
        'text_align'     => $options['text_align'],
        'not_text_align' => $options['not_text_align'],
    ]);

    $filename = 'manual-examination-report-' . $manualExamination->inspection_number . '.pdf';

    // ✅ streamDownload keeps the response inside Laravel's pipeline
    // so CORS middleware can attach Access-Control-Allow-Origin correctly.
    // ❌ Never use $pdf->download() or $pdf->stream() — they call exit()
    //    which kills middleware execution before headers are sent.
    return response()->streamDownload(
        function () use ($pdf) {
            echo $pdf->output();
        },
        $filename,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]
    );
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
        $data['metadata'] = $inspection->metadata ?? [];
        $data['sections'] = $inspection->inspectionType?->sections?->map(function ($section) use ($inspection) {
            $sectionPhotosRaw = (($inspection->metadata ?? [])['section_photos'] ?? [])[(string) $section->id] ?? [];

            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'order' => $section->order,
                'section_photos' => collect($sectionPhotosRaw)
                    ->map(function ($item) {
                        $path = $item['path'] ?? null;
                        if (!$path) {
                            return null;
                        }

                        return [
                            'path' => $path,
                            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all(),
                'fields' => $section->fields->map(function ($field) use ($inspection) {
                    $fieldValue = $inspection->fieldValues->firstWhere('field_id', $field->id);

                    return [
                        'id' => $field->id,
                        'name' => $field->name,
                        'description' => $field->description,
                        'type' => $field->field_type,
                        'is_required' => $field->is_required,
                        'options' => $field->field_options,
                        'order' => $field->sort_order,
                        'value' => $fieldValue?->value,
                        'raw_value' => $fieldValue?->value,
                        'score' => $fieldValue?->score,
                        'notes' => $fieldValue?->notes,
                        'is_flagged' => $fieldValue?->is_flagged ?? false,
                        'flag_reason' => $fieldValue?->flag_reason,
                        'photos' => $fieldValue?->file_attachments ?? [],
                    ];
                })->values(),
            ];
        })->values() ?? [];

        return $data;
    }
}