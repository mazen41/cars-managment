<?php

namespace App\Services\WhatsappTemplates;

class WhatsappTemplateFactory
{
    private static $templates = [
        'phone_verification' => PhoneVerificationTemplate::class,
        'reset_password' => PasswordResetTemplate::class,
        'order_placement' => OrderPlacementTemplate::class,
        'delivery_status_change' => DeliveryStatusTemplate::class,
        'payment_status_change' => PaymentStatusTemplate::class,
        'assign_delivery_boy' => AssignDeliveryBoyTemplate::class,
    ];

    public static function make(string $templateName): WhatsappTemplateInterface
    {
        if (!isset(self::$templates[$templateName])) {
            throw new \InvalidArgumentException("Template {$templateName} not found");
        }

        $templateClass = self::$templates[$templateName];
        return new $templateClass();
    }
}
