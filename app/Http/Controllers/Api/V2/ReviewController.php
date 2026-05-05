<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ReviewCollection;
use App\Models\CarReservation;
use App\Models\DeliveryBoy;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrderDetail;
use App\Models\Shop;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'type' => 'required|in:product,shop',
            'type_id' => 'required|integer'
        ]);
        if($validator->fails()){
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        $reviews = Review::query();
        switch($request->type){
            case('product'):
                $reviews->where('reviewable_type', Product::class)->where('reviewable_id', $request->type_id);
                break;
            case('shop'):
                $reviews->where('reviewable_type', Shop::class)->where('reviewable_id', $request->type_id);
                break;
        }

        return new ReviewCollection($reviews->where('status', 1)->orderBy('updated_at', 'desc')->paginate(10));
    }

   public function submit(Request $request)
{
    $map = [
        'product'  => Product::class,
        'shop'     => Shop::class,
        'delivery' => User::class,
    ];

    $type = $request->type;
    $modelClass = $map[$type] ?? null;

    $validator = Validator::make($request->all(), [
        'type'    => 'required|in:' . implode(',', array_keys($map)),
        'type_id' => "required|integer",
        'rating'  => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => false, 'message' => $validator->errors()->first()]);
    }

    $targetModel = $modelClass::find($request->type_id);
    $user = auth('api')->user();

    if(!$targetModel){
      return response()->json([
        'result'=> false,
        'message' => 'Can not find '.$request->type
      ]);
    }

    $eligibility = $this->checkEligibility($type, $targetModel, $user);
    if (!$eligibility['status']) {
        return response()->json(['result' => false, 'message' => $eligibility['message']]);
    }
    // convert user to delivery boy as it does not follow the relationship standards
    if($modelClass == User::class) {
        $modelClass = DeliveryBoy::class;
        $targetModel = DeliveryBoy::where('user_id',$targetModel->id)->first();
    }

    $exists = Review::where('user_id', $user->id)
        ->where('reviewable_type', $modelClass)
        ->where('reviewable_id', $targetModel->id)
        ->exists();

    if ($exists) {
        return response()->json(['result' => false, 'message' => translate('Already reviewed.')]);
    }


    $review = new Review();
    $review->user_id = $user->id;
    $review->rating = $request->rating;
    $review->comment = $request->comment;
    $review->reviewable()->associate($targetModel);
    $review->viewed = 0;
    $review->save();


    $this->updateTargetRating($targetModel, $modelClass);

    return response()->json([
        'result' => true,
        'message' => translate('Review Submitted Successfully')
    ]);
}


/**
 * Handles the specific business rules for who can review what.
 */
private function checkEligibility($type, $targetModel, $user)
{
    switch ($type) {
        case 'product':
            $canReview = OrderDetail::where('product_id', $targetModel->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('delivery_status', 'delivered');
                })->exists();
            return [
                'status' => $canReview,
                'message' => translate('You have not purchased this product or it is not delivered yet.')
            ];

        case 'shop':
            $canReview = Order::where('seller_id', $targetModel->user_id)
                ->where('delivery_status', 'delivered')
                ->where('user_id', $user->id)
                ->exists() ||
                CarReservation::where('user_id', $user->id)
                ->whereHas('car', function($query) use ($targetModel) {
                    $query->where('user_id', $targetModel->user_id);
                })->exists();
            return [
                'status' => $canReview,
                'message' => translate('You cannot review this shop.')
            ];

        case 'delivery':
            $canReview = Order::where('assign_delivery_boy', $targetModel->id)
                ->where('user_id', $user->id)
                ->where('delivery_status', 'delivered')
                ->exists();
            return [
                'status' => $canReview,
                'message' => translate('Order has not been delivered yet.')
            ];

        default:
            return ['status' => false, 'message' => translate('Invalid review type.')];
    }
}

/**
 * Calculates and updates the average rating for any reviewable model.
 */
private function updateTargetRating($model, $modelClass)
{

    $average = Review::where('reviewable_type', $modelClass)
    ->where('reviewable_id', $model->id)
    ->where('status', 1)
    ->avg('rating');

    $model->rating = $average ?? 0;
    $model->save();
}
}
