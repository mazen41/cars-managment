<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Upload;
use App\Models\User;
use Artisan;
use Cache;
use Carbon\Carbon;
use DB;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_dashboard(Request $request)
    {
        $root_categories = Category::where('level', 0)->get();

        $data['cached_graph_data'] = Cache::remember('cached_graph_data', 86400, function () use ($root_categories) {
            $num_of_sale_data = null;
            $qty_data = null;
            foreach ($root_categories as $key => $category) {
                $category_ids = \App\Utility\CategoryUtility::children_ids($category->id);
                $category_ids[] = $category->id;

                $products = Product::with('stocks')->whereIn('category_id', $category_ids)->get();
                $qty = 0;
                $sale = 0;
                foreach ($products as $key => $product) {
                    $sale += $product->num_of_sale;
                    foreach ($product->stocks as $key => $stock) {
                        $qty += $stock->qty;
                    }
                }
                $qty_data .= $qty . ',';
                $num_of_sale_data .= $sale . ',';
            }
            $item['num_of_sale_data'] = $num_of_sale_data;
            $item['qty_data'] = $qty_data;

            return $item;
        });

        $data['root_categories'] = $root_categories;


        $data['total_products'] = Product::where('approved', 1)->where('published', 1)->count();
        $data['total_inhouse_products'] = Product::where('approved', 1)->where('published', 1)->where('added_by', 'admin')->count();
        $data['total_sellers_products'] = Product::where('approved', 1)->where('published', 1)->where('added_by', '!=', 'admin')->count();
        $data['total_categories'] = Category::count();

        $data['top_categories'] = Product::select('categories.name', 'categories.id', DB::raw('SUM(grand_total) as total'))
            ->leftJoin('order_details', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.delivery_status', 'delivered')
            ->groupBy('categories.id')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();
        $data['total_brands'] = Brand::count();
        $data['top_brands'] = Product::select('brands.name', 'brands.id', DB::raw('SUM(grand_total) as total'))
            ->leftJoin('order_details', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('orders.delivery_status', 'delivered')
            ->groupBy('brands.id')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();
        $data['total_sale'] = Order::where('delivery_status', 'delivered')->sum('grand_total');
        $data['sale_this_month'] = Order::whereMonth('created_at', Carbon::now()->month)->sum('grand_total');
        $data['admin_sale_this_month'] = Order::select(DB::raw('COALESCE(users.user_type, "admin") as user_type'), DB::raw('COALESCE(SUM(grand_total), 0) as total_sale'))
            ->leftJoin('users', 'orders.seller_id', '=', 'users.id')
            ->whereRaw('users.user_type = "admin"')
            ->whereMonth('orders.created_at', Carbon::now()->month)
            ->first();
        $data['seller_sale_this_month'] = Order::select(DB::raw('COALESCE(users.user_type, "seller") as user_type'), DB::raw('COALESCE(SUM(grand_total), 0) as total_sale'))
            ->leftJoin('users', 'orders.seller_id', '=', 'users.id')
            ->whereRaw('users.user_type = "seller"')
            ->whereMonth('orders.created_at', Carbon::now()->month)
            ->first();
        $sales_stat = Order::select('orders.user_id', 'users.name', 'users.user_type', 'users.avatar_original', DB::raw('SUM(grand_total) as total'), DB::raw('DATE_FORMAT(orders.created_at, "%M") AS month'))
            ->leftJoin('users', 'orders.seller_id', '=', 'users.id')
            ->whereRaw('users.user_type = "admin"')
            ->whereYear('orders.created_at', '=', Date("Y"))
            // ->orWhereRaw('users.user_type = "seller"')
            // ->groupBy('users.user_type')
            ->groupBy('month')
            ->orderBy(DB::raw('MONTH(orders.created_at)'), 'asc')
            ->get();
        $new_stat = array();
        foreach ($sales_stat as $row) {
            $new_stat[$row->month][] = $row;
        }
        $data['sales_stat'] = $new_stat;
        // dd($sales_stat);
        // "SELECT users.user_type, SUM(grand_total) FROM `orders` LEFT JOIN users ON orders.seller_id = users.id WHERE users.user_type = 'admin' OR users.user_type = 'seller' GROUP BY users.user_type, MONTH(orders.created_at)";
        $data['total_sellers'] = User::where('user_type', 'seller')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->count();

        $data['status_wise_sellers'] = Shop::select('verification_status', DB::raw('COUNT(*) as total'))
            ->whereIn('user_id', function ($q) {
                $q->select('id')
                    ->from(with(new User)->getTable())
                    ->where('user_type', 'seller')
                    ->where(function ($query) {
                        $query->whereNotNull('email_verified_at')
                            ->orWhereNotNull('phone_verified_at');
                    });
            })
            ->groupBy('verification_status')
            ->get();
        $data['top_sellers'] = Order::select('orders.seller_id', 'users.name', 'users.user_type', 'users.avatar_original', DB::raw('SUM(grand_total) as total'))
            ->leftJoin('users', 'orders.seller_id', '=', 'users.id')
            ->whereRaw('users.user_type = "seller"')
            ->groupBy('users.id')
            ->orderBy('total', 'desc')
            ->limit(6)
            ->get();
        $data['total_order'] = Order::count();
        $data['total_placed_order'] = Order::where('delivery_status', '!=', 'cancelled')->count();
        $data['total_pending_order'] = Order::where('delivery_status', 'pending')->count();
        $data['total_confirmed_order'] = Order::where('delivery_status', 'confirmed')->count();
        $data['total_picked_up_order'] = Order::where('delivery_status', 'picked_up')->count();
        $data['total_shipped_order'] = Order::where('delivery_status', 'on_the_way')->count();
        $admin_id = User::select('id')->where('user_type', 'admin')->first()->id;
        $data['total_inhouse_sale'] = Order::where("seller_id", $admin_id)->sum('grand_total');
        $data['payment_type_wise_inhouse_sale'] = Order::select(DB::raw('case
                                                    when payment_type in ("wallet") then "wallet"
                                                    when payment_type NOT in ("cash_on_delivery") then "others"
                                                    else cast(payment_type as char)
                                                    end as payment_type, SUM(grand_total)  as total_amount'),)
            ->where("user_id", '!=', null)
            ->where("seller_id", $admin_id)
            ->groupBy(DB::raw('1'))
            ->get();
        $data['inhouse_product_rating'] = Product::where('added_by', 'admin')->where('rating', '!=', 0)->avg('rating');
        $data['total_inhouse_order'] = Order::where("seller_id", $admin_id)->count();

        // dd($data['payment_type_wise_inhouse_sale']);
        return view('backend.dashboard', $data);
    }

    public function top_category_products_section(Request $request)
    {
        $top_categories_products = DB::table(DB::raw('(SELECT products.id product_id, products.name product_name, products.slug product_slug, products.auction_product, products.category_id,
                                                        `products`.`thumbnail_img` as `product_thumbnail_img`, od.sales, od.total, od.created_at order_detail_created,
                                                        categories.name AS category_name,
                                                        `categories`.`cover_image`,
                                                        ROW_NUMBER() OVER (PARTITION BY products.category_id ORDER BY od.sales DESC) rn
                                                from products
                                                INNER JOIN (
                                                SELECT product_id, SUM(quantity) sales, SUM(price + tax) AS total, created_at
                                                FROM order_details
                                                WHERE ' . ($request->interval_type == 'all' ?: 'created_at >= DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')') . '
                                                AND order_details.delivery_status = "delivered"
                                                GROUP BY product_id
                                                )  od ON od.product_id = products.id
                                                LEFT JOIN categories ON products.category_id = categories.id
                                                ) t'))
            ->select(DB::raw('category_id, category_name, cover_image, product_id, product_name, product_slug, auction_product, product_thumbnail_img, sales, total, order_detail_created'))
            ->where('rn', '<=', 3)
            ->orderBy('sales', 'desc')
            ->get();

        $category_array = [];
        $new_array = array();
        foreach ($top_categories_products as $key => $row) {
            $row->product_thumbnail_img = Upload::where('id', $row->product_thumbnail_img)->first();
            $category_array[] = $row->category_id;
            $new_array[$row->category_id][] = $row;
        }
        $top_categories2 = array_unique($category_array);
        $top_categories_products = $new_array;

        return view('backend.dashboard.top_category_products_section', compact('top_categories2', 'top_categories_products'))->render();
    }

    public function inhouse_top_categories(Request $request)
    {
        $inhouse_top_category_query = Order::query();
        $inhouse_top_category_query->select('categories.id', 'categories.name', 'categories.cover_image', DB::raw('SUM(order_details.price + order_details.tax) as total'))
            ->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.delivery_status', '=', 'delivered')
            ->whereRaw('products.added_by = "admin"');
        if ($request->interval_type != 'all') {
            $inhouse_top_category_query->where('orders.created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')'));
        }
        $inhouse_top_categories = $inhouse_top_category_query->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('backend.dashboard.inhouse_top_categories', compact('inhouse_top_categories'))->render();
    }

    public function inhouse_top_brands(Request $request)
    {
        $inhouse_top_brand_query = Order::query();
        $inhouse_top_brand_query->select('brands.id', 'brands.name', 'brands.logo', DB::raw('SUM(order_details.price + order_details.tax) as total'))
            ->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('orders.delivery_status', '=', 'delivered')
            ->where('products.brand_id', '!=', null)
            ->whereRaw('products.added_by = "admin"');
        if ($request->interval_type != 'all') {
            $inhouse_top_brand_query->where('orders.created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')'));
        }
        $inhouse_top_brands = $inhouse_top_brand_query->groupBy('brands.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('backend.dashboard.inhouse_top_brands', compact('inhouse_top_brands'))->render();
    }

    public function top_sellers_products_section(Request $request)
    {
        // $top_sellers_products = DB::table(DB::raw('(SELECT products.id product_id, products.name product_name, products.user_id,
        //                                                 `products`.`thumbnail_img` as `product_thumbnail_img`, od.sales, od.total, shops.name AS shop_name,
        //                                                 `shops`.`logo`,
        //                                                 ROW_NUMBER() OVER (PARTITION BY products.user_id ORDER BY od.sales DESC) rn
        //                                     from products
        //                                     INNER JOIN (
        //                                         SELECT product_id, SUM(quantity) sales, SUM(price + tax) AS total, created_at
        //                                         FROM order_details
        //                                         WHERE ' . ($request->interval_type == 'all' ?: 'created_at >= DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')') . '
        //                                         AND order_details.delivery_status = "delivered"
        //                                         GROUP BY product_id
        //                                     )  od ON od.product_id = products.id
        //                                     LEFT JOIN shops ON products.user_id = shops.user_id
        //                                 ) t'))
        //     ->select(DB::raw('user_id, shop_name, logo, product_id, product_name, product_thumbnail_img, sales, total'))
        //     ->where('rn', '<=', 3)
        //     ->where('shop_name', '!=', null)
        //     ->orderBy('total', 'desc')
        //     ->get();

        $new_top_sellers_query = Order::query();
        $new_top_sellers_query = Order::select('shops.user_id AS shop_id', 'shops.name AS shop_name', 'shops.logo', DB::raw('SUM(grand_total) AS sale'))
            ->join('shops', 'orders.seller_id', '=', 'shops.user_id')
            ->whereIn("seller_id", function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('user_type', 'seller');
            })
            ->where('orders.delivery_status', 'delivered')
            ->groupBy('orders.seller_id')
            ->orderBy('sale', 'desc');
        if ($request->interval_type != 'all') {
            $new_top_sellers_query->where('orders.created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')'));
        }

        $new_top_sellers = $new_top_sellers_query->get();

        foreach ($new_top_sellers as $key => $row) {
            $products_query = Product::query();
            $products_query->select('products.id AS product_id', 'products.name', 'products.slug AS product_slug', 'products.auction_product', 'products.thumbnail_img', DB::raw('SUM(quantity) AS total_quantity, SUM(price * quantity) AS sale'))
                ->join('order_details', 'order_details.product_id', '=', 'products.id')
                ->where("seller_id", $row->shop_id)
                ->where('order_details.delivery_status', 'delivered')
                ->where('products.approved', 1)
                ->where('products.published', 1);
            if ($request->interval_type != 'all') {
                $products_query->where('order_details.created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')'));
            }
            $products_query->groupBy('product_id')
                ->orderBy('sale', 'desc')
                ->limit(3);
            $row->products = $products_query->get();
            // $row->product_thumbnail_img = Upload::where('id', $row->product_thumbnail_img)->first();
            // $seller_array[] = $row->user_id;
            // $new_top_sellers_products_array[$row->user_id][] = $row;
            // echo '<pre>';print_r($new_top_sellers);
        }
        // dd($new_top_sellers);
        // $top_sellers2 = array_unique($seller_array);
        // $top_sellers_products = $new_top_sellers_products_array;

        // return view('backend.dashboard.top_sellers_products_section', compact('top_sellers2', 'top_sellers_products'))->render();
        return view('backend.dashboard.top_sellers_products_section', compact('new_top_sellers'))->render();
    }

    public function top_brands_products_section(Request $request)
    {
        $top_brands_products = DB::table(DB::raw('(SELECT products.id product_id, products.name product_name, products.slug product_slug, products.auction_product, products.brand_id,
                                                        `products`.`thumbnail_img` as `product_thumbnail_img`, od.sales, od.total, brands.name AS brand_name,
                                                        `brands`.`logo`,
                                                        ROW_NUMBER() OVER (PARTITION BY products.brand_id ORDER BY od.sales DESC) rn
                                            from products
                                            INNER JOIN (
                                                SELECT product_id, SUM(quantity) sales, SUM(price + tax) AS total, created_at
                                                FROM order_details
                                                WHERE ' . ($request->interval_type == 'all' ?: 'created_at >= DATE_SUB(NOW(), INTERVAL 1 ' . $request->interval_type . ')') . '
                                                AND order_details.delivery_status = "delivered"
                                                GROUP BY product_id
                                            )  od ON od.product_id = products.id
                                            LEFT JOIN brands ON products.brand_id = brands.id
                                        ) t'))
            ->select(DB::raw('brand_id, brand_name, logo, product_id, product_name, product_slug, auction_product, product_thumbnail_img, sales, total'))
            ->where('rn', '<=', 3)
            ->orderBy('total', 'desc')
            ->where('brand_name', '!=', null)
            ->get();

        $brand_array = [];
        $new_array = [];
        foreach ($top_brands_products as $key => $row) {
            $row->product_thumbnail_img = Upload::where('id', $row->product_thumbnail_img)->first();
            $brand_array[] = $row->brand_id;
            $new_array[$row->brand_id][] = $row;
        }

        $top_brands2 = array_unique($brand_array);
        $top_brands_products = $new_array;

        return view('backend.dashboard.top_brands_products_section', compact('top_brands2', 'top_brands_products'))->render();
    }

    function clearCache(Request $request)
    {
        Artisan::call('optimize:clear');
        flash(translate('Cache cleared successfully'))->success();
        return back();
    }

    function optimize()
    {
        Artisan::call('optimize');
        //caching config results in env variables not being returned
        Artisan::call('config:clear');
        flash(translate('System optimized successfully'))->success();
        return back();
    }


    public function getCustomersData()
    {
        $data = $this->getCustomersStats();
        return view('backend.dashboard.customers_stats', compact('data'));
    }
    private function getCustomersStats()
    {
        $data = [];

        // Combined total, verified, and unverified customer stats in one efficient query
        $customerStats = User::selectRaw("
            COUNT(*) as total_customers,
            SUM(CASE WHEN email_verified_at IS NOT NULL OR phone_verified_at IS NOT NULL THEN 1 ELSE 0 END) as total_verified_customers,
            SUM(CASE WHEN email_verified_at IS NULL AND phone_verified_at IS NULL THEN 1 ELSE 0 END) as total_unverified_customers
        ")
            ->where('user_type', 'customer')
            ->first();

        $data['total_customers'] = $customerStats->total_customers == 0 ? 1 : $customerStats->total_customers; // Avoid division by zero
        $data['total_verified_customers'] = $customerStats->total_verified_customers;
        $data['total_verified_customers_rate'] = round($customerStats->total_verified_customers / $data['total_customers'] * 100, 2);
        $data['total_unverified_customers'] = $customerStats->total_unverified_customers;
        $data['total_unverified_customers_rate'] = round($customerStats->total_unverified_customers / $data['total_customers'] * 100, 2);

        $orderUserIds = DB::table('orders')
            ->where('delivery_status', '!=', 'cancelled')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();


        $data['customers_with_orders'] = User::whereIn('id', $orderUserIds)
            ->where('user_type', 'customer')
            ->count();
        $data['customers_with_orders_rate'] = round($data['customers_with_orders'] / $data['total_customers'] * 100, 2);


        $data['customer_signup_data'] = $this->getCustomerSignupData();

        $data['top_customers'] = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.avatar_original',
                DB::raw('COALESCE(orders.total, 0) as total')
            )
            ->leftJoin(DB::raw("(
                SELECT user_id, SUM(grand_total) as total
                FROM orders
                WHERE payment_status = 'paid'
                GROUP BY user_id
            ) as orders"), 'orders.user_id', '=', 'users.id')
            ->where('users.user_type', 'customer')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $data['top_cities_customers'] = $this->getTopCitiesByCustomers();
        $data['top_cities_orders'] = $this->getTopCitiesByOrders();
        $data['customer_retention'] = $this->getCustomerRetentionData();
        $data['total_credit_balance'] = DB::table('users')
            ->where('user_type', 'customer')
            ->where('balance', '>', 0)
            ->sum('balance');
        return $data;
    }

    private function getCustomerSignupData($period = 'daily')
    {
        $query = User::query()
            ->where('user_type', 'customer')
            ->where(function ($q) {
                $q->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            });

        // Dynamic period setup
        $groupColumn = '';
        $dateLimit = now();

        switch ($period) {
            case 'daily':
                $dateLimit = now()->subDays(30);
                $groupColumn = 'DATE(created_at)';
                break;
            case 'weekly':
                $dateLimit = now()->subWeeks(12);
                $groupColumn = 'YEARWEEK(created_at)';
                break;
            case 'yearly':
                $dateLimit = now()->subYears(5);
                $groupColumn = 'YEAR(created_at)';
                break;
            default: // monthly
                $dateLimit = now()->subMonths(12);
                $groupColumn = 'DATE_FORMAT(created_at, "%Y-%m")';
                break;
        }

        $query->select(
            DB::raw("$groupColumn as period"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $dateLimit)
            ->groupBy('period')
            ->orderBy('period');

        $results = $query->get();

        // Set locale for labels
        Carbon::setLocale(app()->getLocale() === 'ye' ? 'ar' : app()->getLocale());

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = match ($period) {
                'daily' => Carbon::parse($result->period)->isoFormat('MMM D'),
                'weekly' => Carbon::parse($result->period)->startOfWeek()->isoFormat('MMM D'),
                'monthly' => Carbon::createFromFormat('Y-m', $result->period)->isoFormat('MMM YYYY'),
                'yearly' => $result->period,
                default => Carbon::createFromFormat('Y-m', $result->period)->isoFormat('MMM YYYY'),
            };

            $data[] = $result->count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'period_label' => match ($period) {
                'daily' => translate('Last 30 Days'),
                'weekly' => translate('Last 12 Weeks'),
                'monthly' => translate('Last 12 Months'),
                'yearly' => translate('Last 5 Years'),
            }
        ];
    }

    public function getCustomerGrowthData(Request $request)
    {
        $period = $request->input('period', 'daily');
        return response()->json($this->getCustomerSignupData($period));
    }

    private function getTopCitiesByCustomers()
{
    return DB::table('addresses')
        ->join('users', 'users.id', '=', 'addresses.user_id')
        ->join('states', 'states.id', '=', 'addresses.state_id')
        ->where('users.user_type', 'customer')
        ->select(
            'states.name as city',
            DB::raw('COUNT(DISTINCT users.id) as total')
        )
        ->whereNotNull('addresses.state_id')
        ->groupBy('states.id', 'states.name')
        ->orderBy('total', 'desc')
        ->limit(10)
        ->get();
}

    private function getTopCitiesByOrders()
    {

        $allOrders = DB::query()
            ->fromSub(function ($query) {
                $query->from('orders')
                    ->select(
                        DB::raw('JSON_UNQUOTE(JSON_EXTRACT(shipping_address, "$.state")) as state'),
                        DB::raw('COUNT(*) as total_orders')
                    )
                    ->whereNotNull('shipping_address')
                    ->where('delivery_status', '!=', 'cancelled')
                    ->whereRaw('JSON_EXTRACT(shipping_address, "$.state") IS NOT NULL')
                    ->groupBy(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(shipping_address, "$.state"))'));
            }, 'combined_orders')
            ->select('state as city', DB::raw('SUM(total_orders) as total'))
            ->groupBy('state')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return $allOrders;
    }

    private function getCustomerRetentionData()
{
    // Get all customer IDs with their order counts
    $customerOrders = DB::query()
        ->fromSub(function ($query) {

            $query->from('orders')
                ->select('user_id', DB::raw('COUNT(*) as order_count'))
                ->whereNotNull('user_id')
                ->where('delivery_status', '!=', 'cancelled')
                ->groupBy('user_id');
        }, 'combined_orders')
        ->select('user_id', DB::raw('SUM(order_count) as total_orders'))
        ->groupBy('user_id')
        ->get();

    // Calculate retention metrics
    $oneTime = 0;
    $repeat = 0;
    $loyal = 0; // More than 3 orders
    $vip = 0;   // More than 5 orders

    foreach ($customerOrders as $customer) {
        if ($customer->total_orders == 1) {
            $oneTime++;
        } elseif ($customer->total_orders <= 3) {
            $repeat++;
        } elseif ($customer->total_orders <= 5) {
            $loyal++;
        } else {
            $vip++;
        }
    }

    return [
        [
            'label' => translate('One-Time Customers'),
            'description' => translate('Customers who have made exactly 1 order'),
            'value' => $oneTime,
            'color' => '#dc3545'
        ],
        [
            'label' => translate('Repeat Customers'),
            'description' => translate('Customers who have made 2-3 orders'),
            'value' => $repeat,
            'color' => '#fd7e14'
        ],
        [
            'label' => translate('Loyal Customers'),
            'description' => translate('Customers who have made 4-5 orders'),
            'value' => $loyal,
            'color' => '#28a745'
        ],
        [
            'label' => translate('VIP Customers'),
            'description' => translate('Customers who have made more than 5 orders'),
            'value' => $vip,
            'color' => '#007bff'
        ]
    ];
}
}
