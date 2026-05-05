<?php

namespace App\Utility;

use App\Mail\InvoiceEmailManager;
use App\Models\User;
use App\Models\SmsTemplate;
use App\Models\FirebaseNotification;
use App\Http\Controllers\OTPVerificationController;
use App\Notifications\OrderNotification;
use App\Notifications\MobileNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotificationUtility
{
    public static function sendOrderPlacedNotification($order)
    {
       if(config('app.env') != 'local'){
        self::sendOrderEmail($order);
        self::sendOrderSms($order);
        self::sendNotification($order, 'placed');
        self::sendFirebaseNotificationForOrder($order);
       }
    }

    private static function sendOrderEmail($order)
    {
        $emailData = [
            'view' => 'emails.invoice',
            'subject' => translate('A new order has been placed') . ' - ' . $order->code,
            'from' => env('MAIL_FROM_ADDRESS'),
            'order' => $order,
        ];

        try {
            if ($order->user->email) {
                Mail::to($order->user->email)->queue(new InvoiceEmailManager($emailData));
            }
            Mail::to($order->orderDetails->first()->product->user->email)->queue(new InvoiceEmailManager($emailData));
        } catch (\Exception $e) {
            Log::error('Failed to send order email: ' . $e->getMessage());
        }
    }

    private static function sendOrderSms($order)
    {
        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'order_placement')->first()->status == 1) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_order_code($order);
            } catch (\Exception $e) {
                Log::error('Failed to send order SMS: ' . $e->getMessage());
            }
        }
    }

    public static function sendNotification($order, $orderStatus)
    {
        $userIds = self::getNotifiableUserIds($order);
        $users = User::findMany($userIds)->merge(User::permission('view_all_orders')->get());

        $notificationData = self::prepareNotificationData($order, $orderStatus);

        foreach ($users as $user) {
            $notificationType = get_notification_type("order_{$orderStatus}_{$user->user_type}", 'type');
            if ($notificationType && $notificationType->status == 1) {
                $notificationData['notification_type_id'] = $notificationType->id;
                Notification::send($user, new OrderNotification($notificationData));
            }
            if($user->user_type == 'seller'){
                $msgBody = $notificationType->default_text;
                $msgBody = str_replace('[[order_code]]', $notificationData['order_code'], $msgBody);
                $msgBody = str_replace('[[status]]', $notificationData['status'], $msgBody);
                $msgBody = str_replace('[[payment_method]]', translate($notificationData['payment_method']), $msgBody);
                $msgBody = str_replace('[[amount]]', $notificationData['amount'].currency_symbol(), $msgBody);
                self::sendFirebaseNotification((object)[
                    'type' => 'order',
                    'id' => $order->id,
                    'user_id' => $user->id,
                    'title' => $notificationType->name,
                    'text'  => $msgBody
                ]);
            }
        }
    }


    private static function getNotifiableUserIds($order)
    {
        $adminId = User::where('user_type', 'admin')->first()->id;
        $userIds = [$order->user->id, $order->seller_id];
        if ($order->seller_id != $adminId) {
            $userIds[] = $adminId;
        }
        return array_unique($userIds);
    }

    private static function prepareNotificationData($order, $orderStatus, $payment_method = null)
    {
        return [
            'order_id' => $order->id,
            'order_code' => $order->code,
            'user_id' => $order->user_id,
            'seller_id' => $order->seller_id ?? null,
            'status' => $orderStatus,
            'payment_method' => $payment_method ?? $order->payment_type,
            'amount' => $order->grand_total,
        ];
    }

    private static function sendFirebaseNotificationForOrder($order)
    {
        if (get_setting('google_firebase') == 1) {
            $firebaseData = [
                'device_token' => $order->user->device_token,
                'title' => translate("Order placed!"),
                'text' => translate('A new order has been placed') . ' - ' . $order->code,
                'type' => "order",
                'id' => $order->id,
                'user_id' => $order->user->id,
            ];

            self::sendFirebaseNotification((object)$firebaseData);
        }
    }

    public static function sendFirebaseNotification($req)
    {
        $data = [
            'item_type' => $req->type,
            'item_type_id' => (string)$req->id,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        $user = User::where('id', $req->user_id)->get();
        Notification::send($user, new MobileNotification($req->title, $req->text, $data));

    }
}
