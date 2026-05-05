<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryHistoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => $data->id,
                    'delivery_boy_id' => intval($data->delivery_boy_id),
                    'order_id' => intval($data->orderable_id),
                    'type' => match($data->orderable_type) {
                        \App\Models\Order::class => 'internal',
                        \App\Models\ExternalOrder::class => 'external',
                        default => 'unknown'
                    },
                    'order_code' => $data->orderable->code,
                    'delivery_status' => $data->delivery_status,
                    'earning' => format_price($data->earning),
                    'collection' => format_price($data->collection),
                    'payment_type' => $data->payment_type,
                    'date' => Carbon::parse($data->created_at)->format('d-m-Y'),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
