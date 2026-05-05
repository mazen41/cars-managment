<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\JsonResource;

class CommissionHistoryResource extends JsonResource
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
            'type' => $this->commissionable_name,
            'reference_code' => $this->reference_code,
            'admin_commission' => $this->admin_commission,
            'seller_earning' => format_price($this->ownable_earning),
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
        ];
    }
}
