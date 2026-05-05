<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id'                => $data->id,
                    'order_code'        => $data->code,
                    'total'             => format_price($data->grand_total),
                    'order_date'        => date('d-m-Y', strtotime($data->created_at)),
                    'payment_status'    => $data->payment_status,
                    "payment_status_string" => ucwords(str_replace('_', ' ', translate($data->payment_status))),
                    'delivery_status'   =>  join(" ", explode('_', $data->delivery_status)),
                    'delivery_status_string' => $data->delivery_status == translate('pending') ? translate("Order Placed") : ucwords(str_replace('_', ' ',  translate($data->delivery_status))),
                ];
            })
        ];
    }
}
