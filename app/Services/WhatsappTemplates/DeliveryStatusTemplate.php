<?php

namespace App\Services\WhatsappTemplates;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

class DeliveryStatusTemplate implements WhatsappTemplateInterface
{
    public function prepare(array $data): Component
    {
        return new Component(
            [
                [
                    'type' => 'text',
                    'text' => $data['order_code']
                ]
            ], // header components
            [
                [
                    'type' => 'text',
                    'text' => $data['delivery_status']
                ]
            ], // body components
            [
                [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => 0,
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => 'Track Order'
                        ]
                    ]
                ]
            ] // button components
        );
    }
}
