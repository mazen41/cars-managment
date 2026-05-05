<?php

namespace App\Http\Resources\V2\Inspector;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentSummaryResource extends JsonResource
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
            'total_earnings' => $this->resource['total_earnings'] ?? 0,
            'total_payments_received' => $this->resource['total_payments_received'] ?? 0,
            'pending_payments' => $this->resource['pending_payments'] ?? 0,
            'current_balance' => $this->resource['current_balance'] ?? 0,
            'formatted_total_earnings' => format_price($this->resource['total_earnings'] ?? 0),
            'formatted_total_payments_received' => format_price($this->resource['total_payments_received'] ?? 0),
            'formatted_pending_payments' => format_price($this->resource['pending_payments'] ?? 0),
            'formatted_current_balance' => format_price($this->resource['current_balance'] ?? 0),
            'this_month' => [
                'earnings' => $this->resource['this_month']['earnings'] ?? 0,
                'payments_received' => $this->resource['this_month']['payments_received'] ?? 0,
                'formatted_earnings' => format_price($this->resource['this_month']['earnings'] ?? 0),
                'formatted_payments_received' => format_price($this->resource['this_month']['payments_received'] ?? 0),
            ],
            'last_payment' => $this->when(
                isset($this->resource['last_payment']),
                function () {
                    return [
                        'amount' => $this->resource['last_payment']['amount'],
                        'formatted_amount' => format_price($this->resource['last_payment']['amount']),
                        'date' => $this->resource['last_payment']['date'],
                        'payment_method' => $this->resource['last_payment']['payment_method'],
                    ];
                }
            ),
        ];
    }
}