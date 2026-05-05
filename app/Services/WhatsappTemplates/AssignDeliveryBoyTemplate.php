<?php

namespace App\Services\WhatsappTemplates;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

class AssignDeliveryBoyTemplate implements WhatsappTemplateInterface
{
    public function prepare(array $data): Component
    {
        return new Component(
            [
            ], // header components
            [
                [
                    'type' => 'text',
                    'text' => $data['order_code']
                ]
            ], // body components
        );
    }
}
