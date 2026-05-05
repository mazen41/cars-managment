<?php

namespace App\Http\Controllers;

use App\Traits\HandlesExports;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use Auth;
use Carbon\Carbon;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    use HandlesExports;
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_customers'])->only(['index', 'customers_balance', 'getDetails', 'show']);
        $this->middleware(['permission:login_as_customer'])->only('login');
        $this->middleware(['permission:ban_customer'])->only('ban');
        $this->middleware(['permission:delete_customer'])->only('destroy');
        $this->middleware(['permission:verify_customer'])->only('verifyPhone', 'unverifyPhone', 'verifyEmail', 'unverifyEmail');
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function index(Request $request)
    {
        $paginate = $request->paginate ?? 15;
        $users = User::where('user_type', 'customer')
        ->applySort($request->sort)->with('wallets');

        $users = self::filter($users, $request);
        $users = $users->paginate($paginate);
        return view('backend.customer.customers.index', compact('users'));
    }

   protected static function filter($query, $request)
    {
        if ($request->has('email_verification')) {
            if ($request->email_verification === 'verified') {
                $query->whereNotNull('email')
                    ->whereNotNull('email_verified_at');
            } elseif ($request->email_verification === 'unverified') {
                $query->whereNotNull('email')
                    ->whereNull('email_verified_at');
            }
        }

        if ($request->has('phone_verification')) {
            if ($request->phone_verification === 'verified') {
                $query->whereNotNull('phone')
                    ->whereNotNull('phone_verified_at');
            } elseif ($request->phone_verification === 'unverified') {
                $query->whereNotNull('phone')
                    ->whereNull('phone_verified_at');
            }
        }
        if ($request->with_credit) {
            $query->where('balance', '>', 0);
        }

        if ($request->has('search')) {
            $sort_search = $request->search;
            $query->where(function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%')->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }

        if($request->has('deletion_request')){
            $query->where('deletion_request', true);
        }
        if($request->has('banned')){
            $query->where('banned', true);
        }

        return $query;

    }
    public function show($id)
    {
        $user = User::with([
            'orders',
            'wallets',
            'addresses',
            'wishlists',
            'reviews',
            'refund_requests'
        ])->findOrFail($id);

        if ($user->user_type != 'customer') {
            flash(translate('This user is not a customer'))->error();
            return redirect()->route('customers.index');
        }

        $orders = $user->orders()->latest()->take(10)->get();
        $wallet_transactions = $user->wallets()->latest()->take(10)->get();

        return view('backend.customer.customers.details', compact('user', 'orders', 'wallet_transactions'));
    }

    public function verifyPhone(Request $request)
    {

        $user = User::findOrFail($request->user_id);

        if (!$user->phone) {
            return response()->json([
                'success' => false,
                'message' => translate('This user does not have a phone number')
            ]);
        }

        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => translate('Phone number verified successfully')
        ]);
    }

    public function unverifyPhone(Request $request)
    {

        $user = User::findOrFail($request->user_id);

        if (!$user->phone) {
            return response()->json([
                'success' => false,
                'message' => translate('This user does not have a phone number')
            ]);
        }

        $user->phone_verified_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => translate('Phone number verification removed')
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if (!$user->email) {
            return response()->json([
                'success' => false,
                'message' => translate('This user does not have an email address')
            ]);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => translate('Email verified successfully')
        ]);
    }

    public function unverifyEmail(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if (!$user->email) {
            return response()->json([
                'success' => false,
                'message' => translate('This user does not have an email address')
            ]);
        }

        $user->email_verified_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => translate('Email verification removed')
        ]);
    }

    public function customers_balance(Request $request)
    {
        $paginate = 15;
        $sort_search = null;
        $users = User::where('user_type', 'customer')
            ->where('balance', '!=', 0)
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $users->where(function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%')->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }
        if ($request->paginate != null) {
            $paginate = $request->paginate;
        }
        $users = $users->paginate($paginate);

        return view('backend.customer.customers.balance', compact('users', 'sort_search', 'paginate'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required|unique:users|email',
            'phone'         => 'required|unique:users',
        ]);

        $response['status'] = 'Error';

        $user = User::create($request->all());

        $customer = new Customer;

        $customer->user_id = $user->id;
        $customer->save();

        if (isset($user->id)) {
            $html = '';
            $html .= '<option value="">
                        ' . translate("Walk In Customer") . '
                    </option>';
            foreach (Customer::all() as $key => $customer) {
                if ($customer->user) {
                    $html .= '<option value="' . $customer->user->id . '" data-contact="' . $customer->user->email . '">
                                ' . $customer->user->name . '
                            </option>';
                }
            }

            $response['status'] = 'Success';
            $response['html'] = $html;
        }

        echo json_encode($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = User::findOrFail($id);

        //delete related non crucial data
        $customer->carts()->delete();
        $customer->addresses()->delete();
        $customer->wishlists()->delete();
        $customer->reviews()->delete();

        User::destroy($id);
        flash(translate('Customer has been deleted successfully'))->success();
        return redirect()->route('customers.index');
    }

    public function cancel_deletion_request($id){
        $user = User::findOrFail(decrypt($id));
        if (!$user->deletion_request) {
            flash(translate('No deletion request found for this account'))->error();
            return back();
        }
        $user->deletion_request = false;
        $user->deletion_requested_at = null;
        $user->save();

        flash(translate('Account deletion request cancelled successfully'))->success();
        return back();
    }

    public function bulk_customer_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $customer_id) {
                $customer = User::findOrFail($customer_id);
                $this->destroy($customer_id);
            }
        }

        return 1;
    }

    public function login($id)
    {
        $user = User::findOrFail(decrypt($id));

        \Auth::user()->impersonate($user);

        return redirect()->route('dashboard');
    }

    public function ban($id)
    {
        $user = User::findOrFail(decrypt($id));

        if ($user->banned == 1) {
            $user->banned = 0;
            flash(translate('Customer UnBanned Successfully'))->success();
        } else {
            $user->banned = 1;
            flash(translate('Customer Banned Successfully'))->success();
        }

        $user->save();

        return back();
    }

 public function customerBulkExport(Request $request)
    {
        return $this->handleBulkExport(
            $request,
            UsersExport::class,
            'customers_export'
        );
    }

    public function ajaxSearch(Request $request)
    {
        $search = $request->get('search');
        $with_phone = $request->input('with_phone', false);
        $with_phone_verified = $request->input('with_phone_verified', false);
        $with_email_verified = $request->input('with_email_verified', false);
        $with_email = $request->input('with_email', false);

        $users = User::select('id', 'name', 'email', 'phone')
        ->where('user_type', 'customer')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->when($with_phone, function ($query) {
                return $query->whereNotNull('phone');
            })
            ->when($with_phone_verified, function ($query) {
                return $query->whereNotNull('phone_verified_at');
            })
            ->when($with_email, function ($query) {
                return $query->whereNotNull('email');
            })
            ->when($with_email_verified, function ($query) {
                return $query->whereNotNull('email_verified_at');
            })
            ->paginate(20);

        return response()->json([
            'users' => $users->items(),
            'hasMore' => $users->hasMorePages()
        ]);
    }

    public function getDetails(Request $request)
{
    $user = User::findOrFail($request->id);

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'order_count' => $user->orders_count,
        'total_spent' => single_price($user->paid_amount),
        'balance' => single_price($user->balance),
        'avatar_original' => uploaded_asset($user->avatar_original),
        'created_at' => Carbon::parse($user->created_at)->format('d/m/Y'),
    ]);
}
}
