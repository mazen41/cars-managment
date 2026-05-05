<?php

namespace App\Http\Resources\V2\Inspector;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_display' => $this->type_display,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'description' => $this->description,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'status' => $this->status,
            'status_display' => $this->status_display,
            'transaction_reference' => $this->transaction_reference,
            'notes' => $this->notes,
            'processed_by' => $this->whenLoaded('processedBy', function () {
                return [
                    'id' => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}