<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            // this code should be refactored as it is a mess -_-
            'data' => $this->collection->map(function($data) {
                $notificationType = get_notification_type($data->notification_type_id, 'id');
                $notifyContent = $notificationType ? $notificationType->getTranslation('default_text') : '';
                $image =$notificationType ? uploaded_asset($notificationType->image) : static_asset('assets/img/notification.png');
                $notificationData = (array) $data->data;
                $type_id = null;
                if ($data->type == 'App\Notifications\OrderNotification'){
                    $type_id = $data->data['order_id'];
                    $notifyContent = str_replace('[[order_code]]', $data->data['order_code'], $notifyContent);
                    isset($data->data['status']) ? $notifyContent = str_replace('[[status]]', $data->data['status'], $notifyContent) : null;
                    isset($data->data['payment_method']) ? $notifyContent = str_replace('[[payment_method]]', translate($data->data['payment_method']), $notifyContent): null;
                    isset($data->data['amount']) ? $notifyContent = str_replace('[[amount]]', format_price(convert_price_from_usd($data->data['amount'], true)), $notifyContent): null;
                } elseif($data->type == 'App\Notifications\CustomNotification') {
                    //
                } elseif($data->type == 'App\Notifications\ShopVerificationNotification') {
                    //
                } elseif($data->type == 'App\Notifications\PayoutNotification') {
                    $notifyContent = str_replace('[[amount]]', $data->data['payment_amount'], $notifyContent);
                    $notifyContent = str_replace('[[shop_name]]', $data->data['name'], $notifyContent);
                } elseif($data->type == 'App\Notifications\ShopProductNotification') {
                    $type_id = $data->data['id'];
                    $notifyContent = str_replace('[[product_name]]', $data->data['name'], $notifyContent);
                } else {
                    if(isset($notificationData["className"])){
                        $notificationClass =  new $notificationData["className"]($notificationData["type"], $notificationData["data"]);
                        $notifyContent = $notificationClass->getBody();
                        if (method_exists($notificationClass, 'getTypeId')) {
                            $type_id = $notificationClass->getTypeId();
                        } else {
                            $type_id = null;
                        }
                        } else {
                            $notifyContent = "Unsupported notification type: " . $data->type;
                        }
                }

                // Don't know why this is code in here but I would't touch it
                 if (isset($notificationData['data']['user_id'])) {
                    $notificationData['data']['user_id'] = (int) $notificationData['data']['user_id'];
                }

                if (isset($notificationData['data']['seller_id'])) {
                    $notificationData['data']['seller_id'] = (int) $notificationData['data']['seller_id'];
                }

                return [
                    'id' => $data->id,
                    "isChecked" => false,
                    'type' => $data->type,
                    'type_id' => $type_id,
                    'data' => $notificationData,
                    'notification_text' => $notifyContent,
                    'image' => $image,
                    'date' => date("F j Y, g:i a", strtotime($data->created_at))
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
