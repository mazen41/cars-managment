<?php

namespace App\Utility;

use App\Models\OtpConfiguration;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SendSmsService;
use App\Services\SendWhatsappService;
use App\Services\OTP\Whatsapp;

class SmsUtility
{
    private static function getWhatsappService(): SendWhatsappService
    {
        return new SendWhatsappService(new Whatsapp());
    }

    public static function phone_number_verification(User $user)
    {
        $sms_template = SmsTemplate::where('identifier', 'phone_number_verification')->first();
        $sms_body = $sms_template->sms_body;
        $sms_body = str_replace('[[code]]', $user->verification_code, $sms_body);
        $sms_body = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $user->phone,
            'phone_verification',
            ['verification_code' => $user->verification_code]
        );

        (new SendSmsService())->sendSMS($user->phone, env('APP_NAME'), $sms_body, $template_id);

    }

    public static function password_reset(User $user)
    {
        $sms_template = SmsTemplate::where('identifier', 'password_reset')->first();
        $sms_body = $sms_template->sms_body;
        $sms_body = str_replace('[[code]]', $user->verification_code, $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $user->phone,
            'reset_password',
            ['verification_code' => $user->verification_code]
        );

        (new SendSmsService())->sendSMS($user->phone, env('APP_NAME'), $sms_body, $template_id);
    }

    public static function order_placement($phone, $order)
    {
        $sms_template = SmsTemplate::where('identifier', 'order_placement')->first();
        $sms_body = $sms_template->sms_body;
        $sms_body = str_replace('[[order_code]]', $order->code, $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $phone,
            'order_placement',
            [
                'order_code' => $order->code,
            ]
        );

        (new SendSmsService())->sendSMS($phone, env('APP_NAME'), $sms_body, $template_id);
    }

    public static function delivery_status_change($phone, $order)
    {
        $sms_template = SmsTemplate::where('identifier', 'delivery_status_change')->first();
        $sms_body = $sms_template->sms_body;
        $delivery_status = translate(ucfirst(str_replace('_', ' ', $order->delivery_status)));
        $sms_body = str_replace('[[delivery_status]]', $delivery_status, $sms_body);
        $sms_body = str_replace('[[order_code]]', $order->code, $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $phone,
            'delivery_status_change',
            [
                'order_code' => $order->code,
                'delivery_status' => $delivery_status,
            ]
        );

        (new SendSmsService())->sendSMS($phone, env('APP_NAME'), $sms_body, $template_id);
    }

    public static function payment_status_change($phone, $order)
    {
        $sms_template = SmsTemplate::where('identifier', 'payment_status_change')->first();
        $sms_body = $sms_template->sms_body;
        $payment_status = translate(ucfirst($order->payment_status));
        $sms_body = str_replace('[[payment_status]]', $payment_status, $sms_body);
        $sms_body = str_replace('[[order_code]]', $order->code, $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $phone,
            'payment_status_change',
            [
                'order_code' => $order->code,
                'payment_status' => $payment_status,
            ]
        );

        (new SendSmsService())->sendSMS($phone, env('APP_NAME'), $sms_body, $template_id);
    }

    public static function assign_delivery_boy($phone, $code)
    {
        $sms_template = SmsTemplate::where('identifier', 'assign_delivery_boy')->first();
        $sms_body = $sms_template->sms_body;
        $sms_body = str_replace('[[order_code]]', $code, $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $phone,
            'assign_delivery_boy',
            [
                'order_code' => $code,
            ]
        );

        (new SendSmsService())->sendSMS($phone, env('APP_NAME'), $sms_body, $template_id);
    }

    public static function account_deletion_request($phone){
        $sms_template = SmsTemplate::where('identifier', 'account_deletion_request')->first();
        $sms_body = $sms_template->sms_body;
        $sms_body = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
        $template_id = $sms_template->template_id;

        self::getWhatsappService()->sendMsg(
            $phone,
            'account_deletion_request',
            []
        );

        (new SendSmsService())->sendSMS($phone, env('APP_NAME'), $sms_body, $template_id);
    }
}
