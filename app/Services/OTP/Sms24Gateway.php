<?php

namespace App\Services\OTP;

use SmsGateway24\SmsGateway24;
use App\Contracts\SendSms;

class Sms24Gateway implements SendSms {

    public function send($to, $from, $text, $template_id){

        $api_key = env('SMS24GATEWAY_API_KEY');
        $deviceId = env('SMS24GATEWAY_DEVICE_ID');
        try{
        $gateway = new SmsGateway24($api_key);
        $sim=0;  // Optional. 0 or 1. For Dual SIM devices. (if you skip it -> default sim is  0)
        $urgent = 1; // Optional. 1 or 0 to make sms Urgent.
        $smsId = $gateway->addSms($to, $text, $deviceId, null, $sim, null, $urgent);

    } catch(\Exception $e){
        return response()->json(['message'=>$e->getMessage()]);
    }
    }
}

