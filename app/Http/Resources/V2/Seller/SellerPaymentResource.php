<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\JsonResource;

class SellerPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $payment_method = translate(ucfirst(str_replace('_', ' ', $this->payment_method)));
        if ($this->txn_code != null) {
            $payment_method = translate(ucfirst(str_replace('_', ' ', $this->payment_method))). ' - ' .translate('Transaction ID'). ':' .$this->txn_code;
        }

        return [
            'id' => $this->id,
            'amount' => format_price($this->amount),
            'payment_method' => $payment_method,
            'payment_date' => date('d-m-Y', strtotime($this->created_at)),
        ];
    }
}
