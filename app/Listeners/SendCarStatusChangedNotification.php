<?php

namespace App\Listeners;

use App\Events\CarStatusChanged;
use App\Notifications\CarNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarStatusChangedNotification implements ShouldQueue
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
    public function handle(CarStatusChanged $event): void
    {
        try {
            $car = $event->car;
            $seller = $car->user;

            if (!$seller || $seller->user_type !== 'seller') {
                Log::warning('Car has no seller, skipping notification', ['car_id' => $car->id]);
                return;
            }

            // Prepare notification data
            $notificationData = [
                'car_id' => $car->id,
                'car_name' => $car->name ?? $car->car_name ?? 'Car #' . $car->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'reason' => $event->reason,
                'price' => $car->price ?? 0,
                'currency' => get_setting('system_default_currency'),
                'url' => url('/seller/cars/' . $car->id),
            ];

            // Determine notification type based on new status
            $notificationType = CarNotification::TYPE_CAR_STATUS_CHANGED;

            if ($event->newStatus === 'published') {
                $notificationType = CarNotification::TYPE_CAR_PUBLISHED;
            } elseif ($event->newStatus === 'draft') {
                $notificationType = CarNotification::TYPE_CAR_UNPUBLISHED;
            } elseif ($event->newStatus === 'sold') {
                $notificationType = CarNotification::TYPE_CAR_SOLD;
            } elseif ($event->newStatus === 'reserved') {
                $notificationType = CarNotification::TYPE_CAR_RESERVED;
            }

            // Send notification to seller
            $seller->notify(new CarNotification(
                $notificationType,
                $notificationData
            ));

            // Send SMS for critical status changes
            if (in_array($event->newStatus, ['sold', 'published']) && $seller->phone) {
                $notification = new CarNotification($notificationType, $notificationData);
                SendSmsToUser::dispatch(
                    $seller->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

            Log::info('Car status changed notification sent', [
                'car_id' => $car->id,
                'seller_id' => $seller->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send car status changed notification: ' . $e->getMessage(), [
                'car_id' => $event->car->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
