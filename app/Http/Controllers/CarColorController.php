<?php

namespace App\Http\Controllers;

use App\Models\CarColor;
use App\Models\CarColorTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarColorController extends Controller
{
    /**
     * Display a listing of the car colors.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarColor::withCount(["cars"]);

        // Apply filters
        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where("name", "LIKE", "%{$search}%");
        }

        // Sorting
        $sortBy = $request->get("sort_by", "name");
        $sortOrder = $request->get("sort_order", "asc");
        $query->orderBy($sortBy ?? 'name', $sortOrder ?? 'asc');

        // Pagination
        $perPage = $request->get("per_page", 15);
        $colors = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(["colors" => $colors]);
        }

        return view("backend.cars.colors.index", compact("colors"));
    }

    /**
     * Show the form for creating a new car color.
     */
    public function create(): View
    {
        $availableLanguages = config("app.available_languages", ["en", "ar"]);
        return view(
            "backend.cars.colors.create",
            compact("availableLanguages"),
        );
    }

    /**
     * Store a newly created car color in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "hex_code" =>
                [
                    'nullable',
                    'string',
                    'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i'
                ],
            "status" => "required|in:active,inactive",
            "translations" => "nullable|array",
            "translations.*.lang" => "required|string|max:10",
            "translations.*.name" => "required|string|max:255",
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(
                    ["errors" => $validator->errors()],
                    422,
                );
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $colorData = $request->only(["name", "hex_code", "status"]);
            $color = CarColor::create($colorData);

            // Handle translations if provided
            if ($request->has("translations")) {
                foreach ($request->translations as $translation) {
                    if (!empty($translation["name"])) {
                        CarColorTranslation::create([
                            "car_color_id" => $color->id,
                            "lang" => $translation["lang"],
                            "name" => $translation["name"],
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(
                    [
                        "message" => "Car color created successfully",
                        "color" => $color->load("translations"),
                    ],
                    201,
                );
            }

            flash()->success("Car color created successfully");
            return redirect()->route("admin.car-colors.index");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to create car color"],
                    500,
                );
            }
            flash()->error("Failed to create car color");
            return back()
                ->with("error", "Failed to create car color")
                ->withInput();
        }
    }

    /**
     * Display the specified car color.
     */
    public function show(CarColor $carColor): View
    {
        $carColor->load(["translations", "cars"]);
        return view("backend.cars.colors.show", compact("carColor"));
    }

    /**
     * Show the form for editing the specified car color.
     */
    public function edit(CarColor $carColor): View
    {
        $carColor->load("translations");
        $availableLanguages = config("app.available_languages", ["en", "ar"]);
        return view(
            "backend.cars.colors.edit",
            compact("carColor", "availableLanguages"),
        );
    }

    /**
     * Update the specified car color in storage.
     */
    public function update(
        Request $request,
        CarColor $carColor,
    ): RedirectResponse|JsonResponse {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "hex_code" =>
             [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i'
            ],
            "status" => "required|in:active,inactive",
            "translations" => "nullable|array",
            "translations.*.lang" => "required|string|max:10",
            "translations.*.name" => "required|string|max:255",
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(
                    ["errors" => $validator->errors()],
                    422,
                );
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $colorData = $request->only(["name", "hex_code", "status"]);

            if($request->lang && $request->lang != app()->getLocale()){
                unset($colorData['name']);
            }


            $carColor->update($colorData);

            $carColor->translate(
                ['lang' => $request->lang?? app()->getLocale()],
                ['name' => $request->name]
            );


            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    "message" => "Car color updated successfully",
                    "color" => $carColor->load("translations"),
                ]);
            }

            flash()->success("Car color updated successfully");
            return redirect()->route("admin.car-colors.index");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to update car color"],
                    500,
                );
            }
            flash()->error("Failed to update car color");
            return back()
                ->with("error", "Failed to update car color")
                ->withInput();
        }
    }

    /**
     * Remove the specified car color from storage.
     */
    public function destroy(CarColor $carColor): RedirectResponse|JsonResponse
    {
        // Check if color has cars
        if ($carColor->cars()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json(
                    [
                        "error" =>
                            "Cannot delete color that is assigned to cars",
                    ],
                    422,
                );
            }
            flash()->error("Cannot delete color that is assigned to cars");
            return back();
        }

        try {
            DB::beginTransaction();

            // Delete translations
            $carColor->translations()->delete();

            // Delete the color
            $carColor->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    "message" => "Car color deleted successfully",
                ]);
            }

            flash()->success("Car color deleted successfully");
            return redirect()->route("admin.car-colors.index");
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(
                    ["error" => "Failed to delete car color"],
                    500,
                );
            }
            flash()->error("Failed to delete car color");
            return back();
        }
    }

    /**
     * Toggle color status.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $statusMap = [
            "active" => "active",
            "inactive" => "inactive",
        ];

        $newStatus = $statusMap[$request->status] ?? "active";
        $carColor = CarColor::find($request->id);

        if (!$carColor) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Color not found",
                ],
                404,
            );
        }

        $carColor->update(["status" => $newStatus]);

        return response()->json([
            "success" => true,
            "message" => "Color status updated successfully",
            "status" => $newStatus,
        ]);
    }

    /**
     * Get colors for API/AJAX requests.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = CarColor::where("status", "active");

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where("name", "LIKE", "%{$search}%");
        }

        $colors = $query->orderBy("name", "asc")->get();

        return response()->json(["colors" => $colors]);
    }

    /**
     * Bulk update colors status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "color_ids" => "required|string",
            "status" => "required|in:active,inactive",
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        try {
            $colorIds = explode(",", $request->color_ids);

            CarColor::whereIn("id", $colorIds)->update([
                "status" => $request->status,
            ]);

            return response()->json([
                "message" => "Colors status updated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ["error" => "Failed to update colors status"],
                500,
            );
        }
    }

    /**
     * Get color statistics.
     */
    public function statistics(CarColor $carColor): JsonResponse
    {
        $stats = [
            "total_cars" => $carColor->cars()->count(),
            "published_cars" => $carColor
                ->cars()
                ->where("status", "published")
                ->count(),
            "draft_cars" => $carColor
                ->cars()
                ->where("status", "draft")
                ->count(),
            "new_cars" => $carColor->cars()->where("condition", "new")->count(),
            "used_cars" => $carColor
                ->cars()
                ->where("condition", "used")
                ->count(),
            "average_price" => $carColor
                ->cars()
                ->where("status", "published")
                ->avg("price"),
            "recent_cars" => $carColor
                ->cars()
                ->where("status", "published")
                ->with(["brand", "model", "category"])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(["statistics" => $stats]);
    }
}
