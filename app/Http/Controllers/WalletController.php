<?php

namespace App\Http\Controllers;

use App\Models\ManualPaymentMethod;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Auth;
use Illuminate\Support\Facades\DB;
use Session;

class WalletController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_offline_wallet_recharges'])->only(['offline_recharge_request', 'updateApproved']);
    }

    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return view('frontend.user.wallet.index', compact('wallets'));
    }

    public function recharge(Request $request)
    {
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'wallet_payment');
        $request->session()->put('payment_data', $data);

        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            return (new $decorator)->pay($request);
        }
    }

    public function wallet_payment_done($payment_data, $payment_details)
    {
        $user = Auth::user();
        $user->balance = $user->balance + $payment_data['amount'];
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $payment_data['amount'];
        $wallet->payment_method = $payment_data['payment_method'];
        $wallet->payment_details = $payment_details;
        $wallet->save();

        Session::forget('payment_data');
        Session::forget('payment_type');

        flash(translate('Recharge completed'))->success();
        return redirect()->route('wallet.index');
    }

    public function offline_recharge(Request $request)
    {
       try{

        if(!get_setting('recharge_wallet_active')){
            flash('Recharging credit is not available now')->error();
            return back();
        }
        $request->validate([
            'name' => 'required',
            'trx_id'    => 'required',
            'amount' => 'required',
            'manual_payment_id' => 'required'
        ]);
        $manual_payment_data = array();
        $manual_payment_method = ManualPaymentMethod::find($request->manual_payment_id);
        if(isset($manual_payment_method)){
            $manual_payment_data['method_name'] = $manual_payment_method->name;
        } else {
            $manual_payment_data['method_name'] = '';
        }
        $manual_payment_data['name'] = $request->name;
        $manual_payment_data['trx_id'] = $request->trx_id;

        $wallet = new Wallet;
        $wallet->user_id = Auth::user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $manual_payment_data;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo;
        $wallet->save();
        flash(translate('Offline Recharge has been done. Please wait for response.'))->success();
       } catch(\Exception $e){
        flash($e->getMessage())->error();
       }
        return redirect()->route('wallet.index');
    }

    public function offline_recharge_request(Request $request)
    {
        $wallets = Wallet::where('offline_payment', 1);
        $type = null;
        if ($request->type != null) {
            $wallets = $wallets->where('approval', $request->type);
            $type = $request->type;
        }
        $wallets = $wallets->orderBy('id','desc')->paginate(10);
        return view('manual_payment_methods.wallet_request', compact('wallets', 'type'));
    }

    public function updateApproved(Request $request)
    {
        try{
        $request->validate([
            'id' => 'required',
            'status' => 'required|boolean',
        ]);
        $wallet = Wallet::findOrFail($request->id);
        $wallet->approval = $request->status;
        $user = $wallet->user;
        DB::beginTransaction();
        if ($request->status == 1) {
            $user->balance = $user->balance + $wallet->amount;
        } else {
            $user->balance = $user->balance - $wallet->amount;
        }
        $user->save();
        $wallet->save();
        DB::commit();
        return 1;


        } catch(\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
