<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seller\SellerPaymentResource;
use App\Models\Payout;

class PaymentController extends Controller
{
    public function getHistory(){
        $sellerId = auth()->user()->id;
        $payments = Payout::orderBy('created_at', 'desc')->where('seller_id',$sellerId)->latest()->paginate(10);
        return  SellerPaymentResource::collection($payments);
    }
}
