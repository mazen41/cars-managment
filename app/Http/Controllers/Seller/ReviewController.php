<?php

namespace App\Http\Controllers\Seller;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Auth;
use DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::where('reviewable_type', Product::class)
                    ->paginate(9);

        foreach ($reviews as $review) {
            $review->viewed = 1;
            $review->save();
        }

        return view('seller.reviews', compact('reviews'));
    }

}
