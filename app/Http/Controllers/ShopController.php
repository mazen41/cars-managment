<?php

namespace App\Http\Controllers;

use App\Http\Requests\SellerRegistrationRequest;
use App\Rules\YemenPhone;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Models\BusinessSetting;
use App\Models\Country;
use Auth;
use Hash;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{

    public function __construct()
    {
        $this->middleware('user', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = Auth::user()->shop;
        return view('seller.shop', compact('shop'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response | \Illuminate\Contracts\View\View
     */
    public function create()
    {
        if (Auth::check()) {
            if ((Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'customer')) {
                flash(translate('Admin or Customer cannot be a seller'))->error();
                return back();
            }
            if (Auth::user()->user_type == 'seller') {
                flash(translate('This user already a seller'))->error();
                return back();
            }
        } else {
            $countries = Country::isEnabled()->get();
            return view('auth.default.seller_registration', compact('countries'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(SellerRegistrationRequest $request)
    {

        try {
            $user = new User;
            $user->name = $request->name;
            //$user->email = $request->email;
            $user->phone =  $request->phone;
            $user->user_type = "seller";
            $user->password = Hash::make($request->password);
            $user->verification_code = generateVerificationCode();

            if ($user->save()) {
                $shop = new Shop;
                $shop->user_id = $user->id;
                $shop->name = $request->shop_name;
                $shop->address = $request->address;
                $shop->slug = preg_replace('/\s+/', '-', str_replace("/", " ", $request->shop_name));
                $shop->save();

                auth()->login($user, false);

                // if (BusinessSetting::where('type', 'email_verification')->first()->value == 0) {
                //     $user->email_verified_at = date('Y-m-d H:m:s');
                //     $user->save();
                // } else {
                //     try {
                //         $user->notify(new EmailVerificationNotification());
                //     } catch (\Throwable $th) {
                //         $shop->delete();
                //         $user->delete();
                //         flash(translate('Seller registration failed. Please try again later.'))->error();
                //         return back();
                //     }
                // }

                $otpController = new OTPVerificationController;
                $otpController->send_code($user);

                flash(translate('Your Shop has been created successfully!'))->success();
                return redirect()->route('seller.shop.index');
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    public function destroy($id)
    {
        //
    }
}
