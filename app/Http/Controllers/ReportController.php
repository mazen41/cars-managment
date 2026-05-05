<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Traits\HandlesExports;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Commission;
use App\Models\RefundHistory;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Search;
use App\Models\Shop;
use Auth;
use App\Models\NotificationType;
use App\Exports\WalletsExport;
use App\Notifications\ExportCompletedNotification;
use Excel;

use function Aws\filter;

class ReportController extends Controller
{
    use HandlesExports;
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:in_house_product_sale_report'])->only('in_house_sale_report');
        $this->middleware(['permission:seller_products_sale_report'])->only('seller_sale_report');
        $this->middleware(['permission:products_stock_report'])->only('stock_report');
        $this->middleware(['permission:product_wishlist_report'])->only('wish_report');
        $this->middleware(['permission:user_search_report'])->only('user_search_report');
        $this->middleware(['permission:commission_history_report'])->only('commission_history');
        $this->middleware(['permission:wallet_transaction_report'])->only(['wallet_transaction_history', 'walletTransactionBulkExport']);
    }

    public function stock_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')&& !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.stock_report', compact('products', 'sort_by'));
    }

    public function in_house_sale_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('num_of_sale', 'desc')->where('added_by', 'admin');
        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.in_house_sale_report', compact('products', 'sort_by'));
    }

    public function seller_sale_report(Request $request)
    {
        $sort_by = null;
        // $sellers = User::where('user_type', 'seller')->orderBy('created_at', 'desc');
        $sellers = Shop::with('user')->orderBy('created_at', 'desc');
        if ($request->has('verification_status')) {
            $sort_by = $request->verification_status;
            $sellers = $sellers->where('verification_status', $sort_by);
        }
        $sellers = $sellers->paginate(10);
        return view('backend.reports.seller_sale_report', compact('sellers', 'sort_by'));
    }

    public function wish_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(10);
        return view('backend.reports.wish_report', compact('products', 'sort_by'));
    }

    public function user_search_report(Request $request)
    {
        $searches = Search::orderBy('count', 'desc')->paginate(10);
        return view('backend.reports.user_search_report', compact('searches'));
    }

   public function commission_history(Request $request)
{
    $user = Auth::user();

    $ownableId = $user->user_type === 'seller' ? $user->id : $request->ownable_id ?? null;

    $dateRange = $request->date_range;

    $types = [
        'order'            => \App\Models\Order::class,
        'car_inspection'   => \App\Models\CarInspection::class,
        'car_reservation'  => \App\Models\CarReservation::class,
        'auction_invoice'  => \App\Models\AuctionInvoice::class,
    ];

    $commissionableType = $types[$request->commissionable_type] ?? null;

    $baseQuery = Commission::query()
        ->when($ownableId, fn ($q) => $q->where('ownable_id', $ownableId))
        ->when($commissionableType, function($q) use ($commissionableType){
            $q->where('commissionable_type', $commissionableType);
        })
        ->when($dateRange, function ($q) use ($dateRange) {
            [$from, $to] = explode(' / ', $dateRange);
            $q->whereBetween('created_at', [$from, $to]);
        });

    $stats = [];



    foreach ($types as $key => $type) {
        $query = (clone $baseQuery)->where('commissionable_type', $type);

        $stats["{$key}_commissions"] = single_price(
            $query->sum('admin_commission')
        );

        $stats["{$key}_commissions_count"] = $query->count();
    }

    $commission_history = (clone $baseQuery)
        ->orderByDesc('created_at')
        ->paginate(10);

    $view = $user->user_type === 'seller'
        ? 'seller.reports.commission_history_report'
        : 'backend.reports.commission_history_report';

    return view($view, compact(
        'commission_history',
        'ownableId',
        'dateRange',
        'stats'
    ));
}


    public function wallet_transaction_history(Request $request)
    {

        $user_id = $request->user_id;
        $date_range = $date_range = $request->date_range;
        $paginate = $request->paginate ?? 10;

        $users_with_wallet = User::whereIn('id', function ($query) {
            $query->select('user_id')->from(with(new Wallet)->getTable());
        })->get();

        $wallet_history = Wallet::applySort($request->sort);

        $wallets = self::wallet_transaction_filter($wallet_history, $request);

        $wallets = $wallets->paginate(15);

        return view('backend.reports.wallet_history_report', compact('wallets', 'users_with_wallet', 'user_id', 'date_range'));
    }

    protected static function wallet_transaction_filter($query, $request){

        if ($request->date_range) {
            $date_range = explode(" / ", $request->date_range);
            $query = $query->where('created_at', '>=', $date_range[0]);
            $query = $query->where('created_at', '<=', $date_range[1]);
        }
        if($request->approved){
            $query = $query->approved();
        }
        if ($request->user_id) {
            $query = $query->where('user_id', '=', $request->user_id);
        }
        return $query;
    }
    public function walletTransactionBulkExport(Request $request)
    {
        return $this->handleBulkExport(
            $request,
            WalletsExport::class,
            'wallet_transactions_export'
        );
    }
    public function refund_history_report(Request $request)
{
    $query = RefundHistory::with('externalOrder')->latest();


    if ($request->has('status') && !empty($request->status)) {
        $query->where('status', $request->status);
    }


    if ($request->has('date_range') && !empty($request->date_range)) {
        $dates = explode(" - ", $request->date_range);
        $from = date('Y-m-d', strtotime($dates[0]));
        $to = date('Y-m-d', strtotime($dates[1]));
        $query->whereDate('created_at', '>=', $from)
              ->whereDate('created_at', '<=', $to);
    }

    $refunds = $query->paginate(15);

    return view('backend.reports.refund_history_report', compact('refunds'));
}

    public function export_sales_report(Request $request)
    {
        $request->merge(['select_all' => true]);
        $type = $request->type;

        return $this->handleBulkExport(
            request: $request,
            exportClass: SalesExport::class,
            baseFilename: 'sales_report_export',
            getIdsCallback: function ($request) use ($type) {
                if ($type == 'seller') {
                    // For seller report, get Seller IDs
                    $query = \App\Models\Seller::query();


                    return $query->pluck('id')->toArray();
                } else {
                    // For in-house report, get Product IDs
                    $query = Product::query();
                    $query->where('added_by', 'admin');
                    $query->orderBy('num_of_sale', 'desc');

                    if ($request->has('category_id') && !empty($request->category_id)) {
                        $query->where('category_id', $request->category_id);
                    }

                    return $query->pluck('id')->toArray();
                }
            },
            filters: $request->only(['type', 'category_id', 'verification_status'])
        );
    }

}
