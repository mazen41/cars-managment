<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\NotificationType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class NewMessageListener
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
    public function handle(MessageSent $event): void
    {
        $sender = $event->sender;
        $message = $event->message;
        $receiver = $event->receiver;

        $conversation = $message->conversation;
        $notifiables = [];

        $message_receiver = $receiver;

        $notification_type =  NotificationType::where('type', 'new_message_admin')->first();

        if($message_receiver->user_type == 'admin'){

            $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q){
                $q->where('user_type', 'staff')
                  ->whereHas('permissions', function($q){
                      $q->where('name', 'view_all_product_conversations');
                  });
            })
            ->get();

           }

           if($message_receiver->user_type == 'customer' || $message_receiver->user_type == 'seller'){
            $notifiables[] = $message_receiver;
           }

        if($notification_type){

            $default_text = $notification_type->default_text;
            $message_text =  mb_strimwidth($message->message, 0, 50, "...");
            $default_text = str_replace('[[message]]', $message_text, $default_text);
            $notification_data = [
                'message' => $message_text,
                'title' => $notification_type->name ?? translate('New Message'),
                'body' => $default_text ?? translate('You have a new message'),
                'notification_type_id' => $notification_type->id,
                'conversation_id' => $message->conversation_id,
            ];
            foreach ($notifiables as $notifiable) {
                $notifiable->notify(new \App\Notifications\NewMessageNotification($notification_data));
                if($notifiable->user_type == 'customer' || $notifiable->user_type == 'seller'){
                    $fcm_data = [
                        'item_type'=> 'conversation',
                        'item_type_id' => $message->conversation_id,
                        'messenger_name'  => $conversation->title,
                        'messenger_image' => static_asset('assets/img/app_logo.png'),
                        'messenger_title'   => $conversation->title,
                        'action'  => 'FLUTTER_NOTIFICATION_CLICK'
                    ];
                    Notification::send($notifiable, new \App\Notifications\MobileNotification($notification_data['title'], $notification_data['body'], $fcm_data));
                }
            }
        }
    }
}
