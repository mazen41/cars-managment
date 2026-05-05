<?php

namespace App\Services\WhatsappTemplates;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

interface WhatsappTemplateInterface
{
    public function prepare(array $data): Component;
}
