<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Resources\V2\Seller\OrderCollection;
use App\Http\Resources\V2\Seller\OrderDetailResource;
use App\Http\Resources\V2\Seller\OrderItemResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService ;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getOrderList(Request $request)
    {
        $order_query = Order::query();
        if ($request->payment_status != "" || $request->payment_status != null) {
            $order_query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status != "" || $request->delivery_status != null) {
            $delivery_status = $request->delivery_status;
            $order_query->whereIn("id", function ($query) use ($delivery_status) {
                $query->select('order_id')
                    ->from('order_details')
                    ->where('delivery_status', $delivery_status);
            });
        }
        if($request->filled('search')){
            $search = $request->search;
            $order_query->where(function($q) use($search){
                $q->where('code', 'like', '%'.$search.'%');
            });
        }
        if($request->filled('from_date') && $request->filled('to_date')){
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));
                $order_query->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);
        }

        $orders = $order_query->where('seller_id', auth()->user()->id)->latest()->paginate(10);
        return new OrderCollection($orders);
    }

    public function getOrderDetails($id)
    {
        $order_detail = Order::where('id', $id)->where('seller_id', auth()->user()->id)->get();
        return  OrderDetailResource::collection($order_detail);
    }

    public function getOrderItems($id)
    {
        $order_id = Order::select('id')->where('id', $id)->where('seller_id', auth()->user()->id)->first();
        $order_query = OrderDetail::where('order_id', $order_id->id);

        return  OrderItemResource::collection($order_query->get());
    }

    public function update_delivery_status(Request $request) {
        (new OrderService)->handle_delivery_status($request);
        return $this->success(translate('Delivery status has been changed successfully'));
    }

    public function update_payment_status(Request $request) {
        (new OrderService)->handle_payment_status($request);
        return $this->success(translate('Payment status has been changed successfully'));
    }
}
