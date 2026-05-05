<?php


namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use App\Models\ManualPaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentTypesController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    public function getList(Request $request)
    {
        $mode = "order";

        if ($request->has('mode')) {
            $mode = $request->mode; // wallet or other things , comes from query param ?mode=wallet
        }

        $list = "both";
        if ($request->has('list')) {
            $list = $request->list; // ?list=offline
        }

        $payment_types = array();
        // For testing porpuses
       if(get_setting('test_payment')){
            $payment_types[] = [
                'payment_type' => 'test',
                'payment_type_key' => 'test',
                'image' => static_asset('assets/img/cards/wallet.png'),
                'name' => "Wallet",
                'title' => translate("test"),
                'offline_payment_id' => 0,
                'details' => "For testing propuses"
            ];
        }

        if ($list == "online" || $list == "both") {
            $all_online_payment_methods = get_activate_payment_methods();
            if (count($all_online_payment_methods) > 0) {

                $online_payment_methods = $all_online_payment_methods->toQuery()->get();

                foreach ($online_payment_methods as $online_payment_method){
                    if ($online_payment_method->active == 1) {
                        $payment_type = array();
                        $payment_type['payment_type'] = $online_payment_method->name;
                        $payment_type['payment_type_key'] = $online_payment_method->name;
                        $payment_type['image'] = static_asset('assets/img/cards/'.$online_payment_method->name.'.png');
                        $payment_type['name'] = ucfirst($online_payment_method->name);
                        $payment_type['title'] = translate("Pay with ".$online_payment_method->name);
                        $payment_type['offline_payment_id'] = 0;
                        $payment_type['details'] = "";
                        if ($mode == 'wallet') {
                            $payment_type['title'] = translate("Pay with ".$online_payment_method->name);
                        }

                        $payment_types[] = $payment_type;
                    }
                }
            }
        }

        // you cannot recharge wallet by wallet or cash payment
        if ($mode != 'wallet' && $mode != 'seller_package' && $list != "offline" && $mode !="repayment") {
            if (get_setting('wallet_system') == 1) {
                $user = User::where('id',auth('api')->user()->id)->first();
                $credit = single_price($user->balance);
                $payment_type = array();
                $payment_type['payment_type'] = 'wallet_system';
                $payment_type['payment_type_key'] = 'wallet';
                $payment_type['image'] = static_asset('assets/img/cards/wallet.png');
                $payment_type['name'] = "Wallet";
                $payment_type['title'] = translate("Wallet Payment");
                $payment_type['offline_payment_id'] = 0;
                $payment_type['details'] = translate('Your credit:')." ".$credit;

                $payment_types[] = $payment_type;
            }

            $haveDigitalProduct = false;
            $cash_on_delivery = false;

            if ($mode == "order") {
                $user   = auth('api')->user();
                $carts = ($user != null) ?
                        Cart::where('user_id', $user->id)->active()->get() :
                        ($request->has('temp_user_id') ? Cart::where('temp_user_id', $request->temp_user_id)->active()->get() : [] );

                foreach ($carts as $key => $cart) {
                    $haveDigitalProduct =  $cart->product->digital == 1;
                    $cash_on_delivery =  $cart->product->cash_on_delivery == 0;
                    if ($haveDigitalProduct || $cash_on_delivery) {
                        break;
                    }
                }
            }

            if (get_setting('cash_payment') == 1  && !$haveDigitalProduct && !$cash_on_delivery && $mode != "external" && $mode !="repayment") {
                $payment_type = array();
                $payment_type['payment_type'] = 'cash_payment';
                $payment_type['payment_type_key'] = 'cash_on_delivery';
                $payment_type['image'] = static_asset('assets/img/cards/cod.png');
                $payment_type['name'] = "Cash Payment";
                $payment_type['title'] = translate("Cash on delivery");
                $payment_type['offline_payment_id'] = 0;
                $payment_type['details'] = "";

                $payment_types[] = $payment_type;
            }
        }
        // offline payment like bank transfer
        if (($list == 'offline' || $list == "both")  && get_setting('manual_payment') == 1) {
            foreach (ManualPaymentMethod::all() as $method) {


                $payment_type = array();
                $payment_type['payment_type'] = 'manual_payment';
                $payment_type['payment_type_key'] = 'manual_payment_'.$method->id;
                $payment_type['image'] = uploaded_asset($method->photo);
                $payment_type['name'] = $method->name;
                $payment_type['title'] = $method->name;
                $payment_type['offline_payment_id'] = $method->id;
                $payment_type['details'] = $method->details;

                $payment_types[] = $payment_type;
            }
        }

        return response()->json($payment_types);
    }
}
