<?php

namespace App\Services;

use App\Models\OtpConfiguration;

class SendSmsService
{
    public function sendSMS($to, $from, $text, $template_id)
    {
        $otp = OtpConfiguration::where('value', 1)->where('type' ,'!=', 'whatsapp')->first();
        if(!$otp){
         return;
        }
        $otp_class = __NAMESPACE__ . '\\OTP\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $otp->type)));

        if (class_exists($otp_class)) {
            return (new $otp_class)->send($to, $from, $text, $template_id);
        } else {
            return;
        }
    }
}
