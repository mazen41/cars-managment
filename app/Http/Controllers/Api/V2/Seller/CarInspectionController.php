<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seller\CarInspectionResource;
use App\Models\Car;
use App\Models\CarInspection;
use App\Traits\SellerCarOwnership;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\V2\Seller\CarIdValidationRequest;
use PDF;

class CarInspectionController extends Controller
{
    use SellerCarOwnership, ApiResponseTrait;
    /**
     * Display a listing of inspections for a specific car.
     *
     * @param int $carId
     * @param CarIdValidationRequest $request
     * @return JsonResponse
     */
    public function index(int $carId, CarIdValidationRequest $request): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Verify car ownership
            $car = $this->verifyCarOwnership($carId);

            if (!$car) {
                return $this->notFoundResponse('Car not found');
            }

            // Get inspections for the car with related data
            $inspections = CarInspection::where('car_id', $carId)
                ->where('inspector_id', '!=', null)
                ->with(['car', 'inspectionType', 'inspector', 'requester', 'payment'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse(
                CarInspectionResource::collection($inspections),
                'Inspections retrieved successfully'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve inspections: ' . $e->getMessage(), [
                'car_id' => $carId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to retrieve inspections');
        }
    }

    /**
     * Display the specified inspection with detailed field values.
     *
     * @param CarInspection $carInspection
     * @return JsonResponse
     */
    public function show(CarInspection $carInspection): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Load car relationship for ownership verification
            $carInspection->load('car');

            // Verify car ownership through inspection
            if (!$this->verifyInspectionOwnership($carInspection)) {
                return $this->notFoundResponse('Inspection not found');
            }

            // Load related data for detailed view including field values
            $carInspection->load([
                'car',
                'inspectionType.sections.fields',
                'inspector',
                'requester',
                'payment',
                'fieldValues.field.section'
            ]);

            return $this->successResponse(
                new CarInspectionResource($carInspection),
                'Inspection details retrieved successfully'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve inspection details: ' . $e->getMessage(), [
                'inspection_id' => $carInspection->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to retrieve inspection details');
        }
    }

    /**
     * Set delivered to inspector status for an inspection.
     */
    public function setDeliveredToInspector(CarInspection $carInspection): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Load car relationship for ownership verification
            $carInspection->load('car');

            // Verify car ownership through inspection
            if (!$this->verifyInspectionOwnership($carInspection)) {
                return $this->notFoundResponse('Inspection not found');
            }

            // Verify inspection delivery status
            if ($carInspection->delivered_to_inspector) {
                return $this->validationErrorResponse([
                    'status' => ['Inspection has already been marked as delivered to inspector.']
                ], 'Inspection has already been marked as delivered to inspector.');
            }

            // Verify if inspection can be marked as delivered
            if (!in_array($carInspection->status, [CarInspection::STATUS_PENDING])) {
                return $this->validationErrorResponse([
                    'status' => ['Inspection cannot be marked as delivered to inspector in its current status: ' . $carInspection->status]
                ], 'Inspection cannot be marked as delivered to inspector in its current status');
            }

            // Verify if inspector is assigned
            if ($carInspection->inspector_id == null ){
                  return $this->validationErrorResponse([
                    'status' => ['Inspection cannot be marked as delivered to inspector as inspector is not assigned']
                ], 'Inspection cannot be marked as delivered to inspector as inspector is not assigned');
            }

            // Update status to delivered to inspector
            $carInspection->delivered_to_inspector = true;
            $carInspection->save();

            return $this->successResponse(
                null,
                'Inspection status updated to delivered to inspector'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to update inspection status: ' . $e->getMessage(), [
                'inspection_id' => $carInspection->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to update inspection status');
        }
    }

    /**
     * Download PDF report for completed inspection.
     *
     * @param CarInspection $carInspection
     * @return Response|JsonResponse
     */
    public function downloadReport(CarInspection $carInspection): Response|JsonResponse| \Symfony\Component\HttpFoundation\Response
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Load car relationship for ownership verification
            $carInspection->load('car');

            // Verify car ownership through inspection
            if (!$this->verifyInspectionOwnership($carInspection)) {
                return $this->notFoundResponse('Inspection not found');
            }

            // Check if inspection is completed
            if ($carInspection->status !== CarInspection::STATUS_COMPLETED) {
                return $this->validationErrorResponse([
                    'status' => ['PDF report is only available for completed inspections. Current status: ' . $carInspection->status]
                ], 'PDF report not available');
            }

            // Load all necessary relationships for PDF generation
            $carInspection->load([
                'car.brand',
                'car.model',
                'car.category',
                'inspectionType.sections.fields',
                'inspector',
                'requester',
                'fieldValues.field.section'
            ]);

            // Organize field values by section for PDF template
            $sectionData = [];
            foreach ($carInspection->inspectionType->sections as $section) {
                $sectionData[$section->id] = [
                    'section' => $section,
                    'fields' => [],
                    'completion' => $carInspection->getSectionCompletion($section->id)
                ];

                foreach ($section->fields as $field) {
                    $fieldValue = $carInspection->fieldValues
                        ->where('field_id', $field->id)
                        ->first();

                    $sectionData[$section->id]['fields'][] = [
                        'field' => $field,
                        'value' => $fieldValue
                    ];
                }
            }

            // Get PDF options and generate PDF
            $options = get_pdf_options();
            $pdf = PDF::loadView('backend.cars.inspections.pdf-report', [
                'carInspection' => $carInspection,
                'sectionData' => $sectionData,
                'font_family' => $options['font_family'],
                'direction' => $options['direction'],
                'text_align' => $options['text_align'],
                'not_text_align' => $options['not_text_align']
            ]);

            $filename = 'inspection-report-' . $carInspection->inspection_number . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF report: ' . $e->getMessage(), [
                'inspection_id' => $carInspection->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to generate PDF report');
        }
    }
}
