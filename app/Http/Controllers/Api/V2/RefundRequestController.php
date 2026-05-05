<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ClubPoint;
use App\Http\Resources\V2\RefundRequestCollection;
use App\Models\OrderDetail;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{

    public function get_list()
    {
        $refunds = RefundRequest::where('user_id', auth()->user()->id)->latest()->paginate(10);

        return new RefundRequestCollection($refunds);
    }

    public function send(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_detail_id' => 'required|exists:order_details,id',
            'reason' => 'required|string|max:255',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

         $detail = OrderDetail::where('id', $request->order_detail_id)->first();

         // Check if the product is refundable, the order is paid, and the delivery status is delivered
         $isExpired = (time() - $detail->created_at->timestamp) > (get_setting('refund_request_time') * 24 * 60 * 60);
         $requestExists = RefundRequest::where('order_detail_id', $detail->id)->exists();

         if ($requestExists) {
             return response()->json([
                 'success' => false,
                 'message' => translate('Refund request already exists for this order detail.')
             ], 400);
         }

         if (!$detail->product || !$detail->product->refundable) {
             return response()->json([
                 'success' => false,
                 'message' => translate('This product is not eligible for a refund.')
             ], 400);
         }

         if ($isExpired) {
             return response()->json([
                 'success' => false,
                 'message' => translate('The refund request period has expired for this order detail.')
             ], 400);
         }

         if ($detail->order->payment_status != 'paid') {
             return response()->json([
                 'success' => false,
                 'message' => translate('Refund requests can only be made for paid orders.')
             ], 400);
         }
        $order_detail = OrderDetail::where('id', $request->order_detail_id)->first();
        $refund = new RefundRequest;
        $refund->user_id = auth()->user()->id;
        $refund->order_id = $order_detail->order_id;
        $refund->order_detail_id = $order_detail->id;
        $refund->seller_id = $order_detail->seller_id;
        $refund->seller_approval = 0;
        $refund->reason = $request->reason;
        $refund->admin_approval = 0;
        $refund->admin_seen = 0;
        $refund->refund_amount = $order_detail->price + $order_detail->tax;
        $refund->refund_status = 0;
        $refund->save();

        return response()->json([
            'success' => true,
            'message' => translate('Request Sent')
        ]);


    }
}
