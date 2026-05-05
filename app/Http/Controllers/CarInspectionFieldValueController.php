<?php

namespace App\Http\Controllers;

use App\Models\CarInspection;
use App\Models\CarInspectionField;
use App\Models\CarInspectionFieldValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarInspectionFieldValueController extends Controller
{
    /**
     * Store a newly created field value.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspection_id" => "required|integer|exists:car_inspections,id",
            "field_id" => "required|integer|exists:car_inspection_fields,id",
            "value" => "nullable",
            "score" => "nullable|numeric|min:0|max:100",
            "notes" => "nullable|string",
            "is_flagged" => "boolean",
            "flag_reason" => "nullable|string|max:255",
            "files" => "nullable|array",
            "files.*" => "file|max:10240", // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $inspection = CarInspection::findOrFail($request->inspection_id);
            $field = CarInspectionField::findOrFail($request->field_id);

            // Check if inspection is editable
            if (!$inspection->is_editable) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "This inspection cannot be modified",
                    ],
                    403,
                );
            }

            // Validate field belongs to the inspection type
            if (
                $field->section->inspection_type_id !==
                $inspection->inspection_type_id
            ) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Field does not belong to this inspection type",
                    ],
                    400,
                );
            }

            $data = $validator->validated();

            // Handle file uploads
            $fileAttachments = [];
            if ($request->hasFile("files")) {
                foreach ($request->file("files") as $file) {
                    $path = $file->store(
                        "inspections/" . $inspection->id . "/attachments",
                        "public",
                    );
                    $fileAttachments[] = [
                        "path" => $path,
                        "original_name" => $file->getClientOriginalName(),
                        "uploaded_at" => now()->toISOString(),
                        "size" => $file->getSize(),
                        "mime_type" => $file->getMimeType(),
                        "uploaded_by" => Auth::id(),
                    ];
                }
            }

            // Create or update field value
            $fieldValue = CarInspectionFieldValue::updateOrCreate(
                [
                    "inspection_id" => $request->inspection_id,
                    "field_id" => $request->field_id,
                ],
                [
                    "value" => $data["value"] ?? null,
                    "score" => $data["score"] ?? null,
                    "notes" => $data["notes"] ?? null,
                    "is_flagged" => $data["is_flagged"] ?? false,
                    "flag_reason" => $data["flag_reason"] ?? null,
                    "file_attachments" => !empty($fileAttachments)
                        ? $fileAttachments
                        : null,
                ],
            );

            // Load relationships for response
            $fieldValue->load(["field.section", "inspection"]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Field value saved successfully",
                    "data" => $fieldValue,
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to save field value",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Update the specified field value.
     */
    public function update(
        Request $request,
        CarInspectionFieldValue $carInspectionFieldValue,
    ) {
        // Check if inspection is editable
        if (!$carInspectionFieldValue->inspection->is_editable) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "This inspection cannot be modified",
                ],
                403,
            );
        }

        $validator = Validator::make($request->all(), [
            "value" => "nullable",
            "score" => "nullable|numeric|min:0|max:100",
            "notes" => "nullable|string",
            "is_flagged" => "boolean",
            "flag_reason" => "nullable|string|max:255",
            "files" => "nullable|array",
            "files.*" => "file|max:10240", // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $data = $validator->validated();

            // Handle file uploads
            $existingAttachments =
                $carInspectionFieldValue->file_attachments ?? [];
            if ($request->hasFile("files")) {
                foreach ($request->file("files") as $file) {
                    $path = $file->store(
                        "inspections/" .
                            $carInspectionFieldValue->inspection_id .
                            "/attachments",
                        "public",
                    );
                    $existingAttachments[] = [
                        "path" => $path,
                        "original_name" => $file->getClientOriginalName(),
                        "uploaded_at" => now()->toISOString(),
                        "size" => $file->getSize(),
                        "mime_type" => $file->getMimeType(),
                        "uploaded_by" => Auth::id(),
                    ];
                }
                $data["file_attachments"] = $existingAttachments;
            }

            $carInspectionFieldValue->update($data);
            $carInspectionFieldValue->load(["field.section", "inspection"]);

            return response()->json([
                "success" => true,
                "message" => "Field value updated successfully",
                "data" => $carInspectionFieldValue,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update field value",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Remove the specified field value.
     */
    public function destroy(CarInspectionFieldValue $carInspectionFieldValue)
    {
        // Check if inspection is editable
        if (!$carInspectionFieldValue->inspection->is_editable) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "This inspection cannot be modified",
                ],
                403,
            );
        }

        try {
            $carInspectionFieldValue->delete();

            return response()->json([
                "success" => true,
                "message" => "Field value deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to delete field value",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Flag a field value
     */
    public function flag(
        Request $request,
        CarInspectionFieldValue $carInspectionFieldValue,
    ) {
        $validator = Validator::make($request->all(), [
            "reason" => "required|string|max:255",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $carInspectionFieldValue->flag($request->reason);

            return response()->json([
                "success" => true,
                "message" => "Field value flagged successfully",
                "data" => $carInspectionFieldValue->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to flag field value",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Unflag a field value
     */
    public function unflag(CarInspectionFieldValue $carInspectionFieldValue)
    {
        try {
            $carInspectionFieldValue->unflag();

            return response()->json([
                "success" => true,
                "message" => "Field value unflagged successfully",
                "data" => $carInspectionFieldValue->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to unflag field value",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }


    /**
     * Get field values for a specific inspection
     */
    public function getByInspection(
        Request $request,
        CarInspection $carInspection,
    ) {
        try {
            $fieldValues = $carInspection
                ->fieldValues()
                ->with(["field.section"])
                ->get()
                ->groupBy("field.section.id");

            return response()->json([
                "success" => true,
                "data" => $fieldValues,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve field values",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Bulk update field values for an inspection
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspection_id" => "required|integer|exists:car_inspections,id",
            "field_values" => "required|array",
            "field_values.*.field_id" =>
                "required|integer|exists:car_inspection_fields,id",
            "field_values.*.value" => "nullable",
            "field_values.*.score" => "nullable|numeric|min:0|max:100",
            "field_values.*.notes" => "nullable|string",
            "field_values.*.is_flagged" => "boolean",
            "field_values.*.flag_reason" => "nullable|string|max:255",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $inspection = CarInspection::findOrFail($request->inspection_id);

            // Check if inspection is editable
            if (!$inspection->is_editable) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "This inspection cannot be modified",
                    ],
                    403,
                );
            }

            $updatedValues = [];
            $errors = [];

            foreach ($request->field_values as $fieldValueData) {
                try {
                    $field = CarInspectionField::findOrFail(
                        $fieldValueData["field_id"],
                    );

                    // Validate field belongs to the inspection type
                    if (
                        $field->section->inspection_type_id !==
                        $inspection->inspection_type_id
                    ) {
                        $errors[] = "Field {$field->name} does not belong to this inspection type";
                        continue;
                    }

                    $fieldValue = CarInspectionFieldValue::updateOrCreate(
                        [
                            "inspection_id" => $inspection->id,
                            "field_id" => $field->id,
                        ],
                        [
                            "value" => $fieldValueData["value"] ?? null,
                            "score" => $fieldValueData["score"] ?? null,
                            "notes" => $fieldValueData["notes"] ?? null,
                            "is_flagged" =>
                                $fieldValueData["is_flagged"] ?? false,
                            "flag_reason" =>
                                $fieldValueData["flag_reason"] ?? null,
                        ],
                    );

                    $updatedValues[] = $fieldValue->load(["field.section"]);
                } catch (\Exception $e) {
                    $errors[] =
                        "Failed to update field {$fieldValueData["field_id"]}: " .
                        $e->getMessage();
                }
            }

            return response()->json([
                "success" => true,
                "message" =>
                    count($updatedValues) .
                    " field values updated successfully",
                "data" => $updatedValues,
                "errors" => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to bulk update field values",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get field value statistics
     */
    public function getStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspection_id" => "nullable|integer|exists:car_inspections,id",
            "field_id" => "nullable|integer|exists:car_inspection_fields,id",
            "section_id" =>
                "nullable|integer|exists:car_inspection_sections,id",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $query = CarInspectionFieldValue::with(["field", "inspection"]);

            if ($request->inspection_id) {
                $query->where("inspection_id", $request->inspection_id);
            }

            if ($request->field_id) {
                $query->where("field_id", $request->field_id);
            }

            if ($request->section_id) {
                $query->whereHas("field", function ($q) use ($request) {
                    $q->where("section_id", $request->section_id);
                });
            }

            $fieldValues = $query->get();

            $statistics = [
                "total_values" => $fieldValues->count(),
                "completed_values" => $fieldValues
                    ->whereNotNull("value")
                    ->count(),
                "flagged_values" => $fieldValues
                    ->where("is_flagged", true)
                    ->count(),
                "scored_values" => $fieldValues->whereNotNull("score")->count(),
                "average_score" => $fieldValues
                    ->whereNotNull("score")
                    ->avg("score"),
                "completion_rate" =>
                    $fieldValues->count() > 0
                        ? round(
                            ($fieldValues->whereNotNull("value")->count() /
                                $fieldValues->count()) *
                                100,
                            2,
                        )
                        : 0,
            ];

            return response()->json([
                "success" => true,
                "data" => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to get statistics",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
