<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Requests\SellerRegistrationRequest;
use App\Http\Resources\V2\Seller\CarResource;
use App\Http\Resources\V2\Seller\ProductCollection;
use App\Http\Resources\V2\Seller\CommissionHistoryResource;
use App\Http\Resources\V2\Seller\SellerPaymentResource;
use App\Http\Resources\V2\ShopCollection;
use App\Http\Resources\V2\ShopDetailsCollection;
use App\Http\Resources\V2\Seller\ShopInfoResource;
use App\Models\BusinessSetting;
use App\Models\Car;
use App\Models\Category;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Utility\SearchUtility;
use Carbon\Carbon;
use DB;
use Hash;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shop_query = Shop::query();

        if ($request->name != null && $request->name != "") {
            $shop_query->where("name", 'like', "%{$request->name}%");
            SearchUtility::store($request->name);
        }
        return new ShopCollection($shop_query->whereIn('user_id', verified_sellers_id())->paginate(10));
    }



    public function update(Request $request)
{
    $shop = Shop::where('user_id', auth()->id())->firstOrFail();

    $allowedFields = [
        'name', 'address', 'phone', 'meta_title', 'meta_description',
        'logo', 'shipping_cost', 'delivery_pickup_longitude',
        'delivery_pickup_latitude', 'facebook', 'instagram', 'google',
        'twitter', 'youtube', 'cash_on_delivery_status', 'bank_payment_status',
        'bank_name', 'bank_acc_name', 'bank_acc_no', 'bank_routing_no', 'sliders'
    ];

    $data = $request->only($allowedFields);

    if ($request->filled('name')) {
        $data['slug'] = preg_replace('/\s+/', '-', $request->name) . '-' . $shop->id;
    }

    $shop->fill($data);

    if ($shop->save()) {
        $message = 'Shop info updated successfully';
        if ($request->hasAny(['bank_name', 'cash_on_delivery_status'])) {
            $message = 'Payment info updated successfully';
        }

        return $this->success(translate($message));
    }

    return $this->failed(translate('Shop info update failed'));
}


    public function sales_stat()
{

    $data = Order::where('seller_id', auth()->id())
        ->where('delivery_status', 'delivered')
        ->where('created_at', '>=', now()->startOfYear())
        ->select([
            DB::raw("SUM(grand_total) as total"),
            DB::raw("DATE_FORMAT(created_at, '%c') as month_num")
        ])
        ->groupBy('month_num')
        ->get()
        ->toArray();

    $sales_array = [];

    $locale = app()->getLocale();

    for ($i = 1; $i <= 12; $i++) {

        $monthName = now()->month($i)->locale($locale)->translatedFormat('M');


        $sales_array[$monthName]['date'] = $monthName;
        $sales_array[$monthName]['total'] = 0;

        if (!empty($data)) {

            $key = array_search($i, array_column($data, 'month_num'));
            if ($key !== false) {
                $sales_array[$monthName]['total'] = (float)$data[$key]['total'];
            }
        }
    }

    return response()->json($sales_array);
}

    public function category_wise_products()
    {
        $category_wise_product = [];
        $new_array = [];
        $parent_categories = Category::with(['products'])->where('parent_id', 0)->get();
        foreach ($parent_categories as $key => $category) {
            if (count($category->products->where('user_id', auth()->user()->id)) > 0) {
                $category_wise_product["id"] = $category->id;
                $category_wise_product['name'] = $category->getTranslation('name');
                $category_wise_product['banner'] = uploaded_asset($category->banner);
                $category_wise_product['cnt_product'] = count($category->products->where('user_id', auth()->user()->id));

                $new_array[] = $category_wise_product;
            }
        }

        return Response()->json($new_array);
    }

    public function top_12_products()
    {
        $products = filter_products(Product::where('user_id',  auth()->user()->id)
            ->orderBy('num_of_sale', 'desc'))
            ->limit(12)
            ->get();

        return new ProductCollection($products);
    }

    public function top_cars()
{
    $cars = Car::where('user_id', auth()->user()->id)
        ->where(function ($query) {
            $query->whereHas('reservations', function ($q) {
                $q->where('status', 'completed');
            })
            ->orWhereHas('inspections', function ($q) {
                $q->where('status', 'completed');
            });
        })
        ->limit(12)
        ->get();

    return response()->json([
        'success' => true,
        'data'    => CarResource::collection($cars),
    ]);
}

    public function info()
{
    $shop = Shop::where('user_id', auth()->user()->id)
        ->withCount([
            // Counts cars for the shop's user that have specific reservations
            'user as car_reservations_count' => function ($query) {
                $query->whereHas('cars', function ($q) {
                    $q->whereHas('reservations', function ($sq) {
                        $sq->whereIn('status', ['completed', 'confirmed','pending']);
                    });
                });
            },
            // Counts cars for the shop's user that have specific inspections
            'user as car_inspections_count' => function ($query) {
                $query->whereHas('cars', function ($q) {
                    $q->whereHas('inspections', function ($sq) {
                        $sq->whereIn('status', ['completed', 'in_progress','pending']);
                    });
                });
            }
        ])
        ->with([
            'products',
            'user',
            'user.cars',
            'orders'
        ])
        ->first();

    return new ShopInfoResource($shop);
}

    public function pacakge()
    {
        $shop = auth()->user()->shop;

        return response()->json([
            'result' => true,
            'id' => $shop->id,
            'package_name' => $shop->seller_package->name,
            'package_img' => uploaded_asset($shop->seller_package->logo)

        ]);
    }

    public function profile()
    {
        $user = auth()->user();


        return response()->json([
            'result' => true,
            'id' => $user->id,
            'type' => $user->user_type,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_original' => uploaded_asset($user->avatar_original),
            'phone' => $user->phone

        ]);
    }

    public function payment_histories(Request $request)
    {
        $payments = Payout::query()->where('seller_id', auth()->user()->id);
        if($request->filled('search')){
            $payments->where('txn_code', 'like', "%{$request->search}%");
        }

        if($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::parse($request->from_date)->startOfDay();
            $to_date = Carbon::parse($request->to_date)->endOfDay();

            $payments = Payout::whereBetween('created_at', [$from_date, $to_date]);
        }
        $payments = $payments->paginate(10);
        return SellerPaymentResource::collection($payments);
    }

    public function collection_histories()
    {
        $user = auth('api')->user();
        $commission_history = $user->shop->commissions()->orderBy('created_at', 'desc')->paginate(10);
        return CommissionHistoryResource::collection($commission_history);
    }

    public function store(SellerRegistrationRequest $request)
    {
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_type = "seller";
        $user->password = Hash::make($request->password);
        $user->verification_code = rand(100000, 999999);

        if ($user->save()) {
            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->name = $request->shop_name;
            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', str_replace("/", " ", $request->shop_name));
            $shop->save();

            if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
            } else {

                try {
                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                    $shop->delete();
                    $user->delete();
                    return $this->failed(translate('Something Went Wrong!'));
                }
            }
            $authController = new AuthController();
            return $authController->loginSuccess($user);
        }

        return $this->failed(translate('Something Went Wrong!'));
    }


    public function getVerifyForm()
    {
        $forms = BusinessSetting::where('type', 'verification_form')->first();
        return response()->json(json_decode($forms->value));
    }

    public function store_verify_info(Request $request)
    {
        $data = array();
        $i = 0;
        foreach (json_decode(BusinessSetting::where('type', 'verification_form')->first()->value) as $key => $element) {
            $item = array();
            if ($element->type == 'text') {
                $item['type'] = 'text';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i];
            } elseif ($element->type == 'select' || $element->type == 'radio') {
                $item['type'] = 'select';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i];
            } elseif ($element->type == 'multi_select') {
                $item['type'] = 'multi_select';
                $item['label'] = $element->label;
                $item['value'] = json_encode($request['element_' . $i]);
            } elseif ($element->type == 'file') {
                $item['type'] = 'file';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i]->store('verification_form', 'public_uploads');
            }
            array_push($data, $item);
            $i++;
        }

        $shop = auth()->user()->shop;
        $shop->verification_info = json_encode($data);
        if ($shop->save()) {
            return $this->success(translate('Your shop verification request has been submitted successfully!'));
        }

        return $this->failed(translate('Something Went Wrong!'));
    }
}
