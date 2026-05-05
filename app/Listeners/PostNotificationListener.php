<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event): void
    {

        $notifiable = $event->notifiable;

        $notification = $event->notification;

        $message = $event->message;

        $response = $event->response;
        $data = [
            $notifiable,
            $notification,
            $message,
            $response->getBody()->getContents()
        ];
       // file_put_contents('fcm.txt', print_r($data,true));

    }
}
