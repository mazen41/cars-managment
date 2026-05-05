<?php

namespace App\Listeners;

use App\Events\CarReservationPaid;
use App\Models\User;
use App\Notifications\CarReservationNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Notifications\AdminCarReservationNotification;

class SendCarReservationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

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
            $adminStaffUsers = User::where('user_type', 'admin')
                ->orWhere(function($q) {
                    $q->where('user_type', 'staff');
                })
                ->get();

            // Get seller if car has one and is not admin/staff
            $seller = null;
            if ($car->user && !in_array($car->user->user_type, ['admin', 'staff'])) {
                $seller = $car->user;
            }

            // Prepare notification data
            $notificationData = [
                'car_name' => $car->name ?? $car->car_name ?? 'Car #' . $car->id,
                'customer_name' => $customer->name ?? 'Customer',
                'amount' => $payment->amount ?? 0,
                'currency' => $payment->currency ?? currency_symbol(),
                'reservation_id' => $carReservation->id,
                'payment_method' => $payment->method ?? 'Unknown',
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'url' => url('/admin/car-reservations/' . $carReservation->id),
                'customer_url' => url('/customer/reservations/' . $carReservation->id),
                'seller_url' => url('/seller/car-reservations/' . $carReservation->id),
            ];

            // Send notification to admin and staff users
            foreach ($adminStaffUsers as $user) {
                $adminNotificationData = array_merge($notificationData, [
                    'url' => url('/admin/car-reservations/' . $carReservation->id),
                    'admin_url' => url('/admin/car-reservations/' . $carReservation->id),
                ]);

                $user->notify(new AdminCarReservationNotification(
                    AdminCarReservationNotification::TYPE_RESERVATION_PAYMENT_RECEIVED,
                    $adminNotificationData
                ));
            }

            // Check for high-value reservation and send admin notification
            $highValueThreshold = get_setting('high_value_reservation_threshold', 50000);
            if (($payment->amount ?? 0) >= $highValueThreshold) {
                $highValueNotificationData = array_merge($notificationData, [
                    'admin_url' => url('/admin/car-reservations/' . $carReservation->id),
                ]);

                foreach ($adminStaffUsers as $user) {
                    $user->notify(new AdminCarReservationNotification(
                        AdminCarReservationNotification::TYPE_HIGH_VALUE_RESERVATION,
                        $highValueNotificationData
                    ));
                }
            }

            // Send notification to seller if exists
            if ($seller) {
                $sellerNotificationData = array_merge($notificationData, [
                    'url' => $notificationData['seller_url'],
                ]);

                $seller->notify(new CarReservationNotification(
                    CarReservationNotification::TYPE_RESERVATION_PAID,
                    $sellerNotificationData
                ));

                // Send SMS to seller if they have a phone
                if ($seller->phone) {
                    $notification = new CarReservationNotification(
                        CarReservationNotification::TYPE_RESERVATION_PAID,
                        $sellerNotificationData
                    );

                    SendSmsToUser::dispatch(
                        $seller->id,
                        $notification->getSmsMessage(),
                        null
                    );
                }
            }

            // Send notification to customer
            $customerNotificationData = array_merge($notificationData, [
                'url' => $notificationData['customer_url'],
            ]);

            $customer->notify(new CarReservationNotification(
                CarReservationNotification::TYPE_RESERVATION_PAID,
                $customerNotificationData
            ));

            // Send SMS to customer if they have a phone
            if ($customer->phone) {
                $notification = new CarReservationNotification(
                    CarReservationNotification::TYPE_RESERVATION_PAID,
                    $customerNotificationData
                );

                SendSmsToUser::dispatch(
                    $customer->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send car reservation notification: ' . $e->getMessage(), [
                'reservation_id' => $event->carReservation->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
