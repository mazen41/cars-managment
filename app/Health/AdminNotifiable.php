<?php
namespace App\Health;

use Illuminate\Notifications\Notifiable;
use App\Models\User;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Support\Collection;

class AdminNotifiable
{
    use Notifiable, HasPushSubscriptions;

    public function routeNotificationForMail()
    {
        return User::role('Tech Support')->pluck('email')->toArray();
    }

    public function routeNotificationForWebPush()
    {
        // Get all Tech Support users
        $techSupportUsers = User::role('Tech Support')->get();

        // Collect all push subscriptions
        $subscriptions = new Collection();

        foreach ($techSupportUsers as $user) {
            $subscriptions = $subscriptions->merge($user->pushSubscriptions);
        }

        return $subscriptions;
    }

    // Add an ID for database notifications
    public function getKey()
    {
        return 'admin';
    }

    // Add a route key name
    public function getRouteKeyName()
    {
        return 'id';
    }
}
