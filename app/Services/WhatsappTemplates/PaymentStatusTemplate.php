<?php

namespace App\Services\WhatsappTemplates;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

class PaymentStatusTemplate implements WhatsappTemplateInterface
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
                    'text' => $data['payment_status']
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
                            'text' => 'View Order'
                        ]
                    ]
                ]
            ] // button components
        );
    }
}
