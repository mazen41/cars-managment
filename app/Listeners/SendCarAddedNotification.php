<?php

namespace App\Listeners;

use App\Events\CarAdded;
use App\Models\User;
use App\Notifications\CarNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarAddedNotification implements ShouldQueue
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
    public function handle(CarAdded $event): void
    {
        try {
            $car = $event->car;
            $seller = $car->user;

            // Get admin and staff users for notification
            $adminStaffUsers = User::where('user_type', 'admin')
                ->orWhere(function($q) {
                    $q->where('user_type', 'staff');
                })
                ->get();

            // Prepare notification data
            $notificationData = [
                'car_id' => $car->id,
                'car_name' => $car->name ?? $car->car_name ?? 'Car #' . $car->id,
                'seller_name' => $seller->name ?? 'Seller',
                'seller_id' => $seller->id ?? null,
                'price' => $car->price ?? 0,
                'currency' => get_setting('system_default_currency'),
                'brand' => $car->brand->name ?? 'Unknown',
                'model' => $car->model->name ?? 'Unknown',
                'year' => $car->manufacture_year ?? 'N/A',
                'url' => url('/admin/cars/' . $car->id),
            ];

            // Send notification to admin and staff users
            foreach ($adminStaffUsers as $user) {
                $user->notify(new CarNotification(
                    CarNotification::TYPE_CAR_ADDED,
                    $notificationData
                ));
            }

            Log::info('Car added notification sent', [
                'car_id' => $car->id,
                'seller_id' => $seller->id ?? null,
                'notified_users' => $adminStaffUsers->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send car added notification: ' . $e->getMessage(), [
                'car_id' => $event->car->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
