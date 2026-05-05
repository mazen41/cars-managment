<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CarInspectorCollection;
use App\Http\Resources\V2\CarInspectorResource;
use App\Models\CarInspector;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CarInspectorController extends Controller
{
    /**
     * Get all active inspectors with optional filtering
     */
    public function index(Request $request): JsonResponse|ResourceCollection
    {
        $query = CarInspector::query()
            ->with(["user", "country", "state", "city"])
            ->where("is_active", true);

        // Apply search filter
        if ($request->has("search") && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas("user", function ($userQuery) use ($search) {
                    $userQuery->where("name", "like", "%{$search}%");
                })
                    ->orWhere("shop_name", "like", "%{$search}%")
                    ->orWhere("address", "like", "%{$search}%");
            });
        }

        // Apply location filter using direct relationships
        if ($request->has("country") && $request->country) {
            if (is_numeric($request->country)) {
                $query->where("country_id", $request->country);
            } else {
                $query->whereHas("country", function (Builder $q) use (
                    $request,
                ) {
                    $q->where("code", $request->country)->orWhere(
                        "name",
                        "like",
                        "%" . $request->country . "%",
                    );
                });
            }
        }

        if ($request->has("state") && $request->state) {
            if (is_numeric($request->state)) {
                $query->where("state_id", $request->state);
            } else {
                $query->whereHas("state", function (Builder $q) use ($request) {
                    $q->where("name", "like", "%" . $request->state . "%");
                });
            }
        }

        if ($request->has("city") && $request->city) {
            if (is_numeric($request->city)) {
                $query->where("city_id", $request->city);
            } else {
                $query->whereHas("city", function (Builder $q) use ($request) {
                    $q->where("name", "like", "%" . $request->city . "%");
                });
            }
        }

        // Apply experience filter
        if ($request->has("min_experience") && $request->min_experience) {
            $query->where("experience_years", ">=", $request->min_experience);
        }

        // Apply service filter
        if ($request->has("service") && $request->service) {
            $query->whereJsonContains("services_offered", $request->service);
        }

        // Apply nearby filter (if coordinates provided)
        if (
            $request->has("latitude") &&
            $request->has("longitude") &&
            $request->latitude &&
            $request->longitude
        ) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radius = $request->input("radius", 50); // Default 50km radius

            $query
                ->selectRaw(
                    "*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$lat, $lng, $lat],
                )
                ->having("distance", "<=", $radius)
                ->orderBy("distance");
        } else {
            // Default ordering
            $query->orderBy("created_at", "desc");
        }

        $perPage = $request->input("per_page", 15);
        $inspectors = $query->paginate($perPage);

        return  new CarInspectorCollection($inspectors);
    }

    /**
     * Get a specific inspector with detailed information
     */
    public function show(CarInspector $carInspector): JsonResponse | JsonResource
    {
        $carInspector->load([
            "user",
            "country",
            "state",
            "city",
            "inspections" => function ($query) {
                $query
                    ->with(["car", "inspectionType"])
                    ->latest()
                    ->limit(10);
            },
        ]);

        return new CarInspectorResource($carInspector);
    }

    /**
     * Get inspectors by country
     * @param Request $request
     * @param string|null $countryCode
     * @return JsonResponse
     */
    public function byCountry(
        Request $request,
        $countryCode = null,
    ): JsonResponse {
        // If country code is provided in URL, use it; otherwise get from request
        $country = $countryCode ?? $request->input("country");

        if (!$country) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Country parameter is required",
                ],
                400,
            );
        }

        // Validate and get country
        $countryModel = null;
        if (is_numeric($country)) {
            $countryModel = Country::find($country);
        } else {
            $countryModel = Country::where("code", $country)
                ->orWhere("name", "like", "%" . $country . "%")
                ->first();
        }

        if (!$countryModel) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid country specified",
                ],
                400,
            );
        }

        $query = CarInspector::query()
            ->with(["user", "country", "state", "city"])
            ->where("is_active", true)
            ->where("country_id", $countryModel->id);

        // Apply additional filters
        if ($request->has("search") && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas("user", function ($userQuery) use ($search) {
                    $userQuery->where("name", "like", "%{$search}%");
                })
                    ->orWhere("shop_name", "like", "%{$search}%")
                    ->orWhere("address", "like", "%{$search}%");
            });
        }

        if ($request->has("experience") && $request->experience) {
            $query->where("experience_years", ">=", $request->experience);
        }

        $perPage = $request->input("per_page", 15);
        $inspectors = $query->orderBy("created_at", "desc")->paginate($perPage);

        return response()->json([
            "success" => true,
            "data" => new CarInspectorCollection($inspectors),
            "filters" => [
                "country" => $countryModel->name,
                "country_id" => $countryModel->id,
                "country_code" => $countryModel->code,
                "total_count" => $inspectors->total(),
            ],
            "pagination" => [
                "current_page" => $inspectors->currentPage(),
                "last_page" => $inspectors->lastPage(),
                "per_page" => $inspectors->perPage(),
                "total" => $inspectors->total(),
                "from" => $inspectors->firstItem(),
                "to" => $inspectors->lastItem(),
            ],
        ]);
    }

    /**
     * Get inspectors by state/city
     * @param Request $request
     * @param string|null $stateId
     * @return JsonResponse
     */
    public function byState(Request $request, $stateId = null): JsonResponse | ResourceCollection
    {
        // If state ID is provided in URL, use it; otherwise get from request
        $state = $stateId ?? $request->input("state");

        if (!$state) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "State parameter is required",
                ],
                400,
            );
        }

        // Validate state exists
        $stateModel = null;
        if (is_numeric($state)) {
            $stateModel = State::find($state);
        } else {
            $stateModel = State::where(
                "name",
                "like",
                "%" . $state . "%",
            )->first();
        }

        if (!$stateModel) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid state specified",
                ],
                400,
            );
        }

        $query = CarInspector::query()
            ->with(["user", "country", "state", "city"])
            ->where("is_active", true)
            ->where("state_id", $stateModel->id);

        // Apply additional filters
        if ($request->has("search") && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas("user", function ($userQuery) use ($search) {
                    $userQuery->where("name", "like", "%{$search}%");
                })
                    ->orWhere("shop_name", "like", "%{$search}%")
                    ->orWhere("address", "like", "%{$search}%");
            });
        }

        if ($request->has("experience") && $request->experience) {
            $query->where("experience_years", ">=", $request->experience);
        }

        if ($request->has("service") && $request->service) {
            $query->whereJsonContains("services_offered", $request->service);
        }

        $perPage = $request->input("per_page", 15);
        $inspectors = $query->orderBy("created_at", "desc")->paginate($perPage);

        return new CarInspectorCollection($inspectors);
    }

    /**
     * Get inspector statistics and summary
     */
    public function statistics(): JsonResponse
    {
        $totalInspectors = CarInspector::count();
        $activeInspectors = CarInspector::where("is_active", true)->count();
        $inactiveInspectors = CarInspector::where("is_active", false)->count();

        $inspectorsByCountry = CarInspector::where("is_active", true)
            ->with("country")
            ->get()
            ->groupBy(function ($inspector) {
                return $inspector->country->name ?? "Unknown";
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        $inspectorsByState = CarInspector::where("is_active", true)
            ->with("state")
            ->get()
            ->groupBy(function ($inspector) {
                return $inspector->state->name ?? "Unknown";
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        $avgExperience = CarInspector::where("is_active", true)
            ->whereNotNull("experience_years")
            ->avg("experience_years");

        return response()->json([
            "success" => true,
            "data" => [
                "totals" => [
                    "total_inspectors" => $totalInspectors,
                    "active_inspectors" => $activeInspectors,
                    "inactive_inspectors" => $inactiveInspectors,
                ],
                "by_country" => $inspectorsByCountry,
                "by_state" => $inspectorsByState,
                "average_experience_years" => round($avgExperience ?? 0, 1),
            ],
        ]);
    }

    /**
     * Search inspectors with advanced filtering
     */
    public function search(Request $request): JsonResponse | ResourceCollection
    {
        if (!$request->has("query") || !$request->query) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Search query is required",
                ],
                400,
            );
        }

        $searchQuery = $request->input('query');
        $query = CarInspector::query()
            ->with(["user", "country", "state", "city"])
            ->where("is_active", true)
            ->where(function ($q) use ($searchQuery) {
                $q->whereHas("user", function ($userQuery) use ($searchQuery) {
                    $userQuery->where("name", "like", "%{$searchQuery}%");
                })
                    ->orWhere("shop_name", "like", "%{$searchQuery}%")
                    ->orWhere("address", "like", "%{$searchQuery}%");
            });

        // Apply filters using direct relationships
        if ($request->has("country") && $request->country) {
            if (is_numeric($request->country)) {
                $query->where("country_id", $request->country);
            } else {
                $query->whereHas("country", function (Builder $q) use (
                    $request,
                ) {
                    $q->where("code", $request->country)->orWhere(
                        "name",
                        "like",
                        "%" . $request->country . "%",
                    );
                });
            }
        }

        if ($request->has("state") && $request->state) {
            if (is_numeric($request->state)) {
                $query->where("state_id", $request->state);
            } else {
                $query->whereHas("state", function (Builder $q) use ($request) {
                    $q->where("name", "like", "%" . $request->state . "%");
                });
            }
        }

        if ($request->has("city") && $request->city) {
            if (is_numeric($request->city)) {
                $query->where("city_id", $request->city);
            } else {
                $query->whereHas("city", function (Builder $q) use ($request) {
                    $q->where("name", "like", "%" . $request->city . "%");
                });
            }
        }

        if ($request->has("min_experience") && $request->min_experience) {
            $query->where("experience_years", ">=", $request->min_experience);
        }

        if ($request->has("services") && $request->services) {
            $services = is_array($request->services)
                ? $request->services
                : [$request->services];
            foreach ($services as $service) {
                $query->whereJsonContains("services_offered", $service);
            }
        }

        $perPage = $request->input("per_page", 15);
        $inspectors = $query->orderBy("created_at", "desc")->paginate($perPage);

        return new CarInspectorCollection($inspectors);
    }
}
