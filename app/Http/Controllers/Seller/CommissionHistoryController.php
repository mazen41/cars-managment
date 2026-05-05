<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\Shop;
use Auth;

class CommissionHistoryController extends Controller
{
    public function index(Request $request) {
        $seller_id = null;
        $date_range = null;
        $shop = Auth::user()->shop;
        $commission_history = $shop->commissions()->orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $commission_history = $commission_history->where('created_at', '>=', $date_range1[0]);
            $commission_history = $commission_history->where('created_at', '<=', $date_range1[1]);
        }

        $commission_history = $commission_history->paginate(10);
        return view('seller.commission_history.index', compact('commission_history', 'seller_id', 'date_range'));
    }
}
