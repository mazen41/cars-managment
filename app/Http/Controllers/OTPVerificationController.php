<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Auth;
use App\Models\User;
use App\Utility\SmsUtility;
use Hash;

class OTPVerificationController extends Controller
{

    public function __construct(){
        $this->middleware('throttle:1,2')->only(['resend_verificcation_code','resend_verificcation_code_ajax']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verification(Request $request){
        if (Auth::check() && Auth::user()->phone_verified_at == null) {
            return view('otp_systems.frontend.auth.default.user_verification');
        }
        else {
            flash('You have already verified your number')->warning();
            return redirect()->route('home');
        }
    }


    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function verify_phone(Request $request){
        $user = Auth::user();
        if ($user->verification_code == $request->verification_code) {
            $user->phone_verified_at = date('Y-m-d h:m:s');
            $user->verification_code = null;
            $user->save();

            flash('Your phone number has been verified successfully')->success();
             if($user->user_type == 'seller') {
            return redirect()->route('seller.dashboard');
        }
            return redirect()->route('home');
        }
        else{
            flash('Invalid Code')->error();
            return back();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function resend_verificcation_code(Request $request){
        $user = Auth::user();
        $user->verification_code = generateVerificationCode();
        $user->save();
        SmsUtility::phone_number_verification($user);

        return back();
    }


    public function resend_verificcation_code_ajax(){
      try{
        $user = Auth::user();
        $user->verification_code =generateVerificationCode();
        $user->save();
        SmsUtility::phone_number_verification($user);
      } catch(\Exception $e){
        return response()->json(['success'=> false, 'message'=> $e->getMessage()]);
      }
      return response()->json(['success'=> true, 'message'=> translate('code resent successfully')]);

    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function reset_password_with_code(Request $request)
    {
        $phone = "+{$request['country_code']}{$request['phone']}";

        if (($user = User::where('phone', $phone)->where('verification_code', $request->code)->first()) != null) {
            if ($request->password == $request->password_confirmation) {
                $user->password = Hash::make($request->password);
                $user->phone_verified_at = date('Y-m-d h:m:s');
                $user->save();
                event(new PasswordReset($user));
                auth()->login($user, true);

                if (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') {
                    flash("Password has been reset successfully")->success();
                    return redirect()->route('admin.dashboard');
                }
                flash("Password has been reset successfully")->success();
                return redirect()->route('home');
            } else {
                flash("Password and confirm password didn't match")->warning();
                return view('otp_systems.frontend.auth.default.reset_with_phone');
            }
        } else {
            flash("Verification code mismatch")->error();
            return view('otp_systems.frontend.auth.default.reset_with_phone');
        }
    }


    /**
     * @param  User $user
     * @return void
     */

    public function send_code($user){
        SmsUtility::phone_number_verification($user);
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_order_code($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::order_placement($phone, $order);
        }
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_delivery_status($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::delivery_status_change($phone, $order);
        }
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_payment_status($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::payment_status_change($phone, $order);
        }
    }
}
