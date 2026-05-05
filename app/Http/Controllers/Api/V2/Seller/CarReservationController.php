<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seller\CarReservationResource;
use App\Http\Requests\Api\V2\Seller\CarReservationConfirmRequest;
use App\Http\Requests\Api\V2\Seller\CarReservationCancelRequest;
use App\Http\Requests\Api\V2\Seller\CarReservationMarkAsSoldRequest;
use App\Http\Requests\Api\V2\Seller\CarIdValidationRequest;
use App\Models\Car;
use App\Models\CarReservation;
use App\Enums\CarStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\CarStatusChanged;
use App\Traits\SellerCarOwnership;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CarReservationController extends Controller
{
    use SellerCarOwnership, ApiResponseTrait;
    /**
     * Display a listing of reservations for All seller's cars.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function indexAll(Request $request) : JsonResponse
    {
        if (!Auth::check()) {
            return $this->unauthorizedResponse('Authentication required');
        }

        $reservations = CarReservation::query();

        if($request->filled('status')) {
            $reservations->where('status', $request->status);
        }

        $reservations = $reservations->whereHas('car', function($query) {
            $query->where('user_id', auth('api')->user()->id);
        })
        ->with([
            'car', 'user', 'payment'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => CarReservationResource::collection($reservations),
                'pagination' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total(),
                    'from' => $reservations->firstItem(),
                    'to' => $reservations->lastItem(),
                    'has_more_pages' => $reservations->hasMorePages(),
                    'next_page_url' => $reservations->nextPageUrl(),
                    'prev_page_url' => $reservations->previousPageUrl(),
                ],
        ]);
    }

     /**
     * Display a listing of reservations for a specific car.
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

            // Get reservations for the car with related data
            $reservations = CarReservation::query();

            // Filter by status
             if($request->filled('status')) {
                $reservations->where('status', $request->status);
            }
            $reservations = $reservations->where('car_id', $carId)
                ->with(['car', 'user', 'payment'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

           return response()->json([
            'success' => true,
            'data' => CarReservationResource::collection($reservations),
                'pagination' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total(),
                    'from' => $reservations->firstItem(),
                    'to' => $reservations->lastItem(),
                    'has_more_pages' => $reservations->hasMorePages(),
                    'next_page_url' => $reservations->nextPageUrl(),
                    'prev_page_url' => $reservations->previousPageUrl(),
                ],
        ]);

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve reservations: ' . $e->getMessage(), [
                'car_id' => $carId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to retrieve reservations');
        }
    }

    /**
     * Display the specified reservation.
     *
     * @param CarReservation $carReservation
     * @return JsonResponse
     */
    public function show(CarReservation $carReservation): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Load car relationship for ownership verification
            $carReservation->load('car');

            // Verify car ownership through reservation
            if (!$this->verifyReservationOwnership($carReservation)) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Load related data for detailed view
            $carReservation->load(['car', 'user', 'payment', 'cancelledBy']);

            return $this->successResponse(
                new CarReservationResource($carReservation),
                'Reservation details retrieved successfully'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve reservation details: ' . $e->getMessage(), [
                'reservation_id' => $carReservation->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to retrieve reservation details');
        }
    }

    /**
     * Confirm a pending reservation.
     *
     * @param CarReservationConfirmRequest $request
     * @return JsonResponse
     */
    public function confirm(CarReservationConfirmRequest $request): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            $reservation = CarReservation::with(['car', 'user', 'payment'])
                ->find($request->reservation_id);

            if (!$reservation) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Verify car ownership
            if (!$this->verifyReservationOwnership($reservation)) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Validate state transition
            if ($reservation->status !== CarReservation::STATUS_PENDING) {
                return $this->validationErrorResponse([
                    'status' => ['Only pending reservations can be confirmed. Current status: ' . $reservation->status]
                ], 'Invalid reservation status for confirmation');
            }

            // Check if car is available for reservation
            if ($reservation->car->car_status->getValue() !== CarStatusEnum::AVAILABLE) {
                return $this->validationErrorResponse([
                    'car_status' => ['Car is not available for reservation. Current status: ' . $reservation->car->car_status->getValue()]
                ], 'Car is not available for reservation');
            }

            // check if payment is not paid
            if ($reservation->payment->status !== PaymentStatusEnum::PAID) {
                return $this->errorResponse('Cannot confirm reservation. Payment is not marked as paid.', 400);
            }

            DB::transaction(function () use ($reservation, $request) {
                // Confirm the reservation using model method
                $oldCarStatus = $reservation->car->car_status->getValue();

                $reservation->confirm($request->admin_notes);

            DB::commit();

            // Dispatch event if car status changed to reserved
            if ($reservation->car->car_status->getValue() !== $oldCarStatus) {
                event(new CarStatusChanged(
                    $reservation->car,
                    $oldCarStatus,
                    $reservation->car->car_status->getValue(),
                    'Car reserved through reservation confirmation'
                ));
            }
            });

            // Reload with fresh data
            $reservation->refresh();
            $reservation->load(['car', 'user', 'payment', 'cancelledBy']);

            return $this->successResponse(
                new CarReservationResource($reservation),
                'Reservation confirmed successfully'
            );

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Failed to confirm reservation: ' . $e->getMessage(), [
                'reservation_id' => $request->reservation_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to confirm reservation');
        }
    }

    /**
     * Cancel an active reservation.
     *
     * @param CarReservationCancelRequest $request
     * @return JsonResponse
     */
    public function cancel(CarReservationCancelRequest $request): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            $reservation = CarReservation::with(['car', 'user', 'payment'])
                ->find($request->reservation_id);

            if (!$reservation) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Verify car ownership
            if (!$this->verifyReservationOwnership($reservation)) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Validate state transition
            if (!$reservation->can_be_cancelled) {
                return $this->validationErrorResponse([
                    'status' => ['This reservation cannot be cancelled. Current status: ' . $reservation->status]
                ], 'Reservation cannot be cancelled');
            }

            DB::transaction(function () use ($reservation, $request) {
                // Cancel the reservation using model method
                $reservation->cancel($request->cancellation_reason, Auth::id());

                // If car was reserved, make it available again
                if ($reservation->car->car_status->getValue() === CarStatusEnum::RESERVED) {
                    $reservation->car->update(['car_status' => CarStatusEnum::AVAILABLE]);
                }
            });

            // Reload with fresh data
            $reservation->refresh();
            $reservation->load(['car', 'user', 'payment', 'cancelledBy']);

            return $this->successResponse(
                new CarReservationResource($reservation),
                'Reservation cancelled successfully'
            );

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Failed to cancel reservation: ' . $e->getMessage(), [
                'reservation_id' => $request->reservation_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to cancel reservation');
        }
    }

    /**
     * Mark reservation as sold (completed).
     *
     * @param CarReservationMarkAsSoldRequest $request
     * @return JsonResponse
     */
    public function markAsSold(CarReservationMarkAsSoldRequest $request): JsonResponse
    {
        try {
            // Verify authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse('Authentication required');
            }

            $reservation = CarReservation::with(['car', 'user', 'payment'])
                ->find($request->reservation_id);

            if (!$reservation) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Verify car ownership
            if (!$this->verifyReservationOwnership($reservation)) {
                return $this->notFoundResponse('Reservation not found');
            }

            // Validate state transition - only confirmed reservations can be marked as sold
            if ($reservation->status !== CarReservation::STATUS_CONFIRMED) {
                return $this->validationErrorResponse([
                    'status' => ['Only confirmed reservations can be marked as sold. Current status: ' . $reservation->status]
                ], 'Invalid reservation status for completion');
            }

            DB::transaction(function () use ($reservation) {
                $oldCarStatus = $reservation->car->car_status->getValue();

                // Complete the reservation using model method
                $reservation->complete();

                // Dispatch event if car status changed to sold
                if ($reservation->car->car_status->getValue() !== $oldCarStatus) {
                    event(new CarStatusChanged(
                        $reservation->car,
                        $oldCarStatus,
                        $reservation->car->car_status->getValue(),
                        'Car sold through reservation completion by seller'
                    ));
                }
            });

            // Reload with fresh data
            $reservation->refresh();
            $reservation->load(['car', 'user', 'payment', 'cancelledBy']);

            return $this->successResponse(
                new CarReservationResource($reservation),
                'Reservation marked as sold successfully'
            );

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Failed to mark reservation as sold: ' . $e->getMessage(), [
                'reservation_id' => $request->reservation_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to mark reservation as sold');
        }
    }
}
