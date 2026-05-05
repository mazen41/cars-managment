<?php

namespace App\Http\Controllers;

use App\Models\CarInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CarInspectionPaymentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:edit_car_inspection_payments');
    }
    /**
     * Complete a payment.
     */
    public function setPaid(CarInspection $carInspection, Request $request)
    {
        $this->authorize('complete_car_inspection_payments');

        try {
            $carInspection->payment->markAsPaid();
            event(new \App\Events\CarInspectionPaid($carInspection));
            flash()->success('Payment marked as paid.');
            return back();

        } catch (\Exception $e) {
            Log::error('Failed to complete car inspection payment', [
                'payment_id' => $carInspection->payment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            flash()->error('Failed to complete payment. Please try again.');
            return back();
        }
    }

    /**
     * Cancel a payment.
     */
    public function setUnpaid(CarInspection $carInspection, Request $request)
    {

        if ($carInspection->payment->status != 'pending') {
            flash()->error('Payment cannot be cancelled in its current state.');
            return back();
        }

        try {
            $carInspection->payment->markAsUnpaid($request->details);
            flash()->success('Payment has been cancelled.');
            return back();

        } catch (\Exception $e) {
            Log::error('Failed to cancel car inspection payment', [
                'payment_id' => $carInspection->payment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            flash()->error('Failed to cancel payment. Please try again.');
            return back();
        }
    }

    /**
     * Mark a payment as refunded.
     */
    public function setRefunded(CarInspection $carInspection, Request $request)
    {
        try {
            if(!$carInspection->payment->can_refund){
                flash()->error('Payment cannot be refunded in its current state.');
                return back();
            }
            if($carInspection->status == CarInspection::STATUS_COMPLETED){
                flash()->error('Cannot refund a completed car inspection.');
                return back();
            }

            $carInspection->payment->markAsRefunded($request->details);
            //TODO: adjust Inspector's owed amount if necessary and handle commission reversal
            //TODO: Notify relevant parties about the refund
            $carInspection->requester->save();
            flash()->success('Payment marked as refunded.');
            return back();

        } catch (\Exception $e) {
            Log::error('Failed to mark car inspection payment as refunded', [
                'payment_id' => $carInspection->payment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            flash()->error('Failed to refund payment. Please try again.');
            return back();
        }
    }
}
