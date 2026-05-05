<?php

namespace App\Services;

use App\Models\OtpConfiguration;
use App\Services\OTP\Whatsapp;
use App\Services\WhatsappTemplates\WhatsappTemplateInterface;
use App\Services\WhatsappTemplates\WhatsappTemplateFactory;

class SendWhatsappService
{
    protected $whatsapp;

    public function __construct(Whatsapp $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function sendMsg(string $to, string $templateName, array $data = [])
    {
        try{
            $whatsappActive = OtpConfiguration::where(['type' => 'whatsapp'])->first();

        if (!$whatsappActive || $whatsappActive->value == 0) {
            return;
        }

        $template = WhatsappTemplateFactory::make($templateName);
        $components = $template->prepare($data);

        return $this->whatsapp->send($to, $templateName, $components);
        } catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
