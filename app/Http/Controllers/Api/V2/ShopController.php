<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ShopCollection;
use App\Http\Resources\V2\ShopDetailsCollection;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Cache;
use App\Models\Address;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        try {
            $shop_query = Shop::query();

            if ($request->filled('name')) {
                $shop_query->where("name", 'like', "%{$request->name}%");
                SearchUtility::store($request->name);
            }

            $user = auth('api')->user();
            if ($user) {
                $address = Address::where('user_id', $user->id)
                                ->orderBy('set_default', 'desc')
                                ->first();

                if ($address && $address->latitude && $address->longitude) {
                    $userLat = $address->latitude;
                    $userLng = $address->longitude;
                    // Haversine formula to calculate distance
                    $shop_query->select('shops.*')
                        ->selectRaw('CASE
                            WHEN delivery_pickup_latitude IS NULL OR delivery_pickup_longitude IS NULL
                            THEN 999999
                            ELSE (
                                6371 * acos(
                                    cos(radians(?))
                                    * cos(radians(delivery_pickup_latitude))
                                    * cos(radians(delivery_pickup_longitude) - radians(?))
                                    + sin(radians(?))
                                    * sin(radians(delivery_pickup_latitude))
                                )
                            )
                        END AS distance', [$userLat, $userLng, $userLat])
                        ->orderBy('distance', 'asc');
                }
            }

            return new ShopCollection(
                $shop_query->whereIn('user_id', verified_sellers_id())
                          ->paginate(10)
            );

        } catch (\Exception $e) {
            // Handle or log the error appropriately
            return response()->json([
                'success'=> false,
                'message' => 'An error occurred while processing your request.'
            ], 500);
        }
    }

    public function info($id)
    {
        $shop = Shop::where('id', $id)->first();
        if(!$shop){
            return response()->json([
                'success'=> false,
                'message' => 'Shop not found'
            ], 404);
        }
        return new ShopDetailsCollection($shop);
    }

    public function shopOfUser($id)
    {
        return new ShopCollection(Shop::where('user_id', $id)->get());
    }

    public function allProducts($id)
    {
        $shop = Shop::findOrFail($id);
        return new ProductCollection(Product::where('user_id', $shop->user_id)->where('published', 1)->latest()->paginate(10));
    }

    public function topSellingProducts($id)
    {
        $shop = Shop::findOrFail($id);

        return Cache::remember("app.top_selling_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::where('user_id', $shop->user_id)->where('published', 1)->orderBy('num_of_sale', 'desc')->limit(10)->get());
        });
    }

    public function featuredProducts($id)
    {
        $shop = Shop::findOrFail($id);

        return Cache::remember("app.featured_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::where(['user_id' => $shop->user_id, 'seller_featured' => 1])->where('published', 1)->latest()->limit(10)->get());
        });
    }

    public function newProducts($id)
    {
        $shop = Shop::findOrFail($id);

        return Cache::remember("app.new_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::where('user_id', $shop->user_id)->where('published', 1)->orderBy('created_at', 'desc')->limit(10)->get());
        });
    }

    public function brands($id)
    {
    }
}
