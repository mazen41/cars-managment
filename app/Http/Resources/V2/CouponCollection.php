<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CouponCollection extends ResourceCollection
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

                $coupon_products_details = [];
                $order_discount_details = null;
                $user_type = $data->user->user_type;
                $shop = $data->user->shop;
                $shop_name = ($user_type == 'admin' || $user_type == 'staff') ? get_setting('website_name') : ( $shop->name ?? '');

                if ($data->type == 'product_base') {
                    $products = json_decode($data->details);
                    foreach ($products as  $key => $product) {
                        array_push($coupon_products_details, (object)[
                            'product_id' =>  $product->product_id,
                            // 'thumbnail_img' => uploaded_asset($product->thumbnail_img),
                        ]);
                    }


                }elseif($data->type == 'external_order'){

                    $order_discount_details = json_decode($data->details);
                    $arr['min_buy'] = single_price(intval($order_discount_details->min_buy));
                    $arr['max_discount'] =  $order_discount_details->max_discount;
                    $arr['provider'] =  $order_discount_details->provider;
                    $arr['base_discount'] =  $order_discount_details->base_discount;
                    $order_discount_details = $arr;
                    $shop_name = translate(ucfirst( $arr['provider']));

                } else {

                    $order_discount_details = json_decode($data->details);
                    $arr['min_buy'] = single_price(intval($order_discount_details->min_buy));
                    $arr['max_discount'] =  $order_discount_details->max_discount;
                    $order_discount_details = $arr;
                }
                 if(isset($order_discount_details['base_discount'])){
                  $base_discount = $order_discount_details['base_discount'] == 'shipping_only' ? translate('Shipping cost') : translate('Subtotal');
                } else {
                    $base_discount = translate('Subtotal');
                }



                if ($user_type == 'admin' || ($shop != null && $shop->verification_status)) {
                    return [
                        'id' => (int)$data->id,
                        'user_type' => $user_type,
                        'shop_id' => $shop->id ?? '',
                        'shop_name' => $shop_name,
                        'shop_slug' => $shop->slug ?? '',
                        'coupon_type' => $data->type,
                        'code' => $data->code,
                        'discount' => $data->discount_type == 'percent' ? $data->discount : single_price($data->discount),
                        'coupon_product_details' => $coupon_products_details,
                        'coupon_discount_details' => $order_discount_details,
                        'discount_type' => $data->discount_type,
                        'base_discount' => $base_discount,
                        'start_date' => $data->start_date,
                        'end_date' => $data->end_date,
                        'start_date_string' => \Carbon\Carbon::parse($data->start_date)->format('d/m/Y'),
                        'end_date_string' => \Carbon\Carbon::parse($data->end_date)->format('d/m/Y'),
                    ];
                }
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
