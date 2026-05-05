<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\Order;
use DB;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'cod' => 'boolean',
            'address_id' => 'required|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        try{
            DB::beginTransaction();

            $combined_order_id = $this->orderService->storeOrder($request->user()->id, $request->address_id, $request->cod, $request->notes);

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }


        return response()->json([
            'combined_order_id' => $combined_order_id,
            'result' => true,
            'message' => translate('Your order has been placed successfully')
        ]);
    }

    public function order_cancel($id)
    {
        $order = Order::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if ($order && ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')) {
            $order->delivery_status = 'cancelled';
            $order->save();

            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->delivery_status = 'cancelled';
                $orderDetail->save();
                product_restock($orderDetail);
            }

            return $this->success(translate('Order has been canceled successfully'));
        } else {
            return  $this->failed(translate('Something went wrong'));
        }
    }
}
