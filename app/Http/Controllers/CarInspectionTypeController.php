<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarInspectionSectionRequest;
use App\Models\CarInspectionType;
use App\Models\CarInspectionSection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CarInspectionTypeController extends Controller
{
    /**
     * Constructor - Apply permission middleware
     */
    public function __construct()
    {
        $this->middleware("permission:view_car_inspection_types")->only([
            "index",
            "show",
            "statistics",
        ]);
        $this->middleware("permission:create_car_inspection_types")->only([
            "create",
            "store",
        ]);
        $this->middleware("permission:edit_car_inspection_types")->only([
            "edit",
            "update",
            "updateOrder",
        ]);
        $this->middleware("permission:delete_car_inspection_types")->only([
            "destroy",
            "bulkDelete",
        ]);
        $this->middleware("permission:duplicate_car_inspection_types")->only([
            "duplicate",
        ]);
        $this->middleware("permission:toggle_car_inspection_type_status")->only(
            ["toggleStatus", "bulkUpdateStatus"],
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CarInspectionType::with(["sections", "inspections"]);

        // Search functionality
        if ($request->has("search") && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has("status") && $request->status !== "") {
            $isActive = $request->status === "active";
            $query->where("is_active", $isActive);
        }

        // Sorting
        $sortField = $request->get("sort", "sort_order");
        $sortDirection = $request->get("direction", "asc");

        if (in_array($sortField, ["name", "created_at", "sort_order"])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->ordered();
        }

        $carInspectionTypes = $query->paginate(15)->appends($request->query());

        if ($request->wantsJson()) {
            return response()->json([
                "success" => true,
                "data" => $carInspectionTypes,
            ]);
        }

        return view(
            "backend.cars.inspections.types.index",
            compact("carInspectionTypes"),
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("backend.cars.inspections.types.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" =>
                "nullable|string|max:255|unique:car_inspection_types,slug",
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
            "is_active" => "boolean",
            "sort_order" => "integer|min:0",
            "metadata" => "nullable|array",
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

        $data = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($data["slug"])) {
            $data["slug"] = Str::slug($data["name"]);
        }

        // Ensure slug is unique
        $originalSlug = $data["slug"];
        $counter = 1;
        while (CarInspectionType::where("slug", $data["slug"])->exists()) {
            $data["slug"] = $originalSlug . "-" . $counter;
            $counter++;
        }

        try {
            $inspectionType = CarInspectionType::create($data);

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Inspection type created successfully",
                        "data" => $inspectionType->load([
                            "sections",
                            "inspections",
                        ]),
                    ],
                    201,
                );
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $inspectionType)
                ->with("success", "Inspection type created successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to create inspection type",
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
                    "Failed to create inspection type: " . $e->getMessage(),
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CarInspectionType $carInspectionType)
    {
        $carInspectionType->load([
            "sections.fields",
            "inspections" => function ($query) {
                $query
                    ->with(["car", "inspector", "requester"])
                    ->latest()
                    ->limit(10);
            },
        ]);

        $statistics = $carInspectionType->getStatistics();

        if (request()->wantsJson()) {
            return response()->json([
                "success" => true,
                "data" => $carInspectionType,
                "statistics" => $statistics,
            ]);
        }

        return view(
            "backend.cars.inspections.types.show",
            compact("carInspectionType", "statistics"),
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CarInspectionType $carInspectionType)
    {
        if (!$carInspectionType->is_editable) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Cannot edit inspection type that has completed inspections",
                    ],
                    403,
                );
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with(
                    "error",
                    "Cannot edit inspection type that has completed inspections",
                );
        }

        return view(
            "backend.cars.inspections.types.edit",
            compact("carInspectionType"),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        CarInspectionType $carInspectionType,
    ) {
        if (!$carInspectionType->is_editable) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Cannot edit inspection type that has completed inspections",
                    ],
                    403,
                );
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $carInspectionType)
                ->with(
                    "error",
                    "Cannot edit inspection type that has completed inspections",
                );
        }

        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" =>
                "nullable|string|max:255|unique:car_inspection_types,slug," .
                $carInspectionType->id,
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
            "is_system_default" => "boolean",
            "sort_order" => "integer|min:0",
            "metadata" => "nullable|array",
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

        $data = $validator->validated();
        $data["is_system_default"] = $request->input("is_system_default", false);
        // Auto-generate slug if not provided
        if (empty($data["slug"])) {
            $data["slug"] = Str::slug($data["name"]);
        }

        // Ensure slug is unique (excluding current record)
        $originalSlug = $data["slug"];
        $counter = 1;
        while (
            CarInspectionType::where("slug", $data["slug"])
                ->where("id", "!=", $carInspectionType->id)
                ->exists()
        ) {
            $data["slug"] = $originalSlug . "-" . $counter;
            $counter++;
        }

        try {
            $carInspectionType->update($data);
            $carInspectionType->load(["sections", "inspections"]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection type updated successfully",
                    "data" => $carInspectionType,
                ]);
            }
            flash()->success("Inspection type updated successfully");
            return redirect()
                ->back()
                ->with("success", "Inspection type updated successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to update inspection type",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }
            flash()->error(
                "Failed to update inspection type: " . $e->getMessage(),
            );
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    "error",
                    "Failed to update inspection type: " . $e->getMessage(),
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarInspectionType $carInspectionType)
    {
        if ($carInspectionType->inspections()->exists()) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Cannot delete inspection type that has inspections",
                    ],
                    403,
                );
            }
            flash()->error(
                "Cannot delete inspection type that has inspections",
            );
            return redirect()
                ->route("admin.car-inspection-types.index")
                ->with(
                    "error",
                    "Cannot delete inspection type that has inspections",
                );
        }

        try {
            $carInspectionType->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection type deleted successfully",
                ]);
            }
            flash()->success("Inspection type deleted successfully");
            return redirect()
                ->route("admin.car-inspection-types.index")
                ->with("success", "Inspection type deleted successfully");
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to delete inspection type",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }
            flash()->error(
                "Failed to delete inspection type: " . $e->getMessage(),
            );
            return redirect()
                ->route("admin.car-inspection-types.index")
                ->with(
                    "error",
                    "Failed to delete inspection type: " . $e->getMessage(),
                );
        }
    }

    /**
     * Toggle the status of the inspection type
     */
    public function toggleStatus(
        Request $request,
        CarInspectionType $carInspectionType,
    ) {
        try {
            $carInspectionType->update([
                "is_active" => !$carInspectionType->is_active,
            ]);

            $status = $carInspectionType->is_active
                ? "activated"
                : "deactivated";

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection type {$status} successfully",
                    "data" => $carInspectionType,
                ]);
            }

            return redirect()
                ->back()
                ->with("success", "Inspection type {$status} successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to toggle status",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Failed to toggle status: " . $e->getMessage());
        }
    }

    /**
     * Duplicate an inspection type with all its sections and fields
     */
    public function duplicate(
        Request $request,
        CarInspectionType $carInspectionType,
    ) {
        $validator = Validator::make($request->all(), [
            "name" => "nullable|string|max:255",
            "slug" =>
                "nullable|string|max:255|unique:car_inspection_types,slug",
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

            return redirect()->back()->withErrors($validator);
        }

        try {
            $newName = $request->input(
                "name",
                $carInspectionType->name . " (Copy)",
            );
            $newSlug = $request->input("slug");

            $duplicate = $carInspectionType->duplicate($newName, $newSlug);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection type duplicated successfully",
                    "data" => $duplicate->load(["sections.fields"]),
                ]);
            }

            return redirect()
                ->route("admin.car-inspection-types.show", $duplicate)
                ->with("success", "Inspection type duplicated successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to duplicate inspection type",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to duplicate inspection type: " . $e->getMessage(),
                );
        }
    }

    /**
     * Bulk update status for multiple inspection types
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "ids" => "required|array|min:1",
            "ids.*" => "integer|exists:car_inspection_types,id",
            "status" => "required|boolean",
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
            $updated = CarInspectionType::whereIn("id", $request->ids)->update([
                "is_active" => $request->status,
            ]);

            $action = $request->status ? "activated" : "deactivated";

            return response()->json([
                "success" => true,
                "message" => "{$updated} inspection types {$action} successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update inspection types",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Bulk delete multiple inspection types
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "ids" => "required|array|min:1",
            "ids.*" => "integer|exists:car_inspection_types,id",
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
            // Check if any of the types have inspections
            $typesWithInspections = CarInspectionType::whereIn(
                "id",
                $request->ids,
            )
                ->whereHas("inspections")
                ->count();

            if ($typesWithInspections > 0) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Cannot delete inspection types that have inspections",
                    ],
                    403,
                );
            }

            $deleted = CarInspectionType::whereIn(
                "id",
                $request->ids,
            )->delete();

            return response()->json([
                "success" => true,
                "message" => "{$deleted} inspection types deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to delete inspection types",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get inspection type statistics
     */
    public function statistics(CarInspectionType $carInspectionType)
    {
        $statistics = $carInspectionType->getStatistics();

        return response()->json([
            "success" => true,
            "data" => $statistics,
        ]);
    }

    /**
     * API endpoint for listing inspection types
     */
    public function apiIndex(Request $request)
    {
        $query = CarInspectionType::query();

        // Only active types for public API
        if (!Auth::check() || !Auth::user()->hasRole("admin")) {
            $query->active();
        }

        if ($request->has("with_sections")) {
            $query->with("sections.fields");
        }

        $inspectionTypes = $query->ordered()->get();

        return response()->json([
            "success" => true,
            "data" => $inspectionTypes,
        ]);
    }

    /**
     * Update sort order
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "items" => "required|array",
            "items.*.id" => "required|integer|exists:car_inspection_types,id",
            "items.*.sort_order" => "required|integer|min:0",
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
            DB::beginTransaction();

            foreach ($request->items as $item) {
                CarInspectionType::where("id", $item["id"])->update([
                    "sort_order" => $item["sort_order"],
                ]);
            }

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Sort order updated successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update sort order",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
