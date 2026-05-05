<?php

namespace App\Services\WhatsappTemplates;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

class PasswordResetTemplate implements WhatsappTemplateInterface
{
    public function prepare(array $data): Component
    {
        return new Component(
            [], // header components
            [
                [
                    'type' => 'text',
                    'text' => $data['verification_code']
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
                            'text' => 'Reset'
                        ]
                    ]
                ]
            ] // button components
        );
    }
}
