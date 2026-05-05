<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CarInspector;
use App\Models\CarInspectorPaymentHistory;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CarInspectorController extends Controller
{
    public function __construct()
    {
        $this->middleware("permission:view_car_inspectors")->only([
            "index",
            "show",
        ]);
        $this->middleware("permission:create_car_inspectors")->only([
            "create",
            "store",
        ]);
        $this->middleware("permission:edit_car_inspectors")->only([
            "edit",
            "update",
        ]);
        $this->middleware("permission:delete_car_inspectors")->only([
            "destroy",
        ]);
        $this->middleware("permission:manage_car_inspector_payments")->only([
            "all_payments",
            "payments",
            "makePayment",
            "paymentHistory",
        ]);
    }

    /**
     * Display a listing of car inspectors.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $status = $request->get("status");
        $sort = $request->get("sort", "created_at");
        $order = $request->get("order", "desc");

        $inspectors = CarInspector::with([
            "user",
            "country",
            "state",
            "city",
            "paymentHistory",
        ])
            ->when($search, function ($query) use ($search) {
                return $query->search($search);
            })
            ->when($status !== null, function ($query) use ($status) {
                if ($status === "active") {
                    return $query->active();
                } elseif ($status === "inactive") {
                    return $query->inactive();
                }
            })
            ->orderBy($sort, $order)
            ->paginate(15);

        return view(
            "backend.cars.inspectors.index",
            compact("inspectors", "search", "status", "sort", "order"),
        );
    }

    /**
     * Show the form for creating a new car inspector.
     */
    public function create()
    {
        $countries = Country::where("status", 1)->orderBy("name")->get();
        $states = State::orderBy("name")->get();
        $cities = City::orderBy("name")->get();

        return view(
            "backend.cars.inspectors.create",
            compact("countries", "states", "cities"),
        );
    }

    /**
     * Store a newly created car inspector.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:8|confirmed",
            "country_code" => "required|string|max:10",
            "phone" => "required|string|max:9|min:9|unique:users,phone",
            "shop_name" => "required|string|max:255",
            "address" => "required|string",
            "country_id" => "required|exists:countries,id",
            "state_id" => "nullable|exists:states,id",
            "city_id" => "nullable|exists:cities,id",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",
            "description" => "nullable|string",
            "certification_number" => "nullable|string|max:100",
            "experience_years" => "nullable|integer|min:0|max:50",
            "image" => "nullable|string",
            "banner_image" => "nullable|string",
            "working_hours" => "nullable|array",
            "services_offered" => "nullable|array",
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "user_type" => "car_inspector",
                "phone" => $request->country_code . $request->phone,
                "email_verified_at" => now(),
            ]);

            // Assign car_inspector role
            $role = Role::where("name", "car_inspector")->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create car inspector profile
            $carInspector = CarInspector::create([
                "user_id" => $user->id,
                "shop_name" => $request->shop_name,
                "address" => $request->address,
                "country_id" => $request->country_id,
                "state_id" => $request->state_id,
                "city_id" => $request->city_id,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "phone" => $request->phone,
                "email" => $request->email,
                "description" => $request->description,
                "certification_number" => $request->certification_number,
                "experience_years" => $request->experience_years,
                "image" => $request->image,
                "banner_image" => $request->banner_image,
                "working_hours" => $request->working_hours,
                "services_offered" => $request->services_offered,
                "is_active" => true,
                "admin_to_pay" => 0,
            ]);

            DB::commit();

            flash(translate("Car inspector created successfully!"))->success();
            return redirect()->route("admin.car-inspectors.index");
        } catch (\Exception $e) {
            DB::rollback();
            flash(
                translate("Error creating car inspector: ") . $e->getMessage(),
            )->error();
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified car inspector.
     */
    public function show(CarInspector $carInspector)
    {
        $carInspector->load([
            "user",
            "country",
            "state",
            "city",
            "paymentHistory.processedBy",
            "inspections.car",
        ]);

        $stats = [
            "total_inspections" => $carInspector->inspections()->count(),
            "pending_inspections" => $carInspector
                ->pendingInspections()
                ->count(),
            "completed_inspections" => $carInspector
                ->completedInspections()
                ->count(),
            "total_earnings" => $carInspector
                ->paymentHistory()
                ->earnings()
                ->completed()
                ->sum("amount"),
            "total_paid" => $carInspector
                ->paymentHistory()
                ->payments()
                ->completed()
                ->sum("amount"),
            "balance_owed" => $carInspector->admin_to_pay,
        ];

        $recentPayments = $carInspector
            ->paymentHistory()
            ->with("processedBy")
            ->latest()
            ->take(10)
            ->get();

        return view(
            "backend.cars.inspectors.show",
            compact("carInspector", "stats", "recentPayments"),
        );
    }

    /**
     * Show the form for editing the specified car inspector.
     */
    public function edit(CarInspector $carInspector)
    {
        $carInspector->load(["user", "country", "state", "city"]);
        $countries = Country::where("status", 1)->orderBy("name")->get();
        $states = State::orderBy("name")->get();
        $cities = City::orderBy("name")->get();

        return view(
            "backend.cars.inspectors.edit",
            compact("carInspector", "countries", "states", "cities"),
        );
    }

    /**
     * Update the specified car inspector.
     */
    public function update(Request $request, CarInspector $carInspector)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" =>
                "required|email|unique:users,email," . $carInspector->user_id,
            "phone" => "required|string|max:20",
            "shop_name" => "required|string|max:255",
            "address" => "required|string",
            "country_id" => "required|exists:countries,id",
            "state_id" => "nullable|exists:states,id",
            "city_id" => "nullable|exists:cities,id",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",
            "description" => "nullable|string",
            "certification_number" => "nullable|string|max:100",
            "experience_years" => "nullable|integer|min:0|max:50",
            "image" => "nullable|string",
            "banner_image" => "nullable|string",
            "working_hours" => "nullable|array",
            "services_offered" => "nullable|array",
            "is_active" => "boolean",
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user
            $carInspector->user->update([
                "name" => $request->name,
                "email" => $request->email,
                "phone" => $request->country_code . $request->phone,
            ]);

            // Update password if provided
            if ($request->filled("password")) {
                $carInspector->user->update([
                    "password" => Hash::make($request->password),
                ]);
            }

            // Update car inspector profile
            $carInspector->update([
                "shop_name" => $request->shop_name,
                "address" => $request->address,
                "country_id" => $request->country_id,
                "state_id" => $request->state_id,
                "city_id" => $request->city_id,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "phone" => $request->phone,
                "email" => $request->email,
                "description" => $request->description,
                "certification_number" => $request->certification_number,
                "experience_years" => $request->experience_years,
                "image" => $request->image,
                "banner_image" => $request->banner_image,
                "working_hours" => $request->working_hours,
                "services_offered" => $request->services_offered,
                "is_active" => $request->boolean("is_active"),
            ]);

            DB::commit();

            flash(translate("Car inspector updated successfully!"))->success();
            return redirect()->route(
                "admin.car-inspectors.show",
                $carInspector,
            );
        } catch (\Exception $e) {
            DB::rollback();
            flash(
                translate("Error updating car inspector: ") . $e->getMessage(),
            )->error();
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified car inspector.
     */
    public function destroy(CarInspector $carInspector)
    {
        try {
            DB::beginTransaction();

            // Check if inspector has pending inspections
            if ($carInspector->pendingInspections()->count() > 0) {
                flash(
                    translate(
                        "Cannot delete inspector with pending inspections.",
                    ),
                )->error();
                return redirect()->back();
            }

            // Soft delete the car inspector
            $carInspector->delete();

            // Optionally deactivate the user instead of deleting
            $carInspector->user->update([
                "user_type" => "deactivated_car_inspector",
            ]);

            DB::commit();

            flash(translate("Car inspector deleted successfully!"))->success();
            return redirect()->route("admin.car-inspectors.index");
        } catch (\Exception $e) {
            DB::rollback();
            flash(
                translate("Error deleting car inspector: ") . $e->getMessage(),
            )->error();
            return redirect()->back();
        }
    }
    /**
     * Display all payment history for car inspectors.
     */
    public function all_payments(Request $request)
    {
        $search = $request->get("search");
        $sort = $request->get("sort", "created_at");
        $order = $request->get("order", "desc");
        $payments = CarInspectorPaymentHistory::with([
            "carInspector",
            "carInspector.user",
            "processedBy",
        ])
            ->when($search, function ($query) use ($search) {
                return $query->search($search);
            })
            ->orderBy($sort, $order)
            ->paginate(20);
        return view(
            "backend.cars.inspectors.all-payments",
            compact("payments", "search", "sort", "order"),
        );
    }
    /**
     * Display payment history for a car inspector.
     */
    public function payments(CarInspector $carInspector)
    {
        $payments = $carInspector
            ->paymentHistory()
            ->with("processedBy")
            ->latest()
            ->paginate(20);

        return view(
            "backend.cars.inspectors.payments",
            compact("carInspector", "payments"),
        );
    }

    /**
     * Show the form for making a payment to the inspector.
     */
    public function showPaymentForm(CarInspector $carInspector)
    {
        if ($carInspector->admin_to_pay <= 0) {
            flash(translate("No amount owed to this inspector."))->warning();
            return redirect()->back();
        }

        return view(
            "backend.cars.inspectors.make-payment",
            compact("carInspector"),
        );
    }

    /**
     * Process payment to the inspector.
     */
    public function makePayment(Request $request, CarInspector $carInspector)
    {
        $validator = Validator::make($request->all(), [
            "amount" =>
                "required|numeric|min:0.01|max:" . $carInspector->admin_to_pay,
            "payment_method" => "required|string|max:100",
            "payment_details" => "nullable|array",
            "description" => "nullable|string|max:500",
            "transaction_reference" => "required|string|max:100",
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $payment = $carInspector->processPayment(
                $request->amount,
                $request->payment_method,
                $request->payment_details,
                $request->description,
            );

            if ($request->transaction_reference) {
                $payment->update([
                    "transaction_reference" => $request->transaction_reference,
                ]);
            }

            DB::commit();

            flash(translate("Payment processed successfully!"))->success();
            return redirect()->route(
                "admin.car-inspectors.show",
                $carInspector,
            );
        } catch (\Exception $e) {
            DB::rollback();
            flash(
                translate("Error processing payment: ") . $e->getMessage(),
            )->error();
            return redirect()->back()->withInput();
        }
    }

    /**
     * Bulk update inspector status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspector_ids" => "required|array",
            "inspector_ids.*" => "exists:car_inspectors,id",
            "status" => "required|boolean",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Invalid data provided.",
            ]);
        }

        try {
            CarInspector::whereIn("id", $request->inspector_ids)->update([
                "is_active" => $request->status,
            ]);

            $message = $request->status
                ? "Inspectors activated successfully!"
                : "Inspectors deactivated successfully!";
            return response()->json([
                "success" => true,
                "message" => translate($message),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error updating inspectors: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Export inspectors data.
     */
    public function export(Request $request)
    {
        // Implementation for exporting inspectors data
        //TODO
        flash(translate("Export functionality will be implemented."))->info();
        return redirect()->back();
    }

    /**
     * Get payment details for AJAX request.
     */
    public function paymentDetails($paymentId)
    {
        $payment = CarInspectorPaymentHistory::with([
            "carInspector.user",
            "processedBy",
        ])->findOrFail($paymentId);

        $html = view(
            "backend.cars.inspectors.partials.payment-details",
            compact("payment"),
        )->render();

        return response()->json(["html" => $html]);
    }

    /**
     * Update payment status via AJAX.
     */
    public function updatePaymentStatus(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|in:pending,completed,failed,cancelled",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Invalid status provided.",
            ]);
        }

        try {
            $payment = CarInspectorPaymentHistory::findOrFail($paymentId);

            $payment->update(["status" => $request->status]);

            $message = translate("Payment status updated successfully!");
            return response()->json(["success" => true, "message" => $message]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" =>
                    "Error updating payment status: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the settings view
     */

    public function settings()
    {
        return view("backend.cars.inspectors.inspector-settings");
    }

    /**
     * Get states by country for AJAX
     */
    public function getStates($countryId)
    {
        $states = State::where("country_id", $countryId)
            ->orderBy("name")
            ->get(["id", "name"]);

        return response()->json($states);
    }

    /**
     * Get cities by state for AJAX
     */
    public function getCities($stateId)
    {
        $cities = City::where("state_id", $stateId)
            ->orderBy("name")
            ->get(["id", "name"]);

        return response()->json($cities);
    }
}
