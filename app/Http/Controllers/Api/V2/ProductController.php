<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\Shop;
use App\Models\Color;
use App\Models\Product;
use App\Models\FlashDeal;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use App\Utility\CategoryUtility;
use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\LastViewedProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Resources\V2\Seller\BrandCollection;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return new ProductMiniCollection(Product::latest()->paginate(10));
    }

     public function inhouse(Request $request)
    {
        return $this->filterProducts($request, 'admin');
    }

    public function index_seller(Request $request)
    {
        return $this->filterProducts($request, 'seller');
    }

    private function filterProducts(Request $request, $type = 'admin', $isFeatured = false, $isTodaysDeal = false)
    {
        $validator = Validator::make($request->all(), [
            "page" => "nullable|integer|min:1",
            "per_page" => "nullable|integer|min:1|max:100",
            "sort_order" => "nullable|string|in:asc,desc",
            "search" => "nullable|string|max:255",
            "brand_id" => "nullable|integer|exists:car_brands,id",
            "category_id" => "nullable|integer|exists:categories,id",
            "subcategory_id" => "nullable|integer|exists:categories,id",
            'filter_attributes' => 'nullable|array',
            'filter_attributes.*' => 'array',
            'filter_attributes.*.*' => 'string',
            "min_price" => "nullable|numeric|min:0",
            "max_price" => "nullable|numeric|min:0",
            "country_id" => "nullable|integer|exists:countries,id",
            "state_id" => "nullable|integer|exists:states,id",
            "city_id" => "nullable|integer|exists:cities,id",
            "address_id" => "nullable|integer|exists:addresses,id"
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $query = Product::with([
            "brand:id,name,logo",
            "main_category:id,name,icon",
            "shop"
        ])->isApprovedPublished()
        ->where('added_by', $type);

        // Featured
        if($isFeatured){
            $query->where('featured', 1);
        }

        // Todays Deal
        if($isTodaysDeal) {
            $query->where('todays_deal', 1);
        }

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                    $q->where('name', "LIKE", "%{$search}%")
                    ->orWhere("description", "LIKE", "%{$search}%")
                    ->orWhereHas("brand", function ($brandQuery) use ($search) {
                        $brandQuery->where("name", "LIKE", "%{$search}%");
                    });
            });
        }

        // Brand filter
        if ($request->filled("brand_id")) {
            $query->where('brand_id', $request->brand_id);
        }

        // Category filter
        if ($request->filled("category_id")) {
            if($request->filled("subcategory_id")){
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('id', $request->subcategory_id);
                });
            } else {
                $query->where('category_id', $request->category_id);
            }
        }

        // Attributes filter
        if ($request->filled("filter_attributes")) {
            $query->byAttributes($request->filter_attributes);
        }


        // Price range filter
        if ($request->filled("min_price") || $request->filled("max_price")) {
            $minPrice = $request->get("min_price", 0);
            $maxPrice = $request->get("max_price", PHP_FLOAT_MAX);
            $query->byPriceRange($minPrice, $maxPrice);
        }

        // State filter
        if($request->filled("state_id")){
            //TODO:implement state filter
        }

        $userLat = null;
        $userLng = null;

        if ($request->filled('address_id') && $type != 'admin') {
            $address = Address::find($request->address_id);
            if ($address && $address->latitude && $address->longitude) {
                $userLat = $address->latitude;
                $userLng = $address->longitude;
            }
        }

        //  Distance Sorting Logic
        if ($userLat && $userLng) {
            $query->select('products.*')
                ->join('users', 'products.user_id', '=', 'users.id')
                ->join('shops', 'users.id', '=', 'shops.user_id')
                ->selectRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(shops.delivery_pickup_latitude))
                    * cos(radians(shops.delivery_pickup_longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(shops.delivery_pickup_latitude)))) AS distance",
                    [$userLat, $userLng, $userLat]
                )
                // Sort: Non-null distances first (distance IS NULL is 0 for valid, 1 for null), then by distance
                ->orderByRaw('distance IS NULL ASC, distance ASC');
        } else {
            // Default Sorting
            $sortBy = $request->get("sort_by", "created_at");
            $sortOrder = $request->get("sort_order", "desc");

            // Ensure we prefix the column to avoid ambiguity if joins happen elsewhere
            $query->orderBy("products.$sortBy", $sortOrder);
        }

        // Finalize and Paginate
        $perPage = $request->get("per_page", 15);
        $products = $query->paginate($perPage);

        return new ProductMiniCollection($products);
    }

    public function product_details($slug_or_id)
    {
        $product = Product::where('slug', $slug_or_id)->orWhere('id', $slug_or_id)
        ->with([
            "brand:id,name,logo",
            "main_category:id,name,icon",
            "categories:id,name",
            "subcategories:id,name,icon",
            "stocks"
        ])
        ->get();
        return new ProductDetailCollection($product);
    }

    public function getPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:products,id',
            'variants' => 'nullable|string',
            'color' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "result" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $product = Product::where("slug", $request->id)->orWhere('id', $request->id)->first();
        $str = '';
        $tax = 0;
        $quantity = 1;



        if ($request->has('quantity') && $request->quantity != null) {
            $quantity = $request->quantity;
        }

        if ($request->has('color') && $request->color != null) {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }

        $product_stock = $product->stocks->where('variant', $str)->first();

        if(!$product_stock) {
            return response()->json(
                [
                    'result' => false,
                    'message' => translate('This product is not available with this variant.'),
                ],
                200
            );
        }

        $price = $product_stock->price;


        if ($product->wholesale_product) {
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $quantity)->where('max_qty', '>=', $quantity)->first();
            if ($wholesalePrice) {
                $price = $wholesalePrice->price;
            }
        }

        $stock_qty = $product_stock->qty;
        $stock_txt = $product_stock->qty;
        $max_limit = $product_stock->qty;

        if ($stock_qty >= 1 && $product->min_qty <= $stock_qty) {
            $in_stock = 1;
        } else {
            $in_stock = 0;
        }

        //Product Stock Visibility
        if ($product->stock_visibility_state == 'text') {
            if ($stock_qty >= 1 && $product->min_qty < $stock_qty) {
                $stock_txt = translate('In Stock');
            } else {
                $stock_txt = translate('Out Of Stock');
            }
        }

        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        // taxes
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;

        return response()->json(

            [
                'result' => true,
                'data' => [
                    'price' => single_price($price * $quantity),
                    'stock' => $stock_qty,
                    'stock_txt' => $stock_txt,
                    'digital' => $product->digital,
                    'variant' => $str,
                    'variation' => $str,
                    'max_limit' => $max_limit,
                    'in_stock' => $in_stock,
                    'image' => $product_stock->image == null ? "" : uploaded_asset($product_stock->image)
                ]

            ]
        );
    }

    public function seller($id, Request $request)
    {
        $shop = Shop::findOrFail($id);
        $products = Product::where('added_by', 'seller')->where('user_id', $shop->user_id);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);
        return new ProductMiniCollection($products->latest()->paginate(10));
    }

    public function categoryProducts($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->first();
        $category = Category::with('childrenCategories')->find($category->id);
        $products = $category->products();

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function brand($slug, Request $request)
    {
        $brand = Brand::where('slug', $slug)->first();
        $products = Product::where('brand_id', $brand->id)->physical();
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function getBrands()
    {
        $brands = Brand::all();

        return BrandCollection::collection($brands);
    }

    public function todaysDeal(Request $request)
    {
       $market = match($request->market){
        'admin' =>'admin',
        'seller' => 'seller',
        default => 'admin'
       };

        return $this->filterProducts($request, $market, false, true);
    }

    public function flashDeal()
    {
        return Cache::remember('app.flash_deals', 86400, function () {
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured(Request $request)
    {
        $market = match($request->market){
            'admin' =>'admin',
            'seller' => 'seller',
            default => 'admin'
        };

        return $this->filterProducts($request, $market, true, false);
    }

    public function similar(Request $request, Product $product) {
        $limit = $request->get('limit', 5) > 20 ? 20 : $request->get('limit', 5);
        $subcategory_id = $product->subcategory_id ? $product->subcategory_id : $product->main_category->id;

        $products = Product::whereHas('categories', function ($query) use ($subcategory_id) {
            $query->where('product_categories.category_id', $subcategory_id);
        })->where('id', '!=', $product->id)->physical();

        return new ProductMiniCollection($products->latest()->paginate($limit));
    }

    public function digital()
    {
        $products = Product::digital();
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function bestSeller()
    {
        $products = Product::orderBy('num_of_sale', 'desc')->physical();
        return new ProductMiniCollection(filter_products($products)->limit(20)->get());
    }

    public function frequentlyBought($slug)
    {
        $product = Product::where("slug", $slug)->first();
        $products = get_frequently_bought_products($product);
        return new ProductMiniCollection($products);
    }

    public function topFromSeller($slug)
    {
        $product = Product::where("slug", $slug)->first();
        $products = Product::where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->physical();
        return new ProductMiniCollection(filter_products($products)->limit(10)->get());
    }


    public function search(Request $request)
    {
        $category_ids = [];
        $brand_ids = [];

        if ($request->categories != null && $request->categories != "") {
            $category_ids = explode(',', $request->categories);
        }

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;


        $products = Product::query();

        $products->where('published', 1)->physical();

        if (!empty($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($category_ids)) {
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }

            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }

            $products->whereIn('category_id', $category_ids);
        }

        if ($name != null && $name != "") {
            $products->where(function ($query) use ($name) {
                foreach (explode(' ', trim($name)) as $word) {
                    $query->where('name', 'like', '%' . $word . '%')->orWhere('tags', 'like', '%' . $word . '%')->orWhereHas('product_translations', function ($query) use ($word) {
                        $query->where('name', 'like', '%' . $word . '%');
                    });
                }
            });
            SearchUtility::store($name);
            $case1 = $name . '%';
            $case2 = '%' . $name . '%';

            $products->orderByRaw("CASE
                WHEN name LIKE '$case1' THEN 1
                WHEN name LIKE '$case2' THEN 2
                ELSE 3
                END");
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }



        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        return new ProductMiniCollection(filter_products($products)->paginate(10));
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color') && $request->color != "") {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }
        return   $this->calc($product, $str, $request, $tax);
    }

    public function lastViewedProducts(){
        $lastViewedProducts = getLastViewedProducts();
        return new LastViewedProductCollection( $lastViewedProducts);
    }
}
