<?php

namespace App\Http\Controllers;


use App\Models\Car;
use App\Models\CarReservation;
use App\Models\User;
use App\Events\CarStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\PaymentStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;

class CarReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_car_reservations')->only(['index', 'show']);
        $this->middleware('permission:edit_car_reservations')->only(['confirm', 'cancel', 'markAsSold']);
        $this->middleware('permission:delete_car_reservations')->only(['destroy']);
    }

    /**
     * Display a listing of car reservations.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarReservation::with([
            'car.brand',
            'car.model',
            'car.color',
            'user',
            'cancelledBy'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        if ($request->filled('car_id')) {
            $query->where('car_id', $request->car_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('reserved_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reserved_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('car', function ($carQuery) use ($search) {
                    $carQuery->where('description', 'LIKE', "%{$search}%")
                             ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                 $brandQuery->where('name', 'LIKE', "%{$search}%");
                             })
                             ->orWhereHas('model', function ($modelQuery) use ($search) {
                                 $modelQuery->where('name', 'LIKE', "%{$search}%");
                             });
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->orWhere('transaction_id', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'reserved_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reservations = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => CarReservation::count(),
            'pending' => CarReservation::pending()->count(),
            'confirmed' => CarReservation::confirmed()->count(),
            'cancelled' => CarReservation::cancelled()->count(),
            'completed' => CarReservation::where('status', CarReservation::STATUS_COMPLETED)->count(),
            'total_amount' => Payment::where('payable_type', CarReservation::class)
                ->where('status', PaymentStatusEnum::PAID)
                ->sum('amount'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'reservations' => $reservations,
                'stats' => $stats,
                'statuses' => CarReservation::getAvailableStatuses(),
                'payment_statuses' => PaymentStatusEnum::labels()
            ]);
        }

        return view('backend.cars.reservations.index', compact('reservations', 'stats'));
    }


    /**
     * Display the specified car reservation.
     */
    public function show(CarReservation $carReservation): View|JsonResponse
    {
        $carReservation->load([
            'car.brand',
            'car.model',
            'car.color',
            'car.category',
            'user',
            'cancelledBy'
        ]);

        if (request()->wantsJson()) {
            return response()->json(['reservation' => $carReservation]);
        }

        return view('backend.cars.reservations.show', compact('carReservation'));
    }

    /**
     * Confirm a car reservation.
     */
    public function confirm(Request $request, CarReservation $carReservation): RedirectResponse|JsonResponse
    {
        if (!$carReservation->can_be_cancelled) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'This reservation cannot be confirmed'], 400);
            }
            return back()->with('error', 'This reservation cannot be confirmed');
        }

        try {
            DB::beginTransaction();

            $oldCarStatus = $carReservation->car->car_status->getValue();
            
            $carReservation->confirm($request->admin_notes);

            DB::commit();

            // Dispatch event if car status changed to reserved
            if ($carReservation->car->car_status->getValue() !== $oldCarStatus) {
                event(new CarStatusChanged(
                    $carReservation->car,
                    $oldCarStatus,
                    $carReservation->car->car_status->getValue(),
                    'Car reserved through reservation confirmation'
                ));
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car reservation confirmed successfully',
                    'reservation' => $carReservation->fresh()
                ]);
            }

            flash(translate('Car reservation confirmed successfully'))->success();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to confirm reservation'], 500);
            }

            return back()->with('error', 'Failed to confirm reservation');
        }
    }

    /**
     * Cancel a car reservation.
     */
    public function cancel(Request $request, CarReservation $carReservation): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        if (!$carReservation->can_be_cancelled) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'This reservation cannot be cancelled'], 400);
            }
            return back()->with('error', 'This reservation cannot be cancelled');
        }

        try {
            DB::beginTransaction();

            $oldCarStatus = $carReservation->car->car_status->getValue();
            
            $carReservation->cancel(
                $request->cancellation_reason,
                Auth::id()
            );

            DB::commit();

            // Dispatch event if car status changed back to available
            if ($carReservation->car->car_status->getValue() !== $oldCarStatus) {
                event(new CarStatusChanged(
                    $carReservation->car,
                    $oldCarStatus,
                    $carReservation->car->car_status->getValue(),
                    'Reservation cancelled: ' . ($request->cancellation_reason ?? 'No reason provided')
                ));
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car reservation cancelled successfully',
                    'reservation' => $carReservation->fresh()
                ]);
            }

            flash(translate('Car reservation cancelled successfully'))->success();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to cancel reservation'], 500);
            }

            return back()->with('error', 'Failed to cancel reservation');
        }
    }

    /**
     * Mark car as sold (complete reservation).
     */
    public function markAsSold(Request $request, CarReservation $carReservation): RedirectResponse|JsonResponse
    {
        if ($carReservation->status !== CarReservation::STATUS_CONFIRMED) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only confirmed reservations can be marked as sold'], 400);
            }
            return back()->with('error', 'Only confirmed reservations can be marked as sold');
        }

        try {
            DB::beginTransaction();

            $oldCarStatus = $carReservation->car->car_status->getValue();
            
            $carReservation->complete($request->admin_notes);

            DB::commit();

            // Dispatch event if car status changed to sold
            if ($carReservation->car->car_status->getValue() !== $oldCarStatus) {
                event(new CarStatusChanged(
                    $carReservation->car,
                    $oldCarStatus,
                    $carReservation->car->car_status->getValue(),
                    'Car sold through reservation completion'
                ));
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car marked as sold successfully',
                    'reservation' => $carReservation->fresh()
                ]);
            }

            flash(translate('Car marked as sold successfully'))->success();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to mark car as sold'], 500);
            }

            return back()->with('error', 'Failed to mark car as sold');
        }
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, CarReservation $carReservation): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'payment_details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            // Decrement owed amount from owner shop for refunded payments
            if($request->payment_status != PaymentStatusEnum::PAID && $carReservation->payment->status == PaymentStatusEnum::PAID){
                $owner = $carReservation->car->user;
                if($owner->user_type == 'seller'){
                    $commission = $carReservation->commission;
                    $reservation_amount = $carReservation->payment ? $carReservation->payment->amount : 0;
                    $owed_amount = $reservation_amount - $commission->admin_commission;
                    $owner->shop->decrementOwedAmount($owed_amount);

                    // Remove shop commission record
                    $commission->delete();
                }


            }

            $carReservation->updatePaymentStatus(
                $request->payment_status,
                $request->transaction_id,
                $request->payment_details
            );
            if($request->payment_status == PaymentStatusEnum::PAID){
                event(new \App\Events\CarReservationPaid($carReservation));
            }
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Payment status updated successfully',
                    'reservation' => $carReservation->fresh()
                ]);
            }

            flash(translate('Payment status updated successfully'))->success();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update payment status'], 500);
            }

            return back()->with('error', 'Failed to update payment status');
        }
    }

    /**
     * Remove the specified car reservation.
     */
    public function destroy(CarReservation $carReservation): RedirectResponse|JsonResponse
    {
        if ($carReservation->is_active) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Cannot delete active reservations'], 400);
            }
            return back()->with('error', 'Cannot delete active reservations');
        }

        try {
            DB::beginTransaction();

            $carReservation->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car reservation deleted successfully']);
            }

            flash(translate('Car reservation deleted successfully'))->success();
            return redirect()->route('admin.car-reservations.index');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car reservation'], 500);
            }

            return back()->with('error', 'Failed to delete car reservation');
        }
    }

    /**
     * Get user's car reservations.
     */
    public function myReservations(Request $request): View|JsonResponse
    {
        $query = CarReservation::where('user_id', Auth::id())
            ->with(['car.brand', 'car.model', 'car.color']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'reserved_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reservations = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json(['reservations' => $reservations]);
        }

        return view('frontend.user.reservations', compact('reservations'));
    }

    /**
     * Bulk update reservations status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'exists:car_reservations,id',
            'status' => 'required|in:pending,confirmed,cancelled,expired,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $updated = 0;
            foreach ($request->reservation_ids as $reservationId) {
                $reservation = CarReservation::find($reservationId);
                if ($reservation && $reservation->can_be_cancelled) {
                    $reservation->update(['status' => $request->status]);
                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully updated {$updated} reservations",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update reservations'], 500);
        }
    }

    /**
     * Get reservation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());

        $stats = [
            'total_reservations' => CarReservation::whereBetween('reserved_at', [$dateFrom, $dateTo])->count(),
            'pending_reservations' => CarReservation::pending()->whereBetween('reserved_at', [$dateFrom, $dateTo])->count(),
            'confirmed_reservations' => CarReservation::confirmed()->whereBetween('reserved_at', [$dateFrom, $dateTo])->count(),
            'cancelled_reservations' => CarReservation::cancelled()->whereBetween('reserved_at', [$dateFrom, $dateTo])->count(),
            'completed_reservations' => CarReservation::where('status', CarReservation::STATUS_COMPLETED)->whereBetween('reserved_at', [$dateFrom, $dateTo])->count(),
            'total_revenue' => Payment::where('payable_type', CarReservation::class)
                ->where('status', PaymentStatusEnum::PAID)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount')
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get Setup page
     */

    public function setup()
    {

     return view('backend.cars.reservations.setup');

    }
}
