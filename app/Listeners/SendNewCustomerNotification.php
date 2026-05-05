<?php

namespace App\Listeners;

use App\Events\CustomerRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewCustomerNotification;
use App\Models\User;

class SendNewCustomerNotification
{
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
    public function handle(CustomerRegistered $event): void
    {
        $user = $event->user;
        offerUserWelcomeCoupon($user->id);
        $data = [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ];
        $notificationType = get_notification_type('new_customer_admin', 'type');
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q){
                $q->where('user_type', 'staff')
                  ->whereHas('permissions', function($q){
                      $q->where('name', 'view_customers');
                  });
            })
            ->get();
        // Notify all admins and staff with permission to view customers
        if (!$notificationType) {
            return; // No notification type found, exit early
        }
        if ($notifiables->isEmpty()) {
            return; // No notifiable users found, exit early
        }
        foreach($notifiables as $notifiable) {
            $notifiable->notify(new NewCustomerNotification($data, $notificationType));
        }
    }
}
