<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use App\Models\SellerWithdrawRequest;
use App\Models\Payout;
use App\Models\Shop;
use App\Models\ShopWallet;
use App\Models\User;
use Session;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PayoutNotification;

class CommissionController extends Controller
{
    //redirect to payment controllers according to selected payment gateway for seller payment
    public function pay_to_seller(Request $request)
    {
        $data['shop_id'] = $request->shop_id;
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;
        $data['payment_withdraw'] = $request->payment_withdraw;
        $data['withdraw_request_id'] = $request->withdraw_request_id;

        if ($request->txn_code != null) {
            $data['txn_code'] = $request->txn_code;
        }
        else {
            flash(translate('Txn is required'))->danger();
            return redirect()->route('sellers.index');
        }

        $request->session()->put('payment_type', 'seller_payment');
        $request->session()->put('payment_data', $data);


        $shop = Shop::findOrFail($data['shop_id']);

        $payment_data = $request->session()->get('payment_data');
        if($payment_data['amount'] > $shop->admin_to_pay){
            flash(translate('Amount can not be more than what you owe the seller'))->error();
            return redirect()->route('sellers.index');
        }
        if ($request->payment_option == 'cash') {
            return $this->seller_payment_done($shop,$payment_data, null);
        }
        elseif ($request->payment_option == 'bank_payment') {
            $request->validate(['wallet_id']);
            $wallet = ShopWallet::find($request->wallet_id);
            $data['payment_details'] = $wallet->wallet_name.' '.'('.$wallet->account_number.')';
            return $this->seller_payment_done($shop, $payment_data, $data['payment_details']);
        }
        else {

            $shop->admin_to_pay = $shop->admin_to_pay + $payment_data['amount'];
            $shop->save();

            $payment = new Payout;
            $payment->seller_id = $shop->user->id;
            $payment->amount = $payment_data['amount'];
            $payment->payment_method = 'Seller paid to admin';
            $payment->txn_code = $payment_data['txn_code'];
            $payment->payment_details = null;
            $payment->save();

            flash(translate('Payment completed'))->success();
            return redirect()->route('sellers.index');
        }
    }

    //redirects to this method after successfull seller payment
    public function seller_payment_done($shop, $payment_data, $payment_details){
        $shop->admin_to_pay = $shop->admin_to_pay - $payment_data['amount'];
        $shop->save();

        $payment = new Payout;
        $payment->seller_id = $shop->user->id;
        $payment->amount = $payment_data['amount'];
        $payment->payment_method = $payment_data['payment_method'];
        $payment->txn_code = $payment_data['txn_code'];
        $payment->payment_details = $payment_details;
        $payment->save();

        if ($payment_data['payment_withdraw'] == 'withdraw_request') {
            $seller_withdraw_request = SellerWithdrawRequest::findOrFail($payment_data['withdraw_request_id']);
            $seller_withdraw_request->status = '1';
            $seller_withdraw_request->viewed = '1';
            $seller_withdraw_request->save();
        }

        // Seller Payout Notification to seller
        $users = User::findMany($shop->user->id);
        $data = array();
        $data['user'] = $shop->user;
        $data['amount'] = $payment_data['amount'];
        $data['status'] = 'paid';
        $data['notification_type_id'] = get_notification_type('seller_payout', 'type')->id;
        Notification::send($users, new PayoutNotification($data));

        Session::forget('payment_data');
        Session::forget('payment_type');

        if ($payment_data['payment_withdraw'] == 'withdraw_request') {
            flash(translate('Payment completed'))->success();
            return redirect()->route('withdraw_requests_all');
        }
        else {
            flash(translate('Payment completed'))->success();
            return redirect()->route('sellers.index');
        }
    }

    //calculate seller commission after payment
   public function calculateCommission($order)
{
    $total_admin_commission = 0;
    $total_seller_earning = 0;
    $seller = null;

    foreach ($order->orderDetails as $orderDetail) {
        $orderDetail->payment_status = 'paid';
        $orderDetail->save();

        if ($orderDetail->product->user->user_type == 'seller') {
            // FIX: Only fetch the seller once, or check if it's the same seller
            if (!$seller) {
                $seller = $orderDetail->product->user->shop;
            }

            $commission_percentage = 0;
            if (get_setting('vendor_commission_activation')) {
                $commission_percentage = get_setting('category_wise_commission')
                    ? $orderDetail->product->main_category->commision_rate
                    : get_setting('vendor_commission');
            }

            $admin_commission = ($orderDetail->price * $commission_percentage) / 100;

            if (get_setting('product_manage_by_admin') == 1) {
                $item_earning = ($orderDetail->tax + $orderDetail->price) - $admin_commission;
            } else {
                $item_earning = ($orderDetail->tax + $orderDetail->shipping_cost + $orderDetail->price) - $admin_commission;
            }

            $total_admin_commission += $admin_commission;

            // Update the single $seller object in memory
            if ($order->payment_type == 'cash_on_delivery' && get_setting('product_manage_by_admin') != 1) {
                $seller->admin_to_pay -= $admin_commission;
                $total_seller_earning -= $admin_commission;
            } else {
                $seller->admin_to_pay += $item_earning;
                $total_seller_earning += $item_earning;
            }
        }
    }

    if ($seller) {
        if ($order->shop != null && $order->payment_type != 'cash_on_delivery') {
            $total_seller_earning -= $order->coupon_discount;
            $seller->admin_to_pay -= $order->coupon_discount;
        }

        $seller->save();

        $commission_history = new Commission;
        $commission_history->commissionable()->associate($order);
        $commission_history->ownable()->associate($seller);
        $commission_history->admin_commission = $total_admin_commission;
        $commission_history->ownable_earning = $total_seller_earning;
        $commission_history->save();
    }
}
}
