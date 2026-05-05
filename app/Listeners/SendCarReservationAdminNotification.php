<?php

namespace App\Listeners;

use App\Events\CarReservationPaid;
use App\Models\User;
use App\Notifications\AdminCarReservationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarReservationAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CarReservationPaid $event): void
    {
        try {
            $carReservation = $event->carReservation;
            $car = $carReservation->car;
            $customer = $carReservation->user;
            $payment = $carReservation->payment;

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
                'amount' => $payment->amount ?? 0,
                'currency' => $payment->currency ?? currency_symbol(),
                'payment_method' => $payment->payment_method ?? 'Unknown',
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'reservation_id' => $carReservation->id,
                'admin_url' => url('/admin/car-reservations/' . $carReservation->id),
            ];

            // Send notification to all admin users
            foreach ($notifiables as $notifiable) {
                $notifiable->notify(new AdminCarReservationNotification(
                    AdminCarReservationNotification::TYPE_RESERVATION_PAYMENT_RECEIVED,
                    $notificationData
                ));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send car reservation admin notification: ' . $e->getMessage(), [
                'reservation_id' => $event->carReservation->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
