<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CarrierCollection;
use App\Http\Resources\V2\PickupPointResource;
use App\Models\Address;
use App\Models\Carrier;
use App\Models\Cart;
use App\Models\City;
use App\Models\Country;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function pickup_list()
    {
        $pickup_point_list = PickupPoint::where('pick_up_status', '=', 1)->get();

        return PickupPointResource::collection($pickup_point_list);
    }

    public function shipping_cost(Request $request)
    {
        $messages = [
            'seller_list.required' => 'The seller list field is required.',
            'seller_list.array' => 'The seller list must be an array.',
            'address_id.required' => 'The address id field is required.',
            'address_id.integer' => 'The address id must be an integer.',
            'seller_list.*.shipping_type.required' => 'The shipping type field is required for each seller.',
            'seller_list.*.shipping_type.string' => 'The shipping type must be a string for each seller.',
            'seller_list.*.shipping_type.in' => 'The shipping type must be either pickup_point, home_delivery, or carrier for each seller.',
            'seller_list.*.shipping_id.required_if' => 'The shipping id field is required for each seller when the shipping type is carrier or pickup_point.',
            'seller_list.*.shipping_id.integer' => 'The shipping id must be an integer for each seller.',
        ];
        $validator = Validator::make($request->all(), [
            'seller_list' => 'required|array',
            'address_id' => 'required|integer',
            'seller_list.*.shipping_type' => 'required|string|in:pickup_point,home_delivery,carrier',
            'seller_list.*.shipping_id' => 'required_if:seller_list.*.shipping_type,carrier,pickup_point|integer',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => $validator->errors()], 422);
        }

        $userId = auth('api')->check() ? auth('api')->user()->id : null;

        $main_carts = Cart::where('user_id', $userId)->active()->get();

        if($main_carts->isEmpty()){
            return response()->json(['result' => false, 'message' => 'No cart items found'], 404);
        }
        // Validate that the seller list matches with the cart items
        if($main_carts->pluck('owner_id')->unique()->count() != count($request->seller_list)){
            return response()->json(['result' => false, 'message' => 'Seller list does not match with cart items'], 400);
        }

        foreach($main_carts->pluck('owner_id')->unique() as $owner_id){
            if(!collect($request->seller_list)->pluck('seller_id')->contains($owner_id)){
                return response()->json(['result' => false, 'message' => 'Seller list does not match with cart items'], 400);
            }
        }

        $shipping_info = null;
        foreach ($request->seller_list as $key => $seller) {
            $seller['shipping_cost'] = 0;

            $carts = $main_carts->toQuery()->where("owner_id", $seller['seller_id'])->get();


            $address = Address::where([
                    'id'=> $request->address_id,
                    'user_id'   => auth('api')->user()->id
                ])->first();

            if(!$address){
                return response()->json(['result' => false, 'message' => 'Address not found'], 404);
            }

            $shipping_info['country_id'] = $address->country_id;
            $shipping_info['city_id'] = $address->city_id;




            foreach ($carts as $key => $cartItem) {
                $cartItem['shipping_cost'] = 0;

                if ($seller['shipping_type'] == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $seller['shipping_id'];
                } else if ($seller['shipping_type'] == 'home_delivery') {
                    $cartItem['shipping_type'] = 'home_delivery';
                    $cartItem['pickup_point'] = 0;

                    $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);

                } else if ($seller['shipping_type'] == 'carrier') {
                    $cartItem['shipping_type'] = 'carrier';
                    $cartItem['pickup_point'] = 0;
                    $cartItem['carrier_id'] = $seller['shipping_id'];
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info, $seller['shipping_id']);
                }

                $cartItem->save();
                // resetting shipping cost for other items of the same seller to avoid duplication in total shipping cost calculation
                $cartItem['shipping_cost'] = 0;
            }
        }

        //Total shipping cost $calculate_shipping
        $total_shipping_cost = $main_carts->fresh()->toQuery()->sum('shipping_cost');
        $carts = array();
        return response()->json(['result' => true, 'shipping_type' => get_setting('shipping_type'), 'value' => convert_price($total_shipping_cost), 'value_string' => format_price(convert_price($total_shipping_cost))], 200);
    }

    public function getDeliveryInfo(Request $request)
    {
        $userId = auth('api')->check() ? auth('api')->user()->id : null;


        $cartQuery = Cart::where('user_id', $userId)->active();

        // Fetch cart items for the current user
        $cartItems = $cartQuery->get();

        // Determine shipping information
        $shipping_info = [];
        if ($userId) {
            $cart = $cartItems->first();
            if ($cart) {
                $request->address_id
                    ? $address = Address::where([
                        'id'=> $request->address_id,
                        'user_id'   => auth('api')->user()->id
                    ])->first()
                    : $address = Address::where('id', $cart->address_id)->first();
                $shipping_info = [
                    'country_id' => $address->country_id ?? null,
                    'city_id' => $address->city_id ?? null,
                ];
            }
        }

        // Retrieve distinct owner IDs from the cart items
        $owner_ids = $cartItems->pluck('owner_id')->unique();

        $shops = [];
        if ($owner_ids->isNotEmpty()) {
            foreach ($owner_ids as $owner_id) {
                // // Fetch shop-specific cart items and convert to array
                // $shopItems = $cartItems->where('owner_id', $owner_id)->values()->toArray();

                // $shop_items_data = array_map(function ($item) {
                //     $product = Product::find($item['product_id']);
                //     return [
                //         'id' => (int) $item['id'],
                //         'owner_id' => (int) $item['owner_id'],
                //         'user_id' => (int) $item['user_id'],
                //         'temp_user_id' => (int) $item['temp_user_id'],
                //         'product_id' => (int) $item['product_id'],
                //         'product_name' => $product->getTranslation('name'),
                //         'product_thumbnail_image' => uploaded_asset($product->thumbnail_img),
                //         'product_is_digital' => $product->digital == 1,
                //     ];
                // }, $shopItems);

                // Fetch shop details
                $shop_data = Shop::where('user_id', $owner_id)->first();
                $shop = [
                    'name' => $shop_data->name ?? translate('Inhouse'),
                    'seller_id' => (int) $owner_id,
                    //'cart_items' => $shop_items_data,
                    'carriers' => seller_base_carrier_list($owner_id, $userId, null, $shipping_info),
                    'pickup_points' => [],
                ];

                // Add pickup points if enabled
                if (get_setting('pickup_point') == 1) {
                    $pickup_points = PickupPoint::where('pick_up_status', 1)->get();
                    $shop['pickup_points'] = PickupPointResource::collection($pickup_points);
                }

                $shops[] = $shop;
            }
        }

        return response()->json($shops);
    }
}
