<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        // Settings that don't change per row to save lookup time
        $defaultShopName = get_setting('website_name');
        $defaultShopPhone = get_setting('contact_phone');
        $warehouseAddress = translate('Samh warhouse');
        $refundTimeLimit = (int)get_setting('refund_request_time');

        return [
            'data' => $this->collection->map(function ($order) use ($defaultShopName, $defaultShopPhone, $warehouseAddress, $refundTimeLimit) {

                // Pre-calculate sums from the loaded collection to avoid N+1 queries
                $orderDetails = $order->orderDetails;
                $totalShipping = $orderDetails->sum('shipping_cost');
                $totalPrice = $orderDetails->sum('price');
                $totalTax = $orderDetails->sum('tax');

                // Shop Logic
                $shop = $order->shop;
                $shop_name = $shop ? $shop->name : $defaultShopName;
                $shop_address = $shop ? $shop->address : $warehouseAddress;
                $shop_phone = $shop ? $order->shop_phone : $defaultShopPhone;

                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'user_phone'=> $order->user->phone ?? '', // Added null safety
                    'user_id' => (int) $order->user_id,
                    'shipping_address' => json_decode($order->shipping_address),
                    'payment_type' => $this->formatString($order->payment_type),
                    'pickup_point' => ($order->shipping_type == 'pickup_point') ? new PickupPointResource($order->pickup_point) : null,
                    'shipping_type' => $order->shipping_type,
                    'shipping_type_string' => $order->shipping_type ? $this->formatString($order->shipping_type) : "",
                    'payment_status' => $order->payment_status,
                    'payment_status_string' => $this->formatString($order->payment_status),
                    'delivery_status' => $order->delivery_status,
                    'delivery_status_string' => $order->delivery_status == translate('pending')
                        ? translate("Order Placed")
                        : $this->formatString($order->delivery_status),
                    'grand_total' => format_price(convert_price($order->grand_total)),
                    'plane_grand_total' => $order->grand_total,
                    'coupon_discount' => format_price(convert_price($order->coupon_discount)),
                    'shipping_cost' => format_price(convert_price($totalShipping)),
                    'subtotal' => format_price(convert_price($totalPrice)),
                    'tax' => format_price(convert_price($totalTax)),
                    'date' => Carbon::createFromTimestamp($order->date)->format('d-m-Y'),
                    'cancel_request' => (bool)$order->cancel_request,
                    'manually_payable' => ($order->manual_payment == 0 && $order->payment_method != 'manual_payment') || $order->payment_status == 'unpaid',
                    'shop_name' => $shop_name,
                    'shop_address' => $shop_address,
                    "shop_phone"=> $shop_phone,
                    'products' => $orderDetails->map(function ($detail) use ($refundTimeLimit) {
                        $refundData = $this->getRefundDetails($detail, $refundTimeLimit);
                        return [
                            'id' => $detail->id,
                            "product_name" => $detail->product->name ?? '',
                            'product_id' => $detail->product_id,
                            'thumbnail' => uploaded_asset($detail->product->thumbnail_img ?? null),
                            'variation' => $detail->variation,
                            'price' => format_price(convert_price($detail->price)),
                            'quantity' => $detail->quantity,
                            'tax' => format_price(convert_price($detail->tax)),
                            'shipping_cost' => format_price(convert_price($detail->shipping_cost)),
                            'coupon_discount' => format_price(convert_price($detail->coupon_discount)),
                            'can_be_refunded' => $refundData['can_be_refunded'],
                            'refund_status' => $refundData['refund_status']
                        ];
                    }),
                    'delivery_captin' => $order->delivery_boy ? [
                        'id' => $order->delivery_boy->id,
                        'name' => $order->delivery_boy->name,
                        'phone' => $order->delivery_boy->phone,
                    ]: null,
                    'notes' => $order->additional_info,
                    'links' => ['details' => '']
                ];
            })
        ];
    }

    private function formatString($str)
    {
        return ucwords(str_replace('_', ' ', translate($str)));
    }

    private function getRefundDetails($detail, $limit)
    {
        $product = $detail->product;
        if (!$product) return ['can_be_refunded' => false, 'refund_status' => ""];

        $isExpired = Carbon::now()->gt($detail->created_at->addDays($limit));
        $request = $detail->refund_request;

        $can_be_refunded = (
            $product->refundable != 0 &&
            !$request &&
            !$isExpired &&
            $detail->order->payment_status == 'paid' && // Ensure you access order if not passed
            $detail->order->delivery_status == 'delivered'
        );

        $status = "";
        if ($request) {
            $statuses = [0 => "Pending", 1 => "Approved", 2 => "Rejected"];
            $status = translate($statuses[$request->refund_status] ?? "");
        } else {
            $status = ($product->refundable != 0) ? translate("N/A") : translate("Non-refundable");
        }

        return [
            'can_be_refunded' => $can_be_refunded,
            'refund_status' => $status,
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
