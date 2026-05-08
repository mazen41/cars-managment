<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatusEnum;
use App\Events\CarInspectionPaid;
use App\Events\CarInspectorAssigned;
use App\Models\Car;
use App\Models\CarInspection;
use App\Models\CarInspectionFieldValue;
use App\Models\CarInspectionType;
use App\Models\CarInspector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use PDF;

class CarInspectionController extends Controller
{
    public function __construct()
    {
        $this->middleware("permission:edit_car_inspection")->only([
            "create",
            "store",
            "edit",
            "update",
            "destroy",
            "start",
            "conduct",
            "progress",
            "complete",
            "cancel",
            "deliverToInspector",
            "bulkUpdateStatus",
        ]);

        $this->middleware("permission:view_car_inspection")->only([
            "dashboard",
            "chartData",
            "index",
            "show",
            "report",
            "downloadPdf",
            "apiIndex",
        ]);
    }
    /**
     * Display car inspections dashboard with metrics and data
     */
    public function dashboard()
    {
        // Get metrics
        $metrics = $this->getDashboardMetrics();

        // Get recent activities
        $recent_activities = $this->getRecentActivities();

        // Get inspection types stats
        $inspection_types_stats = $this->getInspectionTypesStats();

        // Get top inspectors
        $top_inspectors = $this->getTopInspectors();

        // Get upcoming inspections
        $upcoming_inspections = $this->getUpcomingInspections();

        // Get chart data
        $chart_data = $this->getChartData();

        return view(
            "backend.cars.inspections.dashboard",
            compact(
                "metrics",
                "recent_activities",
                "inspection_types_stats",
                "top_inspectors",
                "upcoming_inspections",
                "chart_data",
            ),
        );
    }

    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics()
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Total inspections
        $total_inspections = CarInspection::count();
        $last_month_total = CarInspection::where("created_at", ">=", $lastMonth)
            ->where("created_at", "<", $currentMonth)
            ->count();

        $inspections_change =
            $last_month_total > 0
                ? (($total_inspections - $last_month_total) /
                        $last_month_total) *
                    100
                : 0;

        // Completed inspections
        $completed_inspections = CarInspection::where(
            "status",
            CarInspection::STATUS_COMPLETED,
        )->count();
        $completion_rate =
            $total_inspections > 0
                ? ($completed_inspections / $total_inspections) * 100
                : 0;

        // Pending inspections
        $pending_inspections = CarInspection::where(
            "status",
            CarInspection::STATUS_PENDING,
        )->count();
        $overdue_inspections = CarInspection::where(
            "status",
            CarInspection::STATUS_PENDING,
        )
            ->where("scheduled_at", "<", now())
            ->count();

        // Average score
        $average_score =
            CarInspection::where("status", CarInspection::STATUS_COMPLETED)
                ->whereNotNull("total_score")
                ->avg("total_score") ?? 0;

        $last_month_avg_score =
            CarInspection::where("status", CarInspection::STATUS_COMPLETED)
                ->where("completed_at", ">=", $lastMonth)
                ->where("completed_at", "<", $currentMonth)
                ->whereNotNull("total_score")
                ->avg("total_score") ?? 0;

        $score_trend =
            $last_month_avg_score > 0
                ? (($average_score - $last_month_avg_score) /
                        $last_month_avg_score) *
                    100
                : 0;

        // Additional metrics
        $total_types = CarInspectionType::where("is_active", true)->count();
        $active_inspectors = User::whereHas("carInspections")
            ->distinct()
            ->count();
        $flagged_items = CarInspectionFieldValue::where(
            "value",
            "like",
            "%fail%",
        )
            ->orWhere("value", "like", "%poor%")
            ->count();

        $avg_duration =
            CarInspection::where("status", CarInspection::STATUS_COMPLETED)
                ->whereNotNull("started_at")
                ->whereNotNull("completed_at")
                ->selectRaw(
                    "AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours",
                )
                ->value("avg_hours") ?? 0;

        return [
            "total_inspections" => $total_inspections,
            "inspections_change" => round($inspections_change, 1),
            "completed_inspections" => $completed_inspections,
            "completion_rate" => round($completion_rate, 1),
            "pending_inspections" => $pending_inspections,
            "overdue_inspections" => $overdue_inspections,
            "average_score" => round($average_score, 1),
            "score_trend" => round($score_trend, 1),
            "total_types" => $total_types,
            "active_inspectors" => $active_inspectors,
            "flagged_items" => $flagged_items,
            "avg_duration" => round($avg_duration, 1),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];

        $recent_inspections = CarInspection::with(["car", "inspector"])
            ->orderBy("updated_at", "desc")
            ->limit(10)
            ->get();

        foreach ($recent_inspections as $inspection) {
            $type = "scheduled";
            $title = "Inspection Scheduled";
            $description =
                "Inspection for " . ($inspection->car->car_name ?? "N/A");

            if ($inspection->status === CarInspection::STATUS_COMPLETED) {
                $type = "completed";
                $title = "Inspection Completed";
                $description =
                    "Inspection for " .
                    ($inspection->car->car_name ?? "N/A") .
                    " completed with score " .
                    $inspection->total_score .
                    "%";
            } elseif (
                $inspection->status === CarInspection::STATUS_IN_PROGRESS
            ) {
                $type = "started";
                $title = "Inspection Started";
                $description =
                    "Inspection for " .
                    ($inspection->car->car_name ?? "N/A") .
                    " is in progress";
            } elseif ($inspection->status === CarInspection::STATUS_CANCELLED) {
                $type = "cancelled";
                $title = "Inspection Cancelled";
                $description =
                    "Inspection for " .
                    ($inspection->car->car_name ?? "N/A") .
                    " was cancelled";
            }

            $activities[] = [
                "type" => $type,
                "title" => $title,
                "description" => $description,
                "time" => $inspection->updated_at->diffForHumans(),
            ];
        }

        return $activities;
    }

    /**
     * Get inspection types statistics
     */
    private function getInspectionTypesStats()
    {
        $types = CarInspectionType::withCount("inspections")
            ->with([
                "inspections" => function ($query) {
                    $query
                        ->where("status", CarInspection::STATUS_COMPLETED)
                        ->whereNotNull("total_score");
                },
            ])
            ->where("is_active", true)
            ->get();

        $total_inspections = CarInspection::count();
        $stats = [];

        foreach ($types as $type) {
            $usage_percentage =
                $total_inspections > 0
                    ? ($type->inspections_count / $total_inspections) * 100
                    : 0;
            $avg_score = $type->inspections->avg("total_score") ?? 0;

            $stats[] = [
                "name" => $type->name,
                "count" => $type->inspections_count,
                "usage_percentage" => round($usage_percentage, 1),
                "avg_score" => round($avg_score, 1),
            ];
        }

        return collect($stats)->sortByDesc("count")->values()->all();
    }

    /**
     * Get top inspectors
     */
    private function getTopInspectors()
    {
        $inspectors = CarInspector::whereHas("inspections")
            ->withCount([
                "inspections as completed_count" => function ($query) {
                    $query->where("status", CarInspection::STATUS_COMPLETED);
                },
            ])
            ->with([
                "Inspections" => function ($query) {
                    $query
                        ->where("status", CarInspection::STATUS_COMPLETED)
                        ->whereNotNull("total_score");
                },
            ])
            ->orderByDesc("completed_count")
            ->limit(5)
            ->get();

        $top_inspectors = [];

        foreach ($inspectors as $inspector) {
            $avg_score = $inspector->inspections->avg("total_score") ?? 0;

            $top_inspectors[] = [
                "name" => $inspector->shop_name,
                "avatar" => $inspector->avatar,
                "completed_count" => $inspector->completed_count,
                "avg_score" => round($avg_score, 1),
            ];
        }

        return $top_inspectors;
    }

    /**
     * Get upcoming inspections
     */
    private function getUpcomingInspections()
    {
        $upcoming = CarInspection::with(["car", "inspector"])
            ->where("status", CarInspection::STATUS_PENDING)
            ->whereNotNull("scheduled_at")
            ->orderBy("scheduled_at", "asc")
            ->limit(5)
            ->get();

        $upcoming_inspections = [];

        foreach ($upcoming as $inspection) {
            $is_overdue = $inspection->scheduled_at < now();

            $upcoming_inspections[] = [
                "car_name" => $inspection->car->car_name ?? "N/A",
                "scheduled_date" => $inspection->scheduled_at->format(
                    "M d, Y H:i",
                ),
                "inspector_name" => $inspection->inspector->shop_name ?? null,
                "is_overdue" => $is_overdue,
            ];
        }

        return $upcoming_inspections;
    }

    /**
     * Get chart data
     */
    private function getChartData($period = 30)
    {
        $startDate = now()->subDays($period);
        $dates = [];
        $completed = [];
        $in_progress = [];
        $pending = [];

        for ($i = $period; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates[] = $date->format("M d");

            $completed[] = CarInspection::where(
                "status",
                CarInspection::STATUS_COMPLETED,
            )
                ->whereDate("completed_at", $date->toDateString())
                ->count();

            $in_progress[] = CarInspection::where(
                "status",
                CarInspection::STATUS_IN_PROGRESS,
            )
                ->whereDate("started_at", $date->toDateString())
                ->count();

            $pending[] = CarInspection::where(
                "status",
                CarInspection::STATUS_PENDING,
            )
                ->whereDate("created_at", $date->toDateString())
                ->count();
        }

        return [
            "labels" => $dates,
            "completed" => $completed,
            "in_progress" => $in_progress,
            "pending" => $pending,
        ];
    }

    /**
     * Get chart data via AJAX
     */
    public function chartData(Request $request)
    {
        $period = $request->get("period", 30);
        $data = $this->getChartData($period);

        return response()->json([
            "success" => true,
            "labels" => $data["labels"],
            "completed" => $data["completed"],
            "in_progress" => $data["in_progress"],
            "pending" => $data["pending"],
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CarInspection::with([
            "car.brand",
            "car.model",
            "inspectionType",
            "inspector",
            "requester",
            "payment",
        ]);

        // Search functionality
        if ($request->has("search") && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has("status") && $request->status !== "") {
            $query->byStatus($request->status);
        }

        // Filter by inspection type
        if (
            $request->has("inspection_type") &&
            $request->inspection_type !== ""
        ) {
            $query->byType($request->inspection_type);
        }

        // Filter by inspector
        if ($request->has("inspector") && $request->inspector !== "") {
            $query->byInspector($request->inspector);
        }

        // Filter by car
        if ($request->has("car") && $request->car !== "") {
            $query->byCar($request->car);
        }
        // Filter by date range
        if ($request->has("date") && !empty($request->date)) {
            $query
                ->where(
                    "created_at",
                    ">=",
                    date(
                        "Y-m-d",
                        strtotime(explode(" to ", $request->date)[0]),
                    ) . "  00:00:00",
                )
                ->where(
                    "created_at",
                    "<=",
                    date(
                        "Y-m-d",
                        strtotime(explode(" to ", $request->date)[1]),
                    ) . "  23:59:59",
                );
        }

        // Filter overdue inspections
        if ($request->has("overdue") && $request->overdue === "1") {
            $query->overdue();
        }

        // Sorting
        $sortField = $request->get("sort", "created_at");
        $sortDirection = $request->get("direction", "desc");

        if (
            in_array($sortField, [
                "inspection_number",
                "status",
                "scheduled_at",
                "created_at",
            ])
        ) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $inspections = $query->paginate(15)->appends(request()->query());

        if ($request->wantsJson()) {
            return response()->json([
                "success" => true,
                "data" => $inspections,
            ]);
        }

        // Get filter options
        $inspectionTypes = CarInspectionType::active()->ordered()->get();
        $inspectors = CarInspector::active()->get();

        $statuses = CarInspection::STATUSES;

        return view(
            "backend.cars.inspections.index",
            compact("inspections", "inspectionTypes", "inspectors", "statuses"),
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $cars = Car::with(["brand", "model"])->get();
        $inspectionTypes = CarInspectionType::active()->ordered()->get();
        $inspectors = CarInspector::active()->get();

        // Pre-fill car if provided
        $selectedCar = null;
        if ($request->has("car_id")) {
            $selectedCar = Car::with(["brand", "model"])->find(
                $request->car_id,
            );
        }

        return view(
            "backend.cars.inspections.create",
            compact("cars", "inspectionTypes", "inspectors", "selectedCar"),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "car_id" => "required|integer|exists:cars,id",
            "inspection_type_id" =>
                "required|integer|exists:car_inspection_types,id",
            "inspector_id" => "nullable|integer|exists:car_inspectors,id",
            "scheduled_at" => "nullable|date|after:now",
            "inspector_notes" => "nullable|string",
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
        $data["requested_by"] = Auth::id();

        try {
            $inspection = CarInspection::create($data);
            $inspection->load([
                "car.brand",
                "car.model",
                "inspectionType",
                "inspector",
                "requester",
            ]);

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Inspection created successfully",
                        "data" => $inspection,
                    ],
                    201,
                );
            }

            return redirect()
                ->route("admin.car-inspections.show", $inspection)
                ->with("success", "Inspection created successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to create inspection",
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
                    "Failed to create inspection: " . $e->getMessage(),
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CarInspection $carInspection)
    {
        $carInspection->load([
            "car.brand",
            "car.model",
            "car.category",
            "inspectionType.sections.fields",
            "inspector",
            "requester",
            "fieldValues.field.section",
            "payment",
        ]);

        // Get completion status for each section
        $sectionCompletions = [];
        foreach ($carInspection->inspectionType->sections as $section) {
            $sectionCompletions[
                $section->id
            ] = $carInspection->getSectionCompletion($section->id);
        }

        if (request()->wantsJson()) {
            return response()->json([
                "success" => true,
                "data" => $carInspection,
                "section_completions" => $sectionCompletions,
            ]);
        }

        return view(
            "backend.cars.inspections.show",
            compact("carInspection", "sectionCompletions"),
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CarInspection $carInspection)
    {
        if (!$carInspection->is_editable) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "This inspection cannot be edited",
                    ],
                    403,
                );
            }

            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("error", "This inspection cannot be edited");
        }

        $selectedCar = Car::with(["brand", "model"])->find($carInspection->car_id);
        $inspectionType = $carInspection->InspectionType;
        $inspectors = CarInspector::active()->get();
        $selectedInspector = $carInspection->inspector;

        $carInspection->load([
            "car.brand",
            "car.model",
            "inspectionType",
            "inspector",
            "requester",
        ]);

        return view(
            "backend.cars.inspections.edit",
            compact("carInspection", "selectedCar", "inspectionType", "inspectors", 'selectedInspector'),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarInspection $carInspection)
    {
        if (!$carInspection->is_editable) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "This inspection cannot be edited",
                    ],
                    403,
                );
            }
            flash()->error("This inspection cannot be edited");
            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("error", "This inspection cannot be edited");
        }

        $validator = Validator::make($request->all(), [
            "inspector_id" => "nullable|integer|exists:car_inspectors,id",
            "scheduled_at" => "nullable|date|after:now",
            "inspector_notes" => "nullable|string",
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
            flash()->error("Validation failed");
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // if Inspector is assigned and inspection is already paid, prevent changing inspector to avoid commission issues
        if (
            $carInspection->payment &&
            $carInspection->payment->status == PaymentStatusEnum::PAID &&
            $request->has("inspector_id") &&
            $carInspection->inspector_id != null &&
            $request->input("inspector_id") != $carInspection->inspector_id
        ) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Cannot change inspector for a paid inspection",
                    ],
                    403,
                );
            }
            flash()->error("Cannot change inspector for a paid inspection");
            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with(
                    "error",
                    "Cannot change inspector for a paid inspection",
                );
        }

        try {
            DB::beginTransaction();
            // Check if inspector is being assigned for the first time
            $had_inspector = $carInspection->inspector_id != null  ? true : false;

            $carInspection->update($validator->validated());

            // Dispatch event to calculate commission if new inspector is assigned and inspection is paid
            if ($carInspection->inspector_id && $carInspection->payment && $carInspection->payment->status == PaymentStatusEnum::PAID && !$had_inspector) {
                event(new CarInspectorAssigned($carInspection));
            }

            $carInspection->load([
                "car.brand",
                "car.model",
                "inspectionType",
                "inspector",
                "requester",
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection updated successfully",
                    "data" => $carInspection,
                ]);
            }
            flash()->success("Inspection updated successfully");
            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("success", "Inspection updated successfully");
        } catch (\Exception $e) {
                DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to update inspection",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }
            flash()->error("Failed to update inspection: " . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    "error",
                    "Failed to update inspection: " . $e->getMessage(),
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarInspection $carInspection)
    {
        if (in_array($carInspection->status, [CarInspection::STATUS_COMPLETED, CarInspection::STATUS_IN_PROGRESS, CarInspection::STATUS_PENDING])) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Cannot delete this inspection",
                    ],
                    403,
                );
            }
            flash()->error("Cannot delete this inspections");
            return redirect()
                ->route("admin.car-inspections.index");

        }

        if($carInspection->payment && $carInspection->payment->status == PaymentStatusEnum::PAID){
              if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "The inspection has a paid payment, please refund first",
                    ],
                    403,
                );
            }
            flash()->error("The inspection has a paid payment, please refund first");
            return redirect()
                ->route("admin.car-inspections.index");
        }

        try {
            $carInspection->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection deleted successfully",
                ]);
            }

            flash()->success("Inspection deleted successfully");
            return redirect()
                ->route("admin.car-inspections.index");

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to delete inspection",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->route("admin.car-inspections.index")
                ->with(
                    "error",
                    "Failed to delete inspection: " . $e->getMessage(),
                );
        }
    }

    /**
     * Start an inspection
     */
    public function start(Request $request, CarInspection $carInspection)
    {
        if (!$carInspection->can_start) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Inspection cannot be started",
                    ],
                    400,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Inspection cannot be started");
        }

        $validator = Validator::make($request->all(), [
            "inspector_id" => "nullable|integer|exists:users,id",
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
            $inspectorId = $request->input("inspector_id", Auth::id());
            $carInspection->start($inspectorId);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection started successfully",
                    "data" => $carInspection->load(["inspector"]),
                ]);
            }

            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("success", "Inspection started successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to start inspection",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to start inspection: " . $e->getMessage(),
                );
        }
    }

    public function conduct(CarInspection $carInspection)
    {
        // Load all necessary relationships
        $carInspection->load([
            "car.brand",
            "car.model",
            "car.category",
            "inspectionType.sections.fields",
            "inspector",
            "requester",
            "fieldValues.field.section",
        ]);

        // Calculate completion status for each section
        $section_completions = [];
        foreach ($carInspection->inspectionType->sections as $section) {
            $section_completions[
                $section->id
            ] = $carInspection->getSectionCompletion($section->id);
        }

        // Calculate overall progress (average of section completions)
        $overall_progress = 0;
        if (count($section_completions) > 0) {
            $overall_progress = round(
                array_sum(
                    array_column($section_completions, "completion_percentage"),
                ) / count($section_completions),
            );
        }

        return view("backend.cars.inspections.conduct", [
            "inspection" => $carInspection,
            "section_completions" => $section_completions,
            "overall_progress" => $overall_progress,
        ]);
    }

    /**
     * Get progress for a field value
     */
    public function progress(
        Request $request,
        CarInspection $carInspection
    ) {
        try {

        // Calculate completion status for each section
        $section_progress = [];
        foreach ($carInspection->inspectionType->sections as $section) {
            $section_progress[
                $section->id
            ] = $carInspection->getSectionCompletion($section->id);
        }

        // Calculate overall progress (average of section progress)
        $overall_progress = 0;
        if (count($section_progress) > 0) {
            $overall_progress = round(
                array_sum(
                    array_column($section_progress, "completion_percentage"),
                ) / count($section_progress),
            );
        }
            return response()->json([
                "success" => true,
                "message" => "Field value progress retrieved successfully",
                "overall_progress" => $overall_progress,
                "section_progress" => $section_progress,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve field value progress",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
    /**
     * Complete an inspection
     */
    public function complete(Request $request, CarInspection $carInspection)
    {
        if (!$carInspection->can_complete) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Inspection cannot be completed",
                    ],
                    400,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Inspection cannot be completed");
        }

        $validator = Validator::make($request->all(), [
            "total_score" => "nullable|numeric|min:0|max:100",
            "overall_condition" =>
                "nullable|in:excellent,good,fair,poor,critical",
            "inspector_notes" => "nullable|string",
            "recommendations" => "nullable|string",
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
            $data = $validator->validated();
            $carInspection->complete(
                $data["total_score"] ?? null,
                $data["overall_condition"] ?? null,
                $data["inspector_notes"] ?? null,
            );

            if (!empty($data["recommendations"])) {
                $carInspection->update([
                    "recommendations" => $data["recommendations"],
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection completed successfully",
                    "data" => $carInspection->fresh(),
                ]);
            }

            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("success", "Inspection completed successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to complete inspection",
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
                    "Failed to complete inspection: " . $e->getMessage(),
                );
        }
    }

    /**
     * Cancel an inspection
     */
    public function cancel(Request $request, CarInspection $carInspection)
    {
        if (!$carInspection->can_cancel) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Inspection cannot be cancelled",
                    ],
                    400,
                );
            }

            return redirect()
                ->back()
                ->with("error", "Inspection cannot be cancelled");
        }

        $validator = Validator::make($request->all(), [
            "reason" => "nullable|string|max:500",
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
            $carInspection->cancel($request->input("reason"));

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Inspection cancelled successfully",
                    "data" => $carInspection->fresh(),
                ]);
            }

            return redirect()
                ->route("admin.car-inspections.show", $carInspection)
                ->with("success", "Inspection cancelled successfully");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to cancel inspection",
                        "error" => $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to cancel inspection: " . $e->getMessage(),
                );
        }
    }

    /**
     * Generate inspection report
     */
    public function report(CarInspection $carInspection)
    {
        if ($carInspection->status !== CarInspection::STATUS_COMPLETED) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Report is only available for completed inspections",
                    ],
                    400,
                );
            }
            flash()->error(
                "Report is only available for completed inspections",
            );
            return redirect()
                ->back();
        }

        $carInspection->load([
            "car.brand",
            "car.model",
            "car.category",
            "inspectionType.sections.fields",
            "inspector",
            "requester",
            "fieldValues.field.section",
        ]);

        // Organize field values by section
        $sectionData = [];
        foreach ($carInspection->inspectionType->sections as $section) {
            $sectionData[$section->id] = [
                "section" => $section,
                "fields" => [],
                "completion" => $carInspection->getSectionCompletion(
                    $section->id,
                ),
            ];

            foreach ($section->fields as $field) {
                $fieldValue = $carInspection->fieldValues
                    ->where("field_id", $field->id)
                    ->first();

                $sectionData[$section->id]["fields"][] = [
                    "field" => $field,
                    "value" => $fieldValue,
                ];
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                "success" => true,
                "data" => [
                    "inspection" => $carInspection,
                    "sections" => $sectionData,
                    "generated_at" => now(),
                ],
            ]);
        }

        return view(
            "backend.cars.inspections.report",
            compact("carInspection", "sectionData"),
        );
    }

    /**
     * Download inspection report as PDF
     */
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

    /**
     * Set delivered to inspector true
     */

    public function deliverToInspector(CarInspection $carInspection)
    {
        if($carInspection->status != CarInspection::STATUS_PENDING) {
            flash()->error(translate("Only cars with pending inspections can be delivered to inspector"));
            return back();
        }

        $carInspection->delivered_to_inspector = true;
        $carInspection->save();

        flash()->success(translate("Successfully marked as delivered to Inspector"));
        return back();
    }
    /**
     * Bulk update status for multiple inspections
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "ids" => "required|array|min:1",
            "ids.*" => "integer|exists:car_inspections,id",
            "status" =>
                "required|in:pending,in_progress,completed,cancelled,failed",
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
            $updated = 0;
            $errors = [];

            foreach ($request->ids as $id) {
                $inspection = CarInspection::find($id);
                if ($inspection && $inspection->is_editable) {
                    $inspection->update(["status" => $request->status]);
                    $updated++;
                } else {
                    $errors[] = "Inspection {$inspection->inspection_number} cannot be updated";
                }
            }

            return response()->json([
                "success" => true,
                "message" => "{$updated} inspections updated successfully",
                "errors" => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update inspections",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * API endpoint for listing inspections
     */
    public function apiIndex(Request $request)
    {
        $query = CarInspection::with([
            "car.brand",
            "car.model",
            "inspectionType",
            "inspector",
            "requester",
        ]);

        // Apply filters
        if ($request->has("status")) {
            $query->byStatus($request->status);
        }

        if ($request->has("inspection_type_id")) {
            $query->byType($request->inspection_type_id);
        }

        if ($request->has("car_id")) {
            $query->byCar($request->car_id);
        }

        if ($request->has("inspector_id")) {
            $query->byInspector($request->inspector_id);
        }

        $inspections = $query->latest()->paginate(20);

        return response()->json([
            "success" => true,
            "data" => $inspections,
        ]);
    }
}
