<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Utility\CartUtility;
use App\Utility\NagadUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    public function summary(Request $request)
    {
        // $user  = auth()->user();
        $user  = auth('api')->user()->id != null ? User::where('id', auth('api')->user()->id)->first() : null;
        $items = ($user != null) ?
                Cart::where('user_id', $user->id)->active()->get() :
                ($request->has('temp_user_id') ? Cart::where('temp_user_id', $request->temp_user_id)->active()->get() : [] );

        if ($items->isEmpty()) {
            return response()->json([
                'sub_total' => format_price(0.00),
                'tax' => format_price(0.00),
                'shipping_cost' => format_price(0.00),
                'discount' => format_price(0.00),
                'grand_total' => format_price(0.00),
                'grand_total_value' => 0.00,
                'coupon_code' => "",
                'coupon_applied' => false,
            ]);
        }

        $sum = 0.00;
        $subtotal = 0.00;
        $tax = 0.00;
        $coupon_applied = false;
        $coupon_code = '';

        foreach ($items as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            if($cartItem->coupon_applied){
                $coupon_applied = true ;
                $coupon_code = $cartItem->coupon_code;
            }
        }

        $shipping_cost = $items->sum('shipping_cost');
        $discount = $items->sum('discount');
        $sum = ($subtotal + $tax + $shipping_cost) - $discount;

        return response()->json([
            'sub_total' => single_price($subtotal),
            'tax' => single_price($tax),
            'shipping_cost' => single_price($shipping_cost),
            'discount' => single_price($discount),
            'grand_total' => single_price($sum),
            'grand_total_value' => convert_price($sum),
            'coupon_code' => $coupon_code,
            'coupon_applied' => $coupon_applied == 1,
        ]);
    }

    public function count(Request $request)
    {
        $user_id = auth('api')->user()->id;
        $temp_user_id = $request->temp_user_id;
        $items  = ($user_id != null) ?
                    Cart::where('user_id', $user_id)->active()->get() :
                    ($temp_user_id != null ? Cart::where('temp_user_id', $temp_user_id)->active()->get() : [] );

        return response()->json([
            'count' => sizeof($items),
            'status' => true,
        ]);
    }

    public function getList(Request $request)
    {
        $userId = $request->user()->id;
        $tempUserId = $request->get('temp_user_id');

        // Fetch owner IDs based on the user type (logged-in or guest)
        $ownerIds = Cart::active()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($tempUserId, fn ($query) => $query->where('temp_user_id', $tempUserId))
            ->select('owner_id')
            ->groupBy('owner_id')
            ->pluck('owner_id')
            ->toArray();

        $currencySymbol = currency_symbol();
        $shops = [];
        $grandTotal = 0.00;

        foreach ($ownerIds as $ownerId) {
            $shopItems = Cart::active()
                ->when($userId, fn ($query) => $query->where('user_id', $userId))
                ->when($tempUserId, fn ($query) => $query->where('temp_user_id', $tempUserId))
                ->where('owner_id', $ownerId)
                ->get();

            $shopItemsData = [];
            $subTotal = 0.00;

            foreach ($shopItems as $item) {
                $product = Product::find($item->product_id);

                if (!$product) continue;

                $price = cart_product_price($item, $product, false, false) * intval($item->quantity);
                $tax = cart_product_tax($item, $product, false);

                $shopItemsData[] = [
                    'id' => (int) $item->id,
                    'slug'=>  $product->slug,
                    'status' => (int) $item->status,
                    'owner_id' => (int) $item->owner_id,
                    'user_id' => (int) $item->user_id,
                    'product_id' => (int) $item->product_id,
                    'product_name' => $product->getTranslation('name'),
                    'auction_product' => (int) $product->auction_product,
                    'product_thumbnail_image' => uploaded_asset($product->thumbnail_img),
                    'variation' => $item->variation,
                    'price' => single_price($price),
                    'currency_symbol' => $currencySymbol,
                    'tax' => single_price($tax),
                    'shipping_cost' => (float) $item->shipping_cost,
                    'quantity' => (int) $item->quantity,
                  	'base_price' => (float) convert_price(($price / $item->quantity) + $tax),
                    'stroked_price' => (float) home_base_price($product ,false),
                    'lower_limit' => (int) $product->min_qty,
                    'upper_limit' => (int) optional($product->stocks->where('variant', $item->variation)->first())->qty ?? 0,
                ];

                 $subTotal += $price + ($tax * $item->quantity);
            }

            $grandTotal += $subTotal;

            // Fetch shop data
            $shopData = Shop::where('user_id', $ownerId)->first();
            $shopName = $shopData ? $shopData->name : translate('Inhouse');

            $shops[] = [
                'name' => $shopName,
                'owner_id' => (int) $ownerId,
                'sub_total' => single_price($subTotal),
                'cart_items' => $shopItemsData,
            ];
        }

        return response()->json([
            'grand_total' => single_price($grandTotal),
            'data' => $shops,
        ],200, [], JSON_PRESERVE_ZERO_FRACTION);
    }


    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant' => 'nullable|string|exists:product_stocks,variant',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        $user_id =  auth('api')->user()->id != null ? auth('api')->user()->id : null;

        $product = Product::findOrFail($request->id);


        if ($product->min_qty > $request->quantity) {
            return response()->json([
                'result' => false,
                'message' => translate("Minimum") . " {$product->min_qty} " . translate("item(s) should be ordered")
            ], 200);
        }

        $variant = $request->variant;
        $tax = 0;
        $quantity = $request->quantity;

        $product_stock = $product->stocks->where('variant', $variant)->first();
        $product_stocks_count = $product->stocks->count();

        if($product_stocks_count > 0 && $product_stock == null){
            return response()->json([
                'result' => false,
                'message' => translate('You must select product variant')
            ], 200);
        }

         $cart = Cart::firstOrNew([
                'variation' => $variant,
                'user_id' => $user_id,
                'product_id' => $request['id']
            ]);


        $variant_string = $variant != null && $variant != "" ? translate("for") . " ($variant)" : "";

        if ($cart->exists && $product->digital == 0) {
            if ($product->auction_product == 1 && ($cart->product_id == $product->id)) {
                return response()->json([
                    'result' => false,
                    'message' => translate('This auction product is already added to your cart.')
                ], 200);
            }
            if ($product_stock->qty < $cart->quantity + $request['quantity']) {
                if ($product_stock->qty == 0) {
                    return response()->json([
                        'result' => false,
                        'message' => translate("Stock out")
                    ], 200);
                } else {
                    return response()->json([
                        'result' => false,
                        'message' => translate("Only") . " {$product_stock->qty} " . translate("item(s) are available") . " {$variant_string}"
                    ], 200);
                }
            }
            if ($product->digital == 1 && ($cart->product_id == $product->id)) {
                return response()->json([
                    'result' => false,
                    'message' => translate('Already added this product')
                ]);
            }
            $quantity = $cart->quantity + $request['quantity'];
        }

        $price = CartUtility::get_price($product, $product_stock, $request->quantity);
        $tax = CartUtility::tax_calculation($product, $price);
        CartUtility::save_cart_data($cart, $product, $price, $tax, $quantity);

        return response()->json([
            'result' => true,
            'message' => translate('Product added to cart successfully')
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:carts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => $validator->errors()->first()], 200);
        }
        $cart = Cart::find($request->id);
        if ($cart != null) {
            $product = Product::find($cart->product_id);
            if ($product->auction_product == 1) {
                return response()->json(['result' => false, 'message' => translate('Maximum available quantity reached')], 200);
            }
            if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
                $cart->update([
                    'quantity' => $request->quantity
                ]);

                return response()->json(['result' => true, 'message' => translate('Cart updated')], 200);
            } else {
                return response()->json(['result' => false, 'message' => translate('Maximum available quantity reached')], 200);
            }
        }

        return response()->json(['result' => false, 'message' => translate('Something went wrong')], 200);
    }

    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carts' => 'required',
            'carts.*.id' => 'required|integer|exists:carts,id',
            'carts.*.quantity' => 'required|integer|min:1',
        ]);
        if($validator->fails()){
            return response()->json(['result' => false, 'message' => $validator->errors()->first()], 200);
        }
        if (!empty($request->carts)) {
            foreach ($request->carts as $single) {
                $cart_item = Cart::where('id', $single['id'])->where('user_id', auth('api')->user()->id)->first();
                if(!$cart_item){
                    return response()->json(['result' => false, 'message' => translate('Cart item not found')], 200);
                }
                $product = Product::where('id', $cart_item->product_id)->first();

                if ($product->min_qty > $single['quantity']) {
                    return response()->json(['result' => false, 'message' => translate("Minimum") . " {$product->min_qty} " . translate("item(s) should be ordered for") . " {$product->name}"], 200);
                }

                $stock = $cart_item->product->stocks->where('variant', $cart_item->variation)->first()->qty;
                $variant_string = $cart_item->variation != null && $cart_item->variation != "" ? " ($cart_item->variation)" : "";
                if ($stock >= $single['quantity'] || $product->digital == 1) {
                    $cart_item->update([
                        'quantity' => $single['quantity']
                    ]);
                    if($cart_item->coupon_applied){
                        $cart_item->update([
                            'discount' => 0.00,
                            'coupon_code' => "",
                            'coupon_applied' => 0
                        ]);
                    }
                } else {
                    if ($stock == 0) {
                        return response()->json(['result' => false, 'message' => translate("No item is available for") . " {$product->name}{$variant_string}," . translate("remove this from cart")], 200);
                    } else {
                        return response()->json(['result' => false, 'message' => translate("Only") . " {$stock} " . translate("item(s) are available for") . " {$product->name}{$variant_string}"], 200);
                    }
                }
            }

            return response()->json(['result' => true, 'message' => translate('Cart updated')], 200);
        } else {
            return response()->json(['result' => false, 'message' => translate('Cart is empty')], 200);
        }
    }

    public function destroy($id)
    {
        Cart::destroy($id);
        return response()->json(['result' => true, 'message' => translate('Product is successfully removed from your cart')], 200);
    }

    public function guestCustomerInfoCheck(Request $request){
        $user = addon_is_activated('otp_system') ?
                User::where('email', $request->email)->orWhere('phone','+'.$request->phone)->first() :
                User::where('email', $request->email)->first();

        return response()->json([
            'result' => ($user != null) ? true : false
        ]);
    }

    public function updateCartStatus(Request $request)
    {
        $product_ids = $request->product_ids;
        $user_id = auth('api')->user()->id;
        $temp_user_id = $request->temp_user_id;
        $carts  = ($user_id != null) ?
                    Cart::where('user_id', $user_id)->get() :
                    ($temp_user_id != null ? Cart::where('temp_user_id', $temp_user_id)->get() : [] );

        $carts->toQuery()->update(['status' => 0]);
        if($product_ids != null){
            $carts->toQuery()->whereIn('product_id', $product_ids)->update(['status' => 1]);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Cart status updated successfully')
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $userId = auth('api')->user()->id;
        $cartIds = json_decode($request->cart_ids);
        Cart::where('user_id', $userId)->whereIn('id', $cartIds)->delete();
        return response()->json(['result' => true, 'message' => translate('Products successfully removed from your cart')], 200);
    }
}

