<?php

namespace App\Listeners;

use App\Events\CarInspectionPaid;
use App\Models\User;
use App\Notifications\AdminCarInspectionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarInspectionAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CarInspectionPaid $event): void
    {
        try {
            $carInspection = $event->carInspection;
            $car = $carInspection->car;
            $customer = $carInspection->requester;
            $inspector = $carInspection->inspector;
            $payment = $carInspection->payment;

            // Get admin and staff users for notification
            $notifiables = User::where('user_type', 'admin')
                ->orWhere(function($q) {
                    $q->where('user_type', 'staff');
                })
                ->get();

            if ($notifiables->isEmpty()) {
                return;
            }

            // Prepare notification data
            $notificationData = [
                'car_name' => $car->name ?? $car->car_name ?? 'Car #' . $car->id,
                'customer_name' => $customer->name ?? 'Customer',
                'inspector_name' => $inspector->name ?? 'Inspector',
                'amount' => $payment->amount ?? 0,
                'currency' => $payment->currency ?? currency_symbol(),
                'payment_method' => $payment->payment_method ?? 'Unknown',
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'inspection_id' => $carInspection->id,
                'inspection_number' => $carInspection->inspection_number ?? 'N/A',
                'admin_url' => url('/admin/car-inspections/' . $carInspection->id),
            ];

            // Send notification to all admin users
            foreach ($notifiables as $notifiable) {
                $notifiable->notify(new AdminCarInspectionNotification(
                    AdminCarInspectionNotification::TYPE_INSPECTION_PAYMENT_RECEIVED,
                    $notificationData
                ));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send car inspection admin notification: ' . $e->getMessage(), [
                'inspection_id' => $event->carInspection->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
