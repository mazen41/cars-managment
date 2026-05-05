<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarInspectionFieldRequest;
use App\Models\CarInspectionField;
use App\Models\CarInspectionSection;
use App\Models\CarInspectionType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CarInspectionFieldController extends Controller
{


    public function __construct()
    {
        $this->middleware("permission:edit_car_inspection_types")->only([
            "store",
            "update",
            "destroy",
            "edit",
            "duplicate",
            "sortFields",
            "toggleFieldStatus",
            "bulkUpdate",
        ]);
    }
     /**
     * Store a new field for the section
     */
    public function store(
        CarInspectionFieldRequest $request,
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
    ) {
        // Ensure the section belongs to this inspection type
        if (
            $carInspectionSection->inspection_type_id !== $carInspectionType->id
        ) {
            abort(404);
        }

        try {
            $field = $carInspectionSection->fields()->create([
                "name" => $request->name,
                "slug" => \Str::slug($request->name),
                "description" => $request->description,
                "field_type" => $request->field_type,
                "field_options" => $request->field_options,
                "is_required" => $request->get("is_required", false),
                "is_active" => $request->get("is_active", true),
                "sort_order" =>
                    $request->sort_order ??
                    $carInspectionSection->fields()->max("sort_order") + 1,
                "placeholder" => $request->placeholder,
                "help_text" => $request->help_text,
                "validation_rules" => $request->validation_rules,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Field created successfully",
                    "data" => $field,
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Field created successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to create field",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Failed to create field: " . $e->getMessage());
        }
    }

    /**
     * Edit a field
     */
    public function edit(
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        CarInspectionField $carInspectionField,
    ) {
        // Ensure the field belongs to this section and inspection type
        if (
            $carInspectionSection->inspection_type_id !==
                $carInspectionType->id ||
            $carInspectionField->section_id !== $carInspectionSection->id
        ) {
            abort(404);
        }

        return response()->json([
            "success" => true,
            "data" => $carInspectionField,
        ]);
    }

    /**
     * Update a field
     */
    public function update(
        CarInspectionFieldRequest $request,
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        CarInspectionField $carInspectionField,
    ) {
        // Ensure the field belongs to this section and inspection type
        if (
            $carInspectionSection->inspection_type_id !==
                $carInspectionType->id ||
            $carInspectionField->section_id !== $carInspectionSection->id
        ) {
            abort(404);
        }

        try {

            $carInspectionField->update([
                "name" => $request->name,
                "slug" => \Str::slug($request->name),
                "description" => $request->description,
                "field_type" => $request->field_type,
                "field_options" => $request->field_options,
                "is_required" => $request->get("is_required", false),
                "is_active" => $request->get("is_active", true),
                "sort_order" =>
                    $request->sort_order ?? $carInspectionField->sort_order,
                "placeholder" => $request->placeholder,
                "help_text" => $request->help_text,
                "validation_rules" => $request->validation_rules,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Field updated successfully",
                    "data" => $carInspectionField,
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Field updated successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to update field",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Failed to update field: " . $e->getMessage());
        }
    }

    /**
     * Delete a field
     */
    public function destroy(
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        CarInspectionField $carInspectionField,
    ) {
        // Ensure the field belongs to this section and inspection type
        if (
            $carInspectionSection->inspection_type_id !==
                $carInspectionType->id ||
            $carInspectionField->section_id !== $carInspectionSection->id
        ) {
            abort(404);
        }

        try {
            $carInspectionField->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Field deleted successfully",
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Field deleted successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to delete field",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Failed to delete field: " . $e->getMessage());
        }
    }

    /**
     * Duplicate a field
     */
    public function duplicate(
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        CarInspectionField $carInspectionField,
    ) {
        // Ensure the field belongs to this section and inspection type
        if (
            $carInspectionSection->inspection_type_id !==
                $carInspectionType->id ||
            $carInspectionField->section_id !== $carInspectionSection->id
        ) {
            abort(404);
        }

        try {
            $duplicatedField = $carInspectionField->duplicate(
                $carInspectionSection->id,
                $carInspectionField->name . " (Copy)",
                $carInspectionField->slug . "-copy",
            );

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Field duplicated successfully",
                    "data" => $duplicatedField,
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Field duplicated successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to duplicate field",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to duplicate field: " . $e->getMessage(),
                );
        }
    }

    /**
     * Sort fields in bulk
     */
    public function sortFields(
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        Request $request,
    ) {
        // Ensure the section belongs to this inspection type
        if (
            $carInspectionSection->inspection_type_id !== $carInspectionType->id
        ) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            "order" => "required|array",
            "order.*" => "required|integer|exists:car_inspection_fields,id",
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $validator->errors(),
                    ],
                    422,
                );
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            foreach ($request->order as $index => $fieldId) {
                $field = CarInspectionField::findOrFail($fieldId);

                // Ensure the field belongs to this section
                if ($field->section_id !== $carInspectionSection->id) {
                    throw new \Exception(
                        "Field {$field->id} does not belong to this section",
                    );
                }

                $field->sort_order = $index + 1;
                $field->save();
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Fields sorted successfully",
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Fields sorted successfully");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to sort fields",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Failed to sort fields: " . $e->getMessage());
        }
    }

    /**
     * Toggle field status (active/inactive)
     */
    public function toggleFieldStatus(
        CarInspectionType $carInspectionType,
        CarInspectionSection $carInspectionSection,
        CarInspectionField $carInspectionField,
    ) {
        // Ensure the field belongs to this section and inspection type
        if (
            $carInspectionSection->inspection_type_id !==
                $carInspectionType->id ||
            $carInspectionField->section_id !== $carInspectionSection->id
        ) {
            abort(404);
        }

        try {
            $carInspectionField->update([
                "is_active" => !$carInspectionField->is_active,
            ]);

            $status = $carInspectionField->is_active
                ? "activated"
                : "deactivated";

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Field {$status} successfully",
                    "data" => $carInspectionField,
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Field {$status} successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to toggle field status",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to toggle field status: " . $e->getMessage(),
                );
        }
    }

    /**
     * Get fields by section
     */
    public function getBySectionId(
        Request $request,
        CarInspectionSection $carInspectionSection,
    ) {
        try {
            $query = $carInspectionSection->fields();

            // Filter active only
            if ($request->has("active_only") && $request->active_only) {
                $query->active();
            }

            // Filter required only
            if ($request->has("required_only") && $request->required_only) {
                $query->required();
            }

            $fields = $query->ordered()->get();

            return response()->json([
                "success" => true,
                "data" => $fields,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve fields",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get field statistics
     */
    public function statistics(CarInspectionField $carInspectionField)
    {
        try {
            $statistics = $carInspectionField->getStatistics();

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

    /**
     * API endpoint for listing fields
     */
    public function apiIndex(Request $request)
    {
        $query = CarInspectionField::with(["section.inspectionType"]);

        // Filter by section
        if ($request->has("section_id")) {
            $query->where("section_id", $request->section_id);
        }

        // Filter by field type
        if ($request->has("field_type")) {
            $query->where("field_type", $request->field_type);
        }

        // Only active fields for public API
        if (!auth()->check() || !auth()->user()->hasRole("admin")) {
            $query->active();
        }

        $fields = $query->ordered()->get();

        return response()->json([
            "success" => true,
            "data" => $fields,
        ]);
    }

    /**
     * Get field validation preview
     */
    public function getValidationPreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "field_type" => [
                "required",
                "string",
                "in:" .
                implode(",", array_keys(CarInspectionField::FIELD_TYPES)),
            ],
            "is_required" => "boolean",
            "validation_rules" => "nullable|array",
            "field_options" => "nullable|array",
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
            // Create a temporary field instance to get validation rules
            $tempField = new CarInspectionField([
                "field_type" => $request->field_type,
                "is_required" => $request->boolean("is_required"),
                "validation_rules" => $request->input("validation_rules", []),
                "field_options" => $request->input("field_options", []),
            ]);

            $validationRules = $tempField->validation_rules_array;
            $fieldTypeDisplay =
                CarInspectionField::FIELD_TYPES[$request->field_type];

            return response()->json([
                "success" => true,
                "data" => [
                    "field_type" => $request->field_type,
                    "field_type_display" => $fieldTypeDisplay,
                    "validation_rules" => $validationRules,
                    "has_options" => in_array($request->field_type, [
                        CarInspectionField::FIELD_TYPE_SELECT,
                        CarInspectionField::FIELD_TYPE_RADIO,
                        CarInspectionField::FIELD_TYPE_CHECKBOX,
                    ]),
                    "is_multiple" =>
                        $request->field_type ===
                        CarInspectionField::FIELD_TYPE_CHECKBOX,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to generate validation preview",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
