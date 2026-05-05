<?php

namespace App\Listeners;

use App\Events\CarDeleted;
use App\Models\User;
use App\Notifications\CarNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarDeletedNotification implements ShouldQueue
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
    public function handle(CarDeleted $event): void
    {
        try {
            $carData = $event->carData;
            $sellerId = $carData['seller_id'] ?? null;

            if (!$sellerId) {
                Log::warning('Car has no seller, skipping notification', ['car_data' => $carData]);
                return;
            }

            $seller = User::find($sellerId);
            
            if (!$seller) {
                Log::warning('Seller not found, skipping notification', ['seller_id' => $sellerId]);
                return;
            }

            // Prepare notification data
            $notificationData = [
                'car_id' => $carData['id'] ?? null,
                'car_name' => $carData['name'] ?? $carData['car_name'] ?? 'Car #' . ($carData['id'] ?? 'Unknown'),
                'reason' => $event->reason,
                'deleted_by' => $event->deletedBy,
                'price' => $carData['price'] ?? 0,
                'currency' => get_setting('system_default_currency'),
                'url' => url('/seller/cars'),
            ];

            // Send notification to seller
            $seller->notify(new CarNotification(
                CarNotification::TYPE_CAR_DELETED,
                $notificationData
            ));

            // Send SMS if seller has phone
            if ($seller->phone) {
                $notification = new CarNotification(
                    CarNotification::TYPE_CAR_DELETED,
                    $notificationData
                );
                SendSmsToUser::dispatch(
                    $seller->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

            Log::info('Car deleted notification sent', [
                'car_id' => $carData['id'] ?? null,
                'seller_id' => $seller->id,
                'deleted_by' => $event->deletedBy,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send car deleted notification: ' . $e->getMessage(), [
                'car_data' => $event->carData ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
