<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Exceptions\Inspector\InspectionNotFoundException;
use App\Http\Requests\Inspector\FileUploadRequest;
use App\Models\CarInspection;
use App\Models\CarInspectionType;
use App\Services\Inspector\ErrorLoggingService;
use App\Services\Inspector\FileUploadService;
use App\Services\Inspector\InspectionStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Services\ImageUploadService;

class InspectorInspectionController extends BaseInspectorController
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Get paginated list of inspections assigned to the inspector
     */
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
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'car_type' => 'sometimes|string',
            'inspection_type_id' => 'sometimes|integer|exists:car_inspection_types,id',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:scheduled_at,created_at,status,inspection_number',
            'sort_order' => 'sometimes|string|in:asc,desc'
        ]);

        $query = CarInspection::with([
            'car:id,vin,brand_id,model_id,manufacture_year,fuel_type,transmission',
            'car.brand:id,name',
            'car.model:id,name',
            'car.color:id,name',
            'inspectionType:id,name,price',
            'requester:id,name,email,phone'
        ])
        ->where('inspector_id', $inspector->id)
        ->deliveredToInspector();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
        }

        if ($request->has('inspection_type_id')) {
            $query->where('inspection_type_id', $request->inspection_type_id);
        }

        if ($request->has('car_type')) {
            $query->whereHas('car', function ($carQuery) use ($request) {
                $carQuery->where('fuel_type', 'like', '%' . $request->car_type . '%')
                    ->orWhere('transmission_type', 'like', '%' . $request->car_type . '%');
            });
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($searchQuery) use ($searchTerm) {
                $searchQuery->where('inspection_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('inspector_notes', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('car', function ($carQuery) use ($searchTerm) {
                        $carQuery
                            ->where('vin', "LIKE", "%{$searchTerm}%")
                            ->orWhereHas('model', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', '%' . $searchTerm . '%');
                            })
                            ->OrWhereHas('brand', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    ->orWhereHas('requester', function ($requesterQuery) use ($searchTerm) {
                        $requesterQuery->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'scheduled_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $inspections = $query->paginate($perPage);

        // Transform the data
        $transformedInspections = $inspections->getCollection()->map(function ($inspection) {
            return $this->transformInspection($inspection);
        });

        return response()->json([
            'data' => $transformedInspections,
            'meta' => [
                'current_page' => $inspections->currentPage(),
                'last_page' => $inspections->lastPage(),
                'per_page' => $inspections->perPage(),
                'total' => $inspections->total(),
                'from' => $inspections->firstItem(),
                'to' => $inspections->lastItem()
            ],
            'links' => [
                'first' => $inspections->url(1),
                'last' => $inspections->url($inspections->lastPage()),
                'prev' => $inspections->previousPageUrl(),
                'next' => $inspections->nextPageUrl()
            ]
        ]);
    }

    /**
     * Get detailed information about a specific inspection
     */
    public function show(Request $request, int $inspectionId): JsonResponse
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

        $inspection = CarInspection::with([
            'car:id,vin,brand_id,model_id,manufacture_year,fuel_type,transmission,color_id,milage',
            'car.brand:id,name',
            'car.model:id,name',
            'car.color:id,name',
            'inspectionType:id,name,price,description',
            'inspectionType.sections.fields',
            'requester:id,name,email,phone',
            'fieldValuesWithRelations',
            'payment'
        ])->where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        return response()->json([
            'data' => $this->transformInspectionDetail($inspection)
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Start an inspection
     */
    public function start(Request $request, int $inspectionId): JsonResponse
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

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->can_start) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection cannot be started. Current status: ' . $inspection->status_display,
                    'code' => 'INVALID_STATUS_TRANSITION'
                ]
            ], 422);
        }

        try {
            $inspection->start($inspector->id);

            return response()->json([
                'data' => $this->transformInspection($inspection->fresh()),
                'message' => 'Inspection started successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INSPECTION_START_FAILED'
                ]
            ], 422);
        }
    }

    /**
     * Complete an inspection
     */
    public function complete(Request $request, int $inspectionId): JsonResponse
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
            'total_score' => 'sometimes|numeric|min:0|max:100',
            'overall_condition' => ['sometimes', 'string', Rule::in(array_keys(CarInspection::CONDITIONS))],
            'inspector_notes' => 'sometimes|string|max:2000',
            'recommendations' => 'sometimes|string|max:2000'
        ]);

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->can_complete) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection cannot be completed. Current status: ' . $inspection->status_display,
                    'code' => 'INVALID_STATUS_TRANSITION'
                ]
            ], 422);
        }

        try {
            $inspection->complete(
                $request->get('total_score'),
                $request->get('overall_condition'),
                $request->get('inspector_notes')
            );

            if ($request->has('recommendations')) {
                $inspection->recommendations = $request->get('recommendations');
                $inspection->save();
            }

            return response()->json([
                'data' => $this->transformInspection($inspection->fresh()),
                'message' => 'Inspection completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INSPECTION_COMPLETE_FAILED'
                ]
            ], 422);
        }
    }

    /**
     * Cancel an inspection
     */
    public function cancel(Request $request, int $inspectionId): JsonResponse
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
            'reason' => 'required|string|max:500'
        ]);

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->can_cancel) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection cannot be cancelled. Current status: ' . $inspection->status_display,
                    'code' => 'INVALID_STATUS_TRANSITION'
                ]
            ], 422);
        }

        try {
            $inspection->cancel($request->get('reason'));

            return response()->json([
                'data' => $this->transformInspection($inspection->fresh()),
                'message' => 'Inspection cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INSPECTION_CANCEL_FAILED'
                ]
            ], 422);
        }
    }

    /**
     * Update inspection field values
     */
    public function updateFieldValues(Request $request, int $inspectionId): JsonResponse
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

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->is_editable) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection is not editable in current status: ' . $inspection->status_display,
                    'code' => 'INSPECTION_NOT_EDITABLE'
                ]
            ], 422);
        }

        $request->validate([
            'field_values' => 'required|array',
            'field_values.*.field_id' => 'required|integer|exists:car_inspection_fields,id',
            'field_values.*.value' => 'nullable',
            'field_values.*.score' => 'nullable|numeric|min:0|max:100',
            'field_values.*.notes' => 'nullable|string|max:1000',
            'field_values.*.is_flagged' => 'sometimes|boolean',
            'field_values.*.flag_reason' => 'nullable|string|max:500'
        ]);

        try {
            $updatedFields = [];

            foreach ($request->field_values as $fieldData) {
                $fieldValue = $inspection->setFieldValue(
                    $fieldData['field_id'],
                    $fieldData['value'] ?? null,
                    $fieldData['score'] ?? null,
                    $fieldData['notes'] ?? null,
                    $fieldData['is_flagged'] ?? false
                );

                if (isset($fieldData['flag_reason']) && $fieldData['is_flagged']) {
                    $fieldValue->flag_reason = $fieldData['flag_reason'];
                    $fieldValue->save();
                }

                $updatedFields[] = [
                    'field_id' => $fieldValue->field_id,
                    'field_name' => $fieldValue->field->name,
                    'value' => $fieldValue->value,
                    'score' => $fieldValue->score,
                    'notes' => $fieldValue->notes,
                    'is_flagged' => $fieldValue->is_flagged,
                    'flag_reason' => $fieldValue->flag_reason
                ];
            }

            return response()->json([
                'data' => [
                    'updated_fields' => $updatedFields,
                    'completion_percentage' => $inspection->fresh()->completion_percentage
                ],
                'message' => 'Field values updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to update field values: ' . $e->getMessage(),
                    'code' => 'FIELD_UPDATE_FAILED'
                ]
            ], 422);
        }
    }

    /**
     * Upload photos for inspection field
     */
    public function uploadPhotos(Request $request, int $inspectionId): JsonResponse
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

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->is_editable) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection is not editable in current status: ' . $inspection->status_display,
                    'code' => 'INSPECTION_NOT_EDITABLE'
                ]
            ], 422);
        }

        $request->validate([
            'field_id' => 'required|integer|exists:car_inspection_fields,id',
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
        ]);

        try {
            $fieldValue = $inspection->fieldValues()
                ->where('field_id', $request->field_id)
                ->first();

            if (!$fieldValue) {
                $fieldValue = $inspection->setFieldValue($request->field_id, null);
            }

            $uploadedPhotos = [];

            foreach ($request->file('photos') as $photo) {

                $photoId = $this->imageUploadService->uploadImage($photo, 'uploads/car-inspections');
                $photoUrl = uploaded_asset($photoId);
                $fieldValue->addAttachment($photoId, $photoUrl);

                $uploadedPhotos[] = [
                    'id' => $photoId,
                    'url' => $photoUrl,
                ];
            }

            return response()->json([
                'data' => [
                    'field_id' => $request->field_id,
                    'uploaded_photos' => $uploadedPhotos,
                    'total_attachments' => $fieldValue->fresh()->attachment_count
                ],
                'message' => 'Photos uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to upload photos: ' . $e->getMessage(),
                    'code' => 'PHOTO_UPLOAD_FAILED'
                ]
            ], 422);
        }
    }
    /**
     * Summary of removePhoto
     * @param \Illuminate\Http\Request $reqquest
     * @param int $inspectionId
     */
    public function removePhoto(Request $request, int $inspectionId)
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

        $inspection = CarInspection::where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if (!$inspection->is_editable) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection is not editable in current status: ' . $inspection->status_display,
                    'code' => 'INSPECTION_NOT_EDITABLE'
                ]
            ], 422);
        }

        $request->validate([
            'field_id' => 'required|integer|exists:car_inspection_fields,id',
            'photo_id' => 'required|integer',
        ]);

        try {
            $fieldValue = $inspection->fieldValues()
                ->where('field_id', $request->field_id)
                ->first();

            $this->imageUploadService->deleteImage($request->photo_id);

            $fieldValue->removeAttachment($request->photo_id);

            return response()->json([
                'data' => [
                    'field_id' => $request->field_id,
                    'total_attachments' => $fieldValue->fresh()->attachment_count
                ],
                'message' => 'Photos uploaded successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => [
                    'message' => 'Failed to remove photos: ' . $e->getMessage(),
                    'code' => 'PHOTO_REMOVE_FAILED'
                ]
            ], 422);
        }

    }
    /**
     * Generate inspection report
     */
    public function generateReport(Request $request, int $inspectionId): JsonResponse
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

        $inspection = CarInspection::with([
            'car',
            'car.brand',
            'car.model',
            'inspectionType',
            'inspectionType.sections.fields',
            'fieldValuesWithRelations',
            'requester'
        ])->where('inspector_id', $inspector->id)
            ->find($inspectionId);

        if (!$inspection) {
            return response()->json([
                'error' => [
                    'message' => 'Inspection not found or not assigned to you',
                    'code' => 'INSPECTION_NOT_FOUND'
                ]
            ], 404);
        }

        if ($inspection->status !== CarInspection::STATUS_COMPLETED) {
            return response()->json([
                'error' => [
                    'message' => 'Report can only be generated for completed inspections',
                    'code' => 'INSPECTION_NOT_COMPLETED'
                ]
            ], 422);
        }

        try {
            $reportData = [
                'inspection' => [
                    'id' => $inspection->id,
                    'inspection_number' => $inspection->inspection_number,
                    'status' => $inspection->status,
                    'scheduled_at' => $inspection->scheduled_at?->toISOString(),
                    'started_at' => $inspection->started_at?->toISOString(),
                    'completed_at' => $inspection->completed_at?->toISOString(),
                    'total_score' => $inspection->total_score,
                    'overall_condition' => $inspection->overall_condition,
                    'condition_display' => $inspection->condition_display,
                    'inspector_notes' => $inspection->inspector_notes,
                    'recommendations' => $inspection->recommendations,
                    'duration' => $inspection->formatted_duration
                ],
                'car' => [
                    'name' => $inspection->car->car_name,
                    'brand' => $inspection->car->brand?->name,
                    'model' => $inspection->car->model?->name,
                    'year' => $inspection->car->year,
                    'fuel_type' => $inspection->car->fuel_type,
                    'transmission_type' => $inspection->car->transmission_type,
                    'mileage' => $inspection->car->mileage,
                    'vin' => $inspection->car->vin
                ],
                'customer' => [
                    'name' => $inspection->requester->name,
                    'email' => $inspection->requester->email,
                    'phone' => $inspection->requester->phone
                ],
                'inspector' => [
                    'name' => $inspector->user->name,
                    'email' => $inspector->user->email,
                    'phone' => $inspector->user->phone
                ],
                'inspection_type' => [
                    'name' => $inspection->inspectionType->name,
                    'description' => $inspection->inspectionType->description,
                    'price' => $inspection->inspectionType->price
                ],
                'sections' => $inspection->inspectionType->sections->map(function ($section) use ($inspection) {
                    return [
                        'name' => $section->name,
                        'description' => $section->description,
                        'fields' => $section->fields->map(function ($field) use ($inspection) {
                            $fieldValue = $inspection->fieldValues->firstWhere('field_id', $field->id);

                            return [
                                'name' => $field->name,
                                'description' => $field->description,
                                'type' => $field->field_type,
                                'value' => $fieldValue?->formatted_value,
                                'score' => $fieldValue?->score,
                                'notes' => $fieldValue?->notes,
                                'is_flagged' => $fieldValue?->is_flagged ?? false,
                                'flag_reason' => $fieldValue?->flag_reason,
                                'photos' => $fieldValue?->file_attachments ?? []
                            ];
                        })
                    ];
                }),
                'summary' => $inspection->summary,
                'generated_at' => now()->toISOString(),
                'report_url' => route('api.car-inspections.download-pdf', $inspection->id)
            ];

            return response()->json([
                'data' => $reportData,
                'message' => 'Inspection report generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Failed to generate report: ' . $e->getMessage(),
                    'code' => 'REPORT_GENERATION_FAILED'
                ]
            ], 422);
        }
    }

    /**
     * Transform inspection data for list view
     */
    private function transformInspection(CarInspection $inspection): array
    {
        return [
            'id' => $inspection->id,
            'inspection_number' => $inspection->inspection_number,
            'status' => $inspection->status,
            'status_display' => $inspection->status_display,
            'scheduled_at' => $inspection->scheduled_at?->toISOString(),
            'started_at' => $inspection->started_at?->toISOString(),
            'completed_at' => $inspection->completed_at?->toISOString(),
            'is_overdue' => $inspection->is_overdue,
            'completion_percentage' => $inspection->completion_percentage,
            'duration' => $inspection->formatted_duration,
            'car' => [
                'id' => $inspection->car->id,
                'vin'   => $inspection->car->vin,
                'name' => $inspection->car->car_name,
                'brand' => $inspection->car->brand?->name,
                'model' => $inspection->car->model?->name,
                'year' => $inspection->car->year,
                'fuel_type' => $inspection->car->fuel_type,
                'transmission_type' => $inspection->car->transmission_type
            ],
            'inspection_type' => [
                'id' => $inspection->inspectionType->id,
                'name' => $inspection->inspectionType->name,
                'price' => $inspection->inspectionType->price
            ],
            'customer' => [
                'id' => $inspection->requester->id,
                'name' => $inspection->requester->name,
                'email' => $inspection->requester->email,
                'phone' => $inspection->requester->phone
            ],
            'actions' => [
                'can_start' => $inspection->can_start,
                'can_complete' => $inspection->can_complete,
                'can_cancel' => $inspection->can_cancel,
                'is_editable' => $inspection->is_editable
            ]
        ];
    }

    /**
     * Transform inspection data for detailed view
     */
    private function transformInspectionDetail(CarInspection $inspection): array
    {
        $baseData = $this->transformInspection($inspection);

        // Add detailed information
        $baseData['car']['color'] = $inspection->car->color?->name;
        $baseData['car']['mileage'] = $inspection->car->mileage;
        $baseData['car']['vin'] = $inspection->car->vin;

        $baseData['inspection_type']['description'] = $inspection->inspectionType->description;

        $baseData['total_score'] = $inspection->total_score;
        $baseData['overall_condition'] = $inspection->overall_condition;
        $baseData['condition_display'] = $inspection->condition_display;
        $baseData['inspector_notes'] = $inspection->inspector_notes;
        $baseData['recommendations'] = $inspection->recommendations;
        $baseData['summary'] = $inspection->summary;
        $baseData['metadata'] = $inspection->metadata;

        // Add inspection sections and fields
        $baseData['sections'] = $inspection->inspectionType->sections->map(function ($section) use ($inspection) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'order' => $section->order,
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
                        'value' => $fieldValue?->formatted_value,
                        'score' => $fieldValue?->score,
                        'notes' => $fieldValue?->notes,
                        'is_flagged' => $fieldValue?->is_flagged ?? false,
                        'photos' => $fieldValue ? $fieldValue->file_attachments : []
                    ];
                })
            ];
        });

        // Add payment information
        if ($inspection->payment) {
            $baseData['payment'] = [
                'id' => $inspection->payment->id,
                'amount' => $inspection->payment->amount,
                'status' => $inspection->payment->status,
                'payment_method' => $inspection->payment->payment_method,
                'paid_at' => $inspection->payment->created_at?->toISOString()
            ];
        }

        return $baseData;
    }

    /**
     * Update inspection status with comprehensive error handling
     */
    public function updateStatus(Request $request, int $inspectionId): JsonResponse
    {
        try {
            $this->ensureInspectorAccess();

            $request->validate([
                'status' => 'required|string|in:in_progress,completed,cancelled',
                'notes' => 'sometimes|string|max:1000',
                'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
                'overall_condition' => 'required_if:status,completed|string|in:excellent,good,fair,poor,failed',
                'recommendations' => 'sometimes|string|max:2000',
            ]);

            $inspection = CarInspection::where('inspector_id', $this->getInspectorProfile()->id)
                ->findOrFail($inspectionId);

            $statusService = new InspectionStatusService();
            $updatedInspection = $statusService->transitionStatus(
                $inspection,
                $request->status,
                $request->only(['notes', 'cancellation_reason', 'overall_condition', 'recommendations'])
            );

            // Log successful status change
            ErrorLoggingService::logSuccess('inspection_status_updated', [
                'inspection_id' => $inspectionId,
                'old_status' => $inspection->status,
                'new_status' => $request->status,
            ]);

            return $this->successResponse(
                $this->transformInspection($updatedInspection),
                'Inspection status updated successfully'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update inspection status');
        }
    }

    /**
     * Validate inspection field values with business logic error handling (enhanced version)
     */
    public function validateFieldValuesEnhanced(Request $request, int $inspectionId): JsonResponse
    {
        try {
            $this->ensureInspectorAccess();

            $inspection = CarInspection::where('inspector_id', $this->getInspectorProfile()->id)
                ->findOrFail($inspectionId);

            $this->validateInspectionOwnership($inspection);

            // Validate that inspection is in progress
            if ($inspection->status !== 'in_progress') {
                ErrorLoggingService::logBusinessLogicError('field_validation_invalid_status', [
                    'inspection_id' => $inspectionId,
                    'current_status' => $inspection->status,
                    'required_status' => 'in_progress',
                ]);

                return $this->errorResponse(
                    'Field values can only be updated for inspections in progress',
                    422,
                    ['current_status' => $inspection->status]
                );
            }

            $request->validate([
                'field_values' => 'required|array',
                'field_values.*.field_id' => 'required|integer|exists:car_inspection_fields,id',
                'field_values.*.value' => 'required|string',
                'field_values.*.notes' => 'sometimes|string|max:500',
            ]);

            // Process field values
            $updatedFields = [];
            foreach ($request->field_values as $fieldData) {
                $fieldValue = $inspection->fieldValues()
                    ->updateOrCreate(
                        ['field_id' => $fieldData['field_id']],
                        [
                            'value' => $fieldData['value'],
                            'notes' => $fieldData['notes'] ?? null,
                        ]
                    );
                $updatedFields[] = $fieldValue;
            }

            // Log successful field update
            ErrorLoggingService::logSuccess('inspection_fields_updated', [
                'inspection_id' => $inspectionId,
                'field_count' => count($updatedFields),
                'field_ids' => array_column($request->field_values, 'field_id'),
            ]);

            return $this->successResponse([
                'updated_fields' => $updatedFields,
                'total_count' => count($updatedFields),
            ], 'Field values updated successfully');
        } catch (\Exception $e) {
            ErrorLoggingService::logBusinessLogicError('field_validation_failed', [
                'inspection_id' => $inspectionId,
                'field_data' => $request->field_values ?? [],
            ], $e);

            return $this->handleException($e, 'Failed to update field values');
        }
    }
}
