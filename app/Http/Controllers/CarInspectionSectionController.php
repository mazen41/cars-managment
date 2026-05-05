<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarInspectionFieldRequest;
use App\Http\Requests\CarInspectionSectionRequest;
use App\Models\CarInspectionSection;
use App\Models\CarInspectionType;
use App\Models\CarInspectionField;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CarInspectionSectionController extends Controller
{

    public function __construct(){
        $this->middleware("permission:edit_car_inspection_types")->only([
            "store",
            "update",
            "destroy",
            "duplicate",
            "toggleSectionStatus",
            "sortSections",
        ]);
    }
      /**
     * Store a new section for the inspection type
     */
    public function store(
        CarInspectionSectionRequest $request,
        CarInspectionType $carInspectionType,
    ) {

        try {
            $section = $carInspectionType->sections()->create([
                "name" => $request->name,
                "slug" => \Str::slug($request->name),
                "description" => $request->description,
                "is_active" => $request->get("is_active", true),
                "sort_order" =>
                    $request->sort_order ??
                    $carInspectionType->sections()->max("sort_order") + 1,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Section created successfully",
                    "data" => $section->load("fields"),
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Section created successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to create section",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    "error",
                    "Failed to create section: " . $e->getMessage(),
                );
        }
    }

    /**
     * Update a section
     */
    public function update(
        CarInspectionSectionRequest $request,
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
            $carInspectionSection->update([
                "name" => $request->name,
                "slug" => \Str::slug($request->name),
                "description" => $request->description,
                "is_active" => $request->get("is_active", true),
                "sort_order" =>
                    $request->sort_order ?? $carInspectionSection->sort_order,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Section updated successfully",
                    "data" => $carInspectionSection->load("fields"),
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Section updated successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to update section",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    "error",
                    "Failed to update section: " . $e->getMessage(),
                );
        }
    }

    /**
     * Delete a section
     */
    public function destroy(
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
            $carInspectionSection->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Section deleted successfully",
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Section deleted successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to delete section",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to delete section: " . $e->getMessage(),
                );
        }
    }

    /**
     * Duplicate a section
     */
    public function duplicate(
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
            $duplicatedSection = $carInspectionSection->duplicate(
                $carInspectionType->id,
                $carInspectionSection->name . " (Copy)",
                $carInspectionSection->slug . "-copy",
            );

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Section duplicated successfully",
                    "data" => $duplicatedSection->load("fields"),
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Section duplicated successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to duplicate section",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to duplicate section: " . $e->getMessage(),
                );
        }
    }

    /**
     * Toggle section status (active/inactive)
     */
    public function toggleSectionStatus(
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
            $carInspectionSection->is_active = !$carInspectionSection->is_active;
            $carInspectionSection->save();

            $status = $carInspectionSection->is_active
                ? "activated"
                : "deactivated";

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Section {$status} successfully",
                    "is_active" => $carInspectionSection->is_active,
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Section {$status} successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to toggle section status",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to toggle section status: " . $e->getMessage(),
                );
        }
    }

    /**
     * Sort sections in bulk
     */
    public function sortSections(
        CarInspectionType $carInspectionType,
        Request $request,
    ) {
        $validator = Validator::make($request->all(), [
            "order" => "required|array",
            "order.*" => "required|integer|exists:car_inspection_sections,id",
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

            foreach ($request->order as $index => $sectionId) {
                $section = CarInspectionSection::findOrFail($sectionId);

                // Ensure the section belongs to this inspection type
                if ($section->inspection_type_id !== $carInspectionType->id) {
                    throw new \Exception(
                        "Section {$section->id} does not belong to this inspection type",
                    );
                }

                $section->sort_order = $index + 1;
                $section->save();
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Sections sorted successfully",
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with("success", "Sections sorted successfully");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to sort sections",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Failed to sort sections: " . $e->getMessage());
        }
    }

    /**
     * Get sections by inspection type
     */
    public function getByInspectionType(
        Request $request,
        CarInspectionType $carInspectionType,
    ) {
        try {
            $query = $carInspectionType->sections();

            // Include fields if requested
            if ($request->has("with_fields")) {
                $query->with([
                    "fields" => function ($q) {
                        $q->orderBy("sort_order");
                    },
                ]);
            }

            // Filter active only
            if ($request->has("active_only") && $request->active_only) {
                $query->active();
            }

            $sections = $query->ordered()->get();

            return response()->json([
                "success" => true,
                "data" => $sections,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve sections",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get section statistics
     */
    public function statistics(CarInspectionSection $carInspectionSection)
    {
        try {
            $statistics = $carInspectionSection->getStatistics();

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
     * API endpoint for listing sections
     */
    public function apiIndex(Request $request)
    {
        $query = CarInspectionSection::with(["inspectionType"]);

        // Filter by inspection type
        if ($request->has("inspection_type_id")) {
            $query->where("inspection_type_id", $request->inspection_type_id);
        }

        // Only active sections for public API
        if (!auth()->check() || !auth()->user()->hasRole("admin")) {
            $query->active();
        }

        if ($request->has("with_fields")) {
            $query->with([
                "fields" => function ($q) {
                    $q->active()->ordered();
                },
            ]);
        }

        $sections = $query->ordered()->get();

        return response()->json([
            "success" => true,
            "data" => $sections,
        ]);
    }

}
