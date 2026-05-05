<?php

namespace App\Exports;

use App\Models\Order;

class OrdersExport extends BaseExport
{
    protected function buildQuery()
    {
        return Order::with(['user', 'shop', 'orderDetails'])
            ->select('id', 'code', 'user_id', 'seller_id', 'grand_total', 'delivery_status', 'payment_type', 'payment_status');
    }

    public function headings(): array
    {
        return [
            translate('Order Code'),
            translate('Num. of Products'),
            translate('Customer'),
            translate('Seller'),
            translate('Shipping Cost'),
            translate('Subtotal'),
            translate('Amount'),
            translate('Delivery Status'),
            translate('Payment method'),
            translate('Payment Status'),
        ];
    }

    public function map($order): array
    {
        return [
            $order->code,
            count($order->orderDetails),
            $order->user != null ? $order->user->name : '',
            $order->shop != null ? $order->shop->name : translate('Inhouse Order'),
            single_price($order->orderDetails->sum('shipping_cost')),
            single_price($order->orderDetails->sum('price')),
            single_price($order->grand_total),
            translate(ucfirst(str_replace('_', ' ', $order->delivery_status))),
            translate(ucfirst(str_replace('_', ' ', $order->payment_type))),
            translate(ucfirst($order->payment_status)),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = Order::with('orderDetails');

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'shipping_cost' => 0,
            'subtotal' => 0,
            'grand_total' => 0,
        ];

        $baseQuery->chunk(1000, function ($orders) use (&$totals) {
            foreach ($orders as $order) {
                $totals['grand_total'] += $order->grand_total;
                $totals['shipping_cost'] += $order->orderDetails->sum('shipping_cost');
                $totals['subtotal'] += $order->orderDetails->sum('price');
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            '',
            '',
            '',
            single_price($totals['shipping_cost']),
            single_price($totals['subtotal']),
            single_price($totals['grand_total']),
            '',
            '',
            '',
        ];
    }
}
