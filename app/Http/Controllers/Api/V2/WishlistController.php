<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WishlistCollection;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Car;
use App\Models\CustomerProduct;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    private const ALLOWED_TYPES = [
        'product' => Product::class,
        'car' => Car::class,
        'customer_product' => CustomerProduct::class,
    ];

    public function index(Request $request)
    {
        $query = Wishlist::query()->where('user_id', auth()->user()->id);

        // Filter by type if provided
        if ($request->has('type') && isset(self::ALLOWED_TYPES[$request->type])) {
            $query->where('wishlistable_type', self::ALLOWED_TYPES[$request->type]);
        }

        return new WishlistCollection($query->latest()->get());
    }

    public function add(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', array_keys(self::ALLOWED_TYPES)),
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $modelClass = self::ALLOWED_TYPES[$request->type];
        
        // Validate that the item exists
        if (!$modelClass::find($request->id)) {
            return response()->json([
                'success' => false,
                'message' => translate('Item not found')
            ], 404);
        }

        $existingWishlist = Wishlist::where([
            'wishlistable_type' => $modelClass,
            'wishlistable_id' => $request->id,
            'user_id' => auth()->user()->id
        ])->first();

        if ($existingWishlist) {
            return response()->json([
                'message' => translate('Item already in wishlist'),
                'is_in_wishlist' => true,
                'type' => $request->type,
                'id' => (int)$request->id,
                'wishlist_id' => (int)$existingWishlist->id
            ], 200);
        }

        $wishlist = Wishlist::create([
            'user_id' => auth()->user()->id,
            'wishlistable_type' => $modelClass,
            'wishlistable_id' => $request->id
        ]);

        return response()->json([
            'message' => translate('Item added to wishlist'),
            'is_in_wishlist' => true,
            'type' => $request->type,
            'id' => (int)$request->id,
            'wishlist_id' => (int)$wishlist->id
        ], 200);
    }

    public function remove(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', array_keys(self::ALLOWED_TYPES)),
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $modelClass = self::ALLOWED_TYPES[$request->type];

        $wishlist = Wishlist::where([
            'wishlistable_type' => $modelClass,
            'wishlistable_id' => $request->id,
            'user_id' => auth()->user()->id
        ])->first();

        if (!$wishlist) {
            return response()->json([
                'message' => translate('Item not in wishlist'),
                'is_in_wishlist' => false,
                'type' => $request->type,
                'id' => (int)$request->id,
                'wishlist_id' => 0
            ], 200);
        }

        $wishlist->delete();

        return response()->json([
            'message' => translate('Item removed from wishlist'),
            'is_in_wishlist' => false,
            'type' => $request->type,
            'id' => (int)$request->id,
            'wishlist_id' => 0
        ], 200);
    }

    public function isProductInWishlist(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', array_keys(self::ALLOWED_TYPES)),
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $modelClass = self::ALLOWED_TYPES[$request->type];

        $wishlist = Wishlist::where([
            'wishlistable_type' => $modelClass,
            'wishlistable_id' => $request->id,
            'user_id' => auth()->user()->id
        ])->first();

        if ($wishlist) {
            return response()->json([
                'message' => translate('Item present in wishlist'),
                'is_in_wishlist' => true,
                'type' => $request->type,
                'id' => (int)$request->id,
                'wishlist_id' => (int)$wishlist->id
            ], 200);
        }

        return response()->json([
            'message' => translate('Item not in wishlist'),
            'is_in_wishlist' => false,
            'type' => $request->type,
            'id' => (int)$request->id,
            'wishlist_id' => 0
        ], 200);
    }
}
