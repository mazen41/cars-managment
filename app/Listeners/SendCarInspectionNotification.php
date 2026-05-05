<?php

namespace App\Listeners;

use App\Events\CarInspectionPaid;
use App\Models\User;
use App\Notifications\CarInspectionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarInspectionNotification implements ShouldQueue
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
    public function handle(CarInspectionPaid $event): void
    {
        try {
            $carInspection = $event->carInspection;
            $car = $carInspection->car;
            $customer = $carInspection->requester; // User who requested the inspection
            $inspector = $carInspection->inspector; // Inspector assigned
            $payment = $carInspection->payment;

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
                'inspector_name' => $inspector->name ?? 'Inspector',
                'amount' => $payment->amount ?? 0,
                'currency' => $payment->currency ?? currency_symbol(),
                'inspection_id' => $carInspection->id,
                'inspection_number' => $carInspection->inspection_number ?? 'N/A',
                'payment_method' => $payment->payment_method ?? 'Unknown',
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'scheduled_at' => $carInspection->scheduled_at,
                'url' => url('/admin/car-inspections/' . $carInspection->id),
                'customer_url' => url('/customer/inspections/' . $carInspection->id),
                'seller_url' => url('/seller/car-inspections/' . $carInspection->id),
                'inspector_url' => url('/inspector/inspections/' . $carInspection->id),
            ];

            // Send notification to admin and staff users
            foreach ($adminStaffUsers as $user) {
                $adminNotificationData = array_merge($notificationData, [
                    'url' => url('/admin/car-inspections/' . $carInspection->id),
                    'admin_url' => url('/admin/car-inspections/' . $carInspection->id),
                ]);

                $user->notify(new CarInspectionNotification(
                    CarInspectionNotification::TYPE_INSPECTION_PAID,
                    $adminNotificationData
                ));
            }

            // Check for high-value inspection and send admin notification
            $highValueThreshold = get_setting('high_value_inspection_threshold', 10000);
            if (($payment->amount ?? 0) >= $highValueThreshold) {
                $highValueNotificationData = array_merge($notificationData, [
                    'admin_url' => url('/admin/car-inspections/' . $carInspection->id),
                ]);

                foreach ($adminStaffUsers as $user) {
                    $user->notify(new \App\Notifications\AdminCarInspectionNotification(
                        \App\Notifications\AdminCarInspectionNotification::TYPE_HIGH_VALUE_INSPECTION,
                        $highValueNotificationData
                    ));
                }
            }

            // Send notification to seller if exists
            if ($seller) {
                $sellerNotificationData = array_merge($notificationData, [
                    'url' => $notificationData['seller_url'],
                ]);

                $seller->notify(new CarInspectionNotification(
                    CarInspectionNotification::TYPE_INSPECTION_PAID,
                    $sellerNotificationData
                ));

                // Send SMS to seller if they have a phone
                if ($seller->phone) {
                    $notification = new CarInspectionNotification(
                        CarInspectionNotification::TYPE_INSPECTION_PAID,
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

            $customer->notify(new CarInspectionNotification(
                CarInspectionNotification::TYPE_INSPECTION_PAID,
                $customerNotificationData
            ));

            // Send SMS to customer if they have a phone
            if ($customer->phone) {
                $notification = new CarInspectionNotification(
                    CarInspectionNotification::TYPE_INSPECTION_PAID,
                    $customerNotificationData
                );

                SendSmsToUser::dispatch(
                    $customer->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

            // Send notification to inspector if assigned
            if ($inspector) {
                $inspectorNotificationData = array_merge($notificationData, [
                    'url' => $notificationData['inspector_url'],
                ]);

                $inspector->notify(new CarInspectionNotification(
                    CarInspectionNotification::TYPE_INSPECTION_PAID,
                    $inspectorNotificationData
                ));

                // Send SMS to inspector if they have a phone
                if ($inspector->phone) {
                    $notification = new CarInspectionNotification(
                        CarInspectionNotification::TYPE_INSPECTION_PAID,
                        $inspectorNotificationData
                    );

                    SendSmsToUser::dispatch(
                        $inspector->id,
                        $notification->getSmsMessage(),
                        null
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send car inspection notification: ' . $e->getMessage(), [
                'inspection_id' => $event->carInspection->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
