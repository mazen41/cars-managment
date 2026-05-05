<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\PaymentStatusEnum;
use App\Http\Resources\V2\WalletCollection;
use App\Models\CombinedOrder;
use App\Models\ManualPaymentMethod;
use App\Models\User;
use App\Models\Wallet;
use App\Utility\NotificationUtility;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:sanctum');

    }

    public function balance()
    {
        $lang = request()->header('App-Language');
        if($lang  == 'ye'){
            Carbon::setLocale('ar');
        }
        $user = User::find(auth('api')->user()->id);
        $latest = Wallet::where('user_id', auth('api')->user()->id)->where('amount', '>', 0)->latest()->first();
        return response()->json([
            'balance' => single_price($user->balance),
            'last_recharged' => $latest == null ? translate("Not Available") : $latest->created_at->diffForHumans(),
        ]);
    }

    public function walletRechargeHistory()
    {
        return new WalletCollection(Wallet::where('user_id', auth('api')->user()->id)->latest()->paginate(10));
    }

    /**
     *
     * Deprecated, use the online payment with provider "wallet" instead
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {

        try{
            $request->validate([
                'type'  => 'required',
                'order_id'  => 'required_if:type,order|numeric',
                'inspection_id'  => 'required_if:type,car_inspection|numeric',
                'reservation_id'  => 'required_if:type,car_reservation|numeric',
            ]);


        $user = User::find(auth('api')->user()->id);
        DB::beginTransaction();
            switch ($request->type) {
                case 'order':
                    $this->handleOrderPayment($request->order_id, $user);
                    break;
                case 'car_inspection':
                    $this->handleCarInspectionPayment($request->inspection_id, $user);
                    break;
                case 'car_reservation':
                    $this->handleCarReservationPayment($request->reservation_id, $user);
                    break;
                default:
                    return response()->json([
                        'result' => false,
                        'message' => translate('Invalid payment type')
                    ]);
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => translate('Payment submitted.')
            ]);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handleOrderPayment($combined_order_id, $user){
        $combined_order = CombinedOrder::find($combined_order_id);
        if(!isset($combined_order)){
           throw new \Exception('Invalid order');
        }
        if($combined_order->payment_status == 'paid'){
            throw new \Exception('Order is already paid');
        }
        if($combined_order->user_id != $user->id){
            throw new \Exception('You are not authorized to pay for this order');
        }
        if ($user->balance < $combined_order->grand_total) {
            throw new \Exception('Insufficient wallet balance');
        }

        $combined_order->payment_status = PaymentStatusEnum::PAID;
        $combined_order->save();

         $payment_details = json_encode([
            'type'  => 'order',
            'type_id'   => $combined_order->id,
        ]);
        $user->decrementBalance($combined_order->grand_total, $payment_details);


        foreach ($combined_order->orders as $key => $order) {
                calculateCommissionAffilationClubPoint($order);
            }
            NotificationUtility::sendOrderPlacedNotification($order);
    }

    private function handleCarInspectionPayment($inspection_id, $user){
        $inspection = \App\Models\CarInspection::find($inspection_id);
        if(!isset($inspection)){
           throw new \Exception('Invalid inspection');
        }
        if($inspection->payment && $inspection->payment->status == 'paid'){
            throw new \Exception('Inspection is already paid');
        }
        if($inspection->requested_by != $user->id){
            throw new \Exception('You are not authorized to pay for this inspection');
        }
        $inspection_price = $inspection->inspectionType->price;
        if ($user->balance < $inspection_price) {
            throw new \Exception('Insufficient wallet balance');
        }

        $payment_details = json_encode([
            'type'  => 'car_inspection',
            'type_id'   => $inspection->id,
        ]);
        $walletRecordId = $user->decrementBalance($inspection_price, $payment_details);

        $inspection->payment()->updateOrCreate([],[
            'method'    => 'wallet',
            'is_manual_payment' => false,
            'status'    => PaymentStatusEnum::PAID,
            'amount'   => $inspection_price,
            'transaction_id'   => 'W-'.$walletRecordId,
            'paid_at'   => Carbon::now(),
            'details'   => null,
           ]);

        // Trigger the event to handle commission & notifications
        event(new \App\Events\CarInspectionPaid($inspection));
    }

    private function handleCarReservationPayment($reservation_id, $user){
        $reservation = \App\Models\CarReservation::find($reservation_id);
        if(!isset($reservation)){
           throw new \Exception('Invalid reservation');
        }
        if($reservation->payment && $reservation->payment->status == 'paid'){
            throw new \Exception('Reservation is already paid');
        }
        if($reservation->user_id != $user->id){
            throw new \Exception('You are not authorized to pay for this reservation');
        }
        $reservation_price = get_setting('car_reservation_amount', 0);
        if ($user->balance < $reservation_price) {
            throw new \Exception('Insufficient wallet balance');
        }

         $payment_details = json_encode([
            'type'  => 'car_reservation',
            'type_id'   => $reservation->id,
        ]);
        $walletRecordId = $user->decrementBalance($reservation_price, $payment_details);
        $reservation->payment()->updateOrCreate([],[
            'method'    => 'wallet',
            'is_manual_payment' => false,
            'status'    => PaymentStatusEnum::PAID,
            'amount'   => $reservation_price,
            'transaction_id'   => 'W-'.$walletRecordId,
            'paid_at'   => Carbon::now(),
            'details'   => null,
           ]);

        // Trigger the event to handle commission & notifications
        event(new \App\Events\CarReservationPaid($reservation));
    }


    public function offline_recharge(Request $request)
    {
        if(!get_setting('recharge_wallet_active')){
            return response()->json([
                'result' => false,
                'message' => translate('Recharging credit is not available now')
            ]);
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
        $wallet->user_id = auth('api')->user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = 'manual_payment';
        $wallet->payment_details = $manual_payment_data;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo != 0 ? $request->photo : null;
        $wallet->save();
        return response()->json([
            'result' => true,
            'message' => translate('Offline Recharge has been done. Please wait for response.')
        ]);
    }

}
