<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\OTPVerificationController;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:2,1')->only('verify');
        $this->middleware('throttle:1,1')->only('resend', 'resend_ajax');
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     */
    public function show(Request $request)
    {
        // if ($request->user()->email != null && !$request->user()->hasVerifiedEmail()) {
        //     return view('auth.default.verify_email');
        // }
        if($request->user()->phone != null && $request->user()->phone_verified_at == null) {
            return redirect()->route('verification');
        }
        return redirect($this->redirectPath());
    }


    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', true);
    }

    public function resend_ajax(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
          return response()->json(['success'=> false, 'verified' =>true ,'message'=> translate('Email has already been verified')]);
        }
        try{
            $request->user()->sendEmailVerificationNotification();
            return response()->json(['success'=> true, 'message'=> translate('A fresh verification link has been sent to your email address.')]);
        } catch(\Exception $e){
            return response()->json(['success'=> false, 'message'=> $e->getMessage()]);
        }
    }

    public function verification_confirmation($code){
        $user = User::where('verification_code', $code)->first();
        if($user != null){
            $user->email_verified_at = Carbon::now();
            $user->save();
            auth()->login($user, true);
            flash(translate('Your email has been verified successfully'))->success();
        }
        else {
            flash(translate('Sorry, we could not verifiy you. Please try again'))->error();
            return view('errors.expired_confirmation_link');
        }

        if($user->user_type == 'seller') {
            return redirect()->route('seller.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
