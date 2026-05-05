<?php

namespace App\Listeners;

use App\Events\AccountDeletionRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Notifications\AccountDeletionRequestNotification;
use App\Models\NotificationType;
use App\Utility\SmsUtility;

class SendAccountDeletionRequestNotification
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
    public function handle(AccountDeletionRequested $event): void
    {
        $user = $event->user;
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q){
                $q->where('user_type', 'staff')
                  ->whereHas('permissions', function($q){
                      $q->where('name', 'view_customers');
                  });
            })
            ->get();

            $customerNotificationData = [
                'user_id' => $user->id,
                'notification_type_id' => NotificationType::where('type', 'account_deletion_request_customer')->first()->id,
            ];
        // Send notification to the user
        $userNotificationType = NotificationType::where('type', 'account_deletion_request_customer')->first();
        if ($userNotificationType) {
            $user->notify(new AccountDeletionRequestNotification($customerNotificationData));
        }
        if($user->phone){
            SmsUtility::account_deletion_request($user->phone);
        }

        // Send notification to admin and staff users
        if ($notifiables->isEmpty()) {
            return; // No notifiable users found, exit early
        }
        $adminNotificationType = NotificationType::where('type', 'account_deletion_request_admin')->first();
        if (!$adminNotificationType) {
            return; // No admin notification type found, exit early
        }
         $adminNotificationData = [
                'user_id' => $user->id,
                'notification_type_id' => NotificationType::where('type', 'account_deletion_request_admin')->first()->id,
            ];
        foreach($notifiables as $notifiable) {
            $notifiable->notify(new AccountDeletionRequestNotification($adminNotificationData));
        }
    }
}
