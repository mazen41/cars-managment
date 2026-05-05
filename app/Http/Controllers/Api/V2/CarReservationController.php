<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\CarReservationResource;
use App\Models\Car;
use App\Models\CarReservation;
use App\Enums\CarStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's reservations
     */
    public function index(Request $request): JsonResponse | ResourceCollection
    {
        $query = CarReservation::where('user_id', Auth::id())
            ->with(['car.brand', 'car.model', 'car.color']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'reserved_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reservations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CarReservationResource::collection($reservations),
            'message' => 'Reservations retrieved successfully'
        ]);
    }

    /**
     * Show a specific reservation
     */
    public function show(CarReservation $carReservation): JsonResponse
    {
        // Check if user owns this reservation
        if ($carReservation->user_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this reservation'
            ], 403);
        }

        $carReservation->load([
            'car.brand',
            'car.model',
            'car.color',
            'car.category',
            'user'
        ]);

        return response()->json([
            'success' => true,
            'data' => new CarReservationResource($carReservation),
            'message' => 'Reservation details retrieved successfully'
        ]);
    }

    /**
     * Create a new car reservation
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'car_id' => [
                'required',
                'exists:cars,id',
                function ($attribute, $value, $fail) {
                    $car = Car::find($value);
                    if ($car && !$car->canBeReserved()) {
                        $fail('This car is not available for reservation.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $car = Car::findOrFail($request->car_id);

            if (!$car->canBeReserved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This car is not available for reservation'
                ], 400);
            }

            // Check if user already has an active reservation for this car
            $existingReservation = CarReservation::where('user_id', Auth::id())
                ->where('car_id', $car->id)
                ->active()
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active reservation for this car'
                ], 400);
            }
            $reservation = $car->createReservation(
                Auth::id(),
                $request->notes,
            );

            DB::commit();

            $reservation->load(['car.brand', 'car.model', 'user']);

            return response()->json([
                'success' => true,
                'reservation_id' => $reservation->id,
                'message' => 'Car reserved successfully! You will be contacted shortly.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     */
    public function cancel(Request $request, CarReservation $carReservation): JsonResponse
    {
        // Check if user owns this reservation
        if ($carReservation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this reservation'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$carReservation->can_be_cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'This reservation cannot be cancelled'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $carReservation->cancel(
                $request->cancellation_reason,
                Auth::id()
            );
            $carReservation->car()->update(['car_status' => CarStatusEnum::AVAILABLE]);
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new CarReservationResource($carReservation->fresh()),
                'message' => 'Reservation cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel reservation'
            ], 500);
        }
    }

    /**
     * Check if a car can be reserved
     */
    public function checkAvailability(Car $car): JsonResponse
    {
        $car->load(['activeReservations.user', 'brand', 'model']);

        return response()->json([
            'success' => true,
            'data' => [
                'car_id' => $car->id,
                'can_be_reserved' => $car->canBeReserved(),
                'is_reserved' => $car->isReserved(),
                'is_sold' => $car->isSold(),
                'status' => $car->status,
                'reservation_status' => $car->reservation_status,
                'current_reservation' => $car->currentReservation(),
                'suggested_amount' => $car->price ? number_format($car->price * 0.1, 2, '.', '') : null
            ],
            'message' => 'Car availability checked successfully'
        ]);
    }
}
