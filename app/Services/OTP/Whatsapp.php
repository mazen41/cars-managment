<?php

namespace App\Services\OTP;


use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;


class Whatsapp
{

    public function send($to, $template_name, $components)
    {

        $phone_number = env('WHATSAPP_PHONE_NUMBER_ID');
        $access_token = env('WHATSAPP_API_ACCESS_TOKEN');
        try {

            $whatsapp_cloud_api = new WhatsAppCloudApi([
                'from_phone_number_id' => $phone_number,
                'access_token' => $access_token,
            ]);

            $whatsapp_cloud_api->sendTemplate($to, $template_name, 'ar', $components);
        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()]);
        }
    }
}
