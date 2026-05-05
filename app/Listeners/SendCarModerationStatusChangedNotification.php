<?php

namespace App\Listeners;

use App\Events\CarModerationStatusChanged;
use App\Notifications\CarNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarModerationStatusChangedNotification implements ShouldQueue
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
    public function handle(CarModerationStatusChanged $event): void
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
                'old_moderation_status' => $event->oldModerationStatus,
                'new_moderation_status' => $event->newModerationStatus,
                'notes' => $event->notes,
                'price' => $car->price ?? 0,
                'currency' => get_setting('system_default_currency'),
                'url' => url('/seller/cars/' . $car->id),
            ];

            // Determine notification type based on new moderation status
            $notificationType = CarNotification::TYPE_CAR_MODERATION_STATUS_CHANGED;

            if ($event->newModerationStatus === 'approved') {
                $notificationType = CarNotification::TYPE_CAR_APPROVED;
            } elseif ($event->newModerationStatus === 'rejected') {
                $notificationType = CarNotification::TYPE_CAR_REJECTED;
                $notificationData['reason'] = $event->notes ?? 'Not specified';
            }

            // Send notification to seller
            $seller->notify(new CarNotification(
                $notificationType,
                $notificationData
            ));

            // Send SMS for approved or rejected status
            if (in_array($event->newModerationStatus, ['approved', 'rejected']) && $seller->phone) {
                $notification = new CarNotification($notificationType, $notificationData);
                SendSmsToUser::dispatch(
                    $seller->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

            Log::info('Car moderation status changed notification sent', [
                'car_id' => $car->id,
                'seller_id' => $seller->id,
                'old_moderation_status' => $event->oldModerationStatus,
                'new_moderation_status' => $event->newModerationStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send car moderation status changed notification: ' . $e->getMessage(), [
                'car_id' => $event->car->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
