<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CarInspectionResource;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\CarInspection;
use App\Models\CarInspectionType;
use \Exception;
use Validator;
use PDF;
use Auth;

class CarInspectionController extends Controller
{

    public function __counstruct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Index of car inspections for user
     *
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inspection_type_id' => 'nullable| exists:car_inspection_types,id',
            'start_date' => 'nullable|date|required_with:end_date',
            'end_date'   => 'nullable|date|required_with:start_date|after:start_date',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        try {
            $user = auth('api')->user();

            $inspectionsQuery = CarInspection::regular();
            if($request->filled('inspection_type_id')) {
               $inspectionsQuery->where('inspection_type_id', $request->inspection_type_id);
            }

            if($request->filled('start_date') && $request->filled('end_date')) {
               if ($request->filled(['start_date', 'end_date'])) {
                    $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                    $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();

                    $inspectionsQuery->whereBetween('created_at', [$start, $end]);
                }
            }

            $inspectionsQuery->where('requested_by', $user->id)
            ->with('car','inspector','inspectionType');

            $inspections = $inspectionsQuery->paginate(10);

            return CarInspectionResource::collection($inspections);
        } catch (Exception $e) {
            return response()->json([
                'success'   => false,
                'message'   => $e->getMessage()
            ]);
        }
    }
    /**
     * Get the car inspection
     */

    public function show(CarInspection $carInspection)
    {
        try {
            if ($carInspection->is_manual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inspection not found'
                ], 404);
            }

            if ($carInspection->status !== CarInspection::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Inspection is not ready yet!'
                ], 403);
            }

            // Check if user owns the inspection
            // if ($carInspection->requested_by != auth('api')->user()->id) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized access to this inspection'
            //     ], 403);
            // }

            $carInspection->load([
                "inspectionType.sections.fields",
                "inspector",
                "fieldValues.field.section",
            ]);


            return new CarInspectionResource($carInspection);
        } catch (Exception $e) {
            return response()->json([
                'success'   => false,
                'message'   => $e->getMessage()
            ]);
        }
    }


    /**
     * Order an inspection for a car
     */

    public function order(Request $request) {
          $validator = Validator::make($request->all(),
            [
                'car_id' => ['required', 'integer', 'exists:cars,id'],
                "inspection_type_id" => ['required','integer','exists:car_inspection_types,id'],
                "inspector_id"  => ['sometimes', 'integer', 'exists:car_inspectors,id']
            ]
        );

        if($validator->fails()){
            return response()->json([
                   "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
            ],422,);
        }

        $data = $validator->validated();
        $car = Car::find($data['car_id']);

        //check if car is available
        if($car->isSold() || !$car->isPublished()){
            return  response()->json([
                   "success" => false,
                    "message" => "Car is not available",
            ],422,);
        }

        $inspection_type = CarInspectionType::find($data['inspection_type_id']);
            if ($inspection_type->is_system_default){
                $data['inspector_id'] = null; // system default inspection types should not have inspector assigned at creation
                } else {
                    if (!isset($data['inspector_id'])) {
                        return response()->json([
                            "success" => false,
                            "message" => "Inspector is required for this inspection type",
                        ], 422);
                    }
            }

        $data["requested_by"] = auth('api')->user()->id;

         //Check if user has ongoing inspections for this car
        $pendingInspections = CarInspection::byCar($data['car_id'])->byRequester($data["requested_by"])->byStatus('pending')->count();
        if($pendingInspections > 0){
            return response()->json([
                "success" => false,
                "message" => "You already have a pending inspection for this car",
            ]);
        }

        try {
            $inspection = CarInspection::create($data);

            return response()->json(
                    [
                        "success" => true,
                        "message" => "Inspection created successfully",
                        "inspection_id" => $inspection->id,
                    ],
                    201,
                );

        } catch (Exception $e) {
            return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to create inspection",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
        }
    }

    public function downloadPdf(CarInspection $carInspection)
{
    if ($carInspection->status !== CarInspection::STATUS_COMPLETED) {
        return redirect()
            ->back()
            ->with('error', 'PDF report is only available for completed inspections');
    }

    $carInspection->load([
        'car.brand',
        'car.model',
        'car.category',
        'inspectionType.sections.fields',
        'inspector',
        'requester',
        'fieldValues.field.section',
    ]);

    $sectionData = [];
    foreach ($carInspection->inspectionType->sections as $section) {
        $sectionData[$section->id] = [
            'section'    => $section,
            'fields'     => [],
            'completion' => $carInspection->getSectionCompletion($section->id),
        ];
        foreach ($section->fields as $field) {
            $sectionData[$section->id]['fields'][] = [
                'field' => $field,
                'value' => $carInspection->fieldValues->where('field_id', $field->id)->first(),
            ];
        }
    }

    $options = get_pdf_options();
    $pdf = PDF::loadView('backend.cars.inspections.pdf-report', [
        'carInspection'  => $carInspection,
        'sectionData'    => $sectionData,
        'font_family'    => $options['font_family'],
        'direction'      => $options['direction'],
        'text_align'     => $options['text_align'],
        'not_text_align' => $options['not_text_align'],
    ]);

    $filename = 'inspection-report-' . $carInspection->inspection_number . '.pdf';

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
}
