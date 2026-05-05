<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Events\AccountDeletionRequested;
use App\Events\CustomerRegistered;
use App\Http\Controllers\OTPVerificationController;
use App\Mail\GuestAccountOpeningMailManager;
use App\Models\Address;
use App\Models\BusinessSetting;
use App\Models\Shop;
use App\Rules\YemenPhone;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use Hash;
use Socialite;
use App\Models\Cart;
use App\Rules\Recaptcha;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:5,1')->only('login', 'signup','confirmCode');
        $this->middleware('throttle:1,2')->only('resendCode');
        $this->middleware('auth:sanctum')->only('signUpPassword', 'resendCode', 'confirmCode', 'user', 'logout', 'account_deletion', 'account_deletion_request', 'cancel_deletion_request');

    }
    public function signup(Request $request)
    {
        try{
            $messages = array(
                'name.required' => translate('Name is required'),
                'email' => translate('Email must be a valid email address'),
                'phone.numeric' => translate('Phone must be a number.'),
                'phone.unique' => translate('The phone has already been taken'),
                'email.unique'  => translate('Email is required'),
                'email.email' => translate('Email must be a valid email address'),
                'phone.min' =>  translate("Phone number must be 9 digits long"),
                "privacy_policy.accepted" => translate("You must accept our privacy policy."),
            );

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'phone' => [
                    'required',
                    'numeric',
                    new YemenPhone,
                    'unique:users,phone',
                ],
                'email' => ['nullable', 'email'],
                'privacy_policy' => 'accepted',
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'access_token' => '',
                    'message' => $validator->errors()->first()
                ]);
            }
            DB::beginTransaction();
            $user = new User();
            $user->name = $request->name;
            $user->phone = $request->phone;

            if ($request->email ) {
                $user->email = $request->email;
            }
            $user->verification_code = generateVerificationCode();
            $user->email_verified_at = null;
            if ($user->email != null) {
                if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                    $user->email_verified_at = Carbon::now();
                }
            }
            $user->save();
            DB::commit();
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);

            if ($request->email) {
                $user->notify(new AppEmailVerificationNotification());
            }

            return response()->json([
                'result' => true,
                'access_token' => $user->createToken('API Token')->plainTextToken,
                'message' => translate('Your account has been created. Please verify your account to login'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'access_token'=> '',
                'message' => translate('Something went wrong'),
            ], 500);
        }

    }
    /**
     *
     *  Sign up for Seller account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shopSignup(Request $request)
    {
        try{
            $messages = array(
                'name.required' => translate('Name is required'),
                'email' => translate('Email must be a valid email address'),
                'phone.numeric' => translate('Phone must be a number.'),
                'phone.unique' => translate('The phone has already been taken'),
                'email.unique'  => translate('Email is already used'),
                'email.email' => translate('Email must be a valid email address'),
                'phone.min' =>  translate("Phone number must be 9 digits long"),
                "privacy_policy.accepted" => translate("You must accept our privacy policy."),
                'password.required' => translate('Password is required'),
                'password.confirmed' => translate('Password confirmation does not match'),
                'password.min' => translate('Minimum 6 digits required for password'),
            );

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'phone' => [
                    'required',
                    'numeric',
                    new YemenPhone,
                    'unique:users,phone',
                ],
                'email' => ['nullable', 'email', 'unique:users,email'],
                'password'  => 'required|confirmed|min:6',
                'shop_name' => 'required|string|max:191',
                'shop_address' => 'required|string|max:191',
                'privacy_policy' => 'accepted',
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'access_token' => '',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ]);
            }
            DB::beginTransaction();
            // Registering User
            $user = new User();
            $user->user_type = 'seller';
            $user->name = $request->name;
            $user->phone = $request->phone;

            if ($request->email ) {
                $user->email = $request->email;
            }

            $user->verification_code = generateVerificationCode();
            $user->email_verified_at = null;
            if ($user->email != null) {
                if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                    $user->email_verified_at = Carbon::now();
                }
            }
            $user->password = Hash::make($request->password);

            // Registering Shop
            $shop = new Shop();
            $shop->name = $request->shop_name;
            $shop->address = $request->shop_address;
            $user->save();
            $user->shop()->save($shop);
            DB::commit();
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);

            // if ($request->email) {
            //     $user->notify(new AppEmailVerificationNotification());
            // }

            return $this->loginSuccess($user);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'access_token'=> '',
                'token_type' => 'Bearer',
                'message' => translate('Something went wrong'),
            ], 500);
        }

    }

    public function signUpPassword(Request $request)
    {
        $user = auth()->user();

        if($user->password){
            return response()->json([
                'result' => false,
                'message' => translate('Password is already set for this account')
            ]);
        }
        $messages = array(
            'password.required' => translate('Password is required'),
            'password.confirmed' => translate('Password confirmation does not match'),
            'password.min' => translate('Minimum 6 digits required for password'),
            'phone.min' =>  translate("Phone number must be 9 digits long"),
        );

        $validator = Validator::make($request->all(), [
            'password' => [
                 'required','min:6','confirmed',
            ],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();
        $token = $request->bearerToken();
        event(new CustomerRegistered($user));
        return $this->loginSuccess($user, $token);
    }

    public function resendCode()
    {
        $user = auth()->user();
        $user->verification_code = generateVerificationCode();
        $user->save();
        if ($user->email) {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = auth()->user();

        if ($user->verification_code == $request->verification_code) {
            $user->phone_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your phone is now verified'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    public function login(Request $request)
    {
        $messages = array(
            'phone.required' => translate('Phone is required'),
            'phone.numeric' => translate('Phone must be a number.'),
            'password.required' => translate('Password is required'),
        );

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'phone' => [
                'required',
                new YemenPhone
            ],
            'user_type' => [
                'required',
                Rule::in(['customer', 'seller', 'delivery_boy'])
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        switch ($request->user_type) {
            case 'customer':
                $user = User::where('phone', $request->phone)->where('user_type', 'customer')->first();
                break;
            case 'seller':
                $user = User::where('phone', $request->phone)->where('user_type', 'seller')->first();
                break;
            case 'delivery_boy':
                $user = User::where('phone', $request->phone)->where('user_type', 'delivery_boy')->first();
                break;
            }

        if(!$user) {
            return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
        }

        if($user->banned) {
            return response()->json(['result' => false, 'message' => translate('User is banned'), 'user' => null], 401);
        }

        if (Hash::check($request->password, $user->password)) {
            return $this->loginSuccess($user,'');

        } else {
            return response()->json(['result' => false, 'message' => translate('Unauthorized'), 'user' => null], 401);
        }

    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged out')
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            case 'twitter':
                $social_user = Socialite::driver('twitter');
                break;
            case 'apple':
                $social_user = Socialite::driver('sign-in-with-apple')
                    ->scopes(['name', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        if ($request->social_provider == 'twitter') {
            $social_user_details = $social_user->userFromTokenAndSecret($request->access_token, $request->secret_token);
        } else {
            $social_user_details = $social_user->userFromToken($request->access_token);
        }

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            $existingUserByProviderId->access_token = $social_user_details->token;
            if ($request->social_provider == 'apple') {
                $existingUserByProviderId->refresh_token = $social_user_details->refreshToken;
                if (!isset($social_user->user['is_private_email'])) {
                    $existingUserByProviderId->email = $social_user_details->email;
                }
            }
            $existingUserByProviderId->save();
            return $this->loginSuccess($existingUserByProviderId);
        } else {
            $existing_or_new_user = User::firstOrNew(
                [['email', '!=', null], 'email' => $social_user_details->email]
            );

            // $existing_or_new_user->user_type = 'customer';
            $existing_or_new_user->provider_id = $social_user_details->id;

            if (!$existing_or_new_user->exists) {
                if ($request->social_provider == 'apple') {
                    if ($request->name) {
                        $existing_or_new_user->name = $request->name;
                    } else {
                        $existing_or_new_user->name = 'Apple User';
                    }
                } else {
                    $existing_or_new_user->name = $social_user_details->name;
                }
                $existing_or_new_user->email = $social_user_details->email;
                $existing_or_new_user->email_verified_at = date('Y-m-d H:m:s');
            }

            $existing_or_new_user->save();
            event(new CustomerRegistered($existing_or_new_user));
            return $this->loginSuccess($existing_or_new_user);
        }
    }



    public function loginSuccess($user, $token = null)
    {

        if (!$token) {
            $token = $user->createToken('API Token')->plainTextToken;
        }

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone,
                'has_password' => $user->password != null,
                'email_verified' => $user->email_verified_at != null,
                'phone_verified' => $user->phone_verified_at != null,
                'deletion_requested' => (bool) $user->deletion_request,
                'deletion_requested_at' => $user->deletion_requested_at? $user->deletion_requested_at->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }


    protected function loginFailed()
    {

        return response()->json([
            'result' => false,
            'message' => translate('Login Failed'),
            'access_token' => '',
            'token_type' => '',
            'expires_at' => null,
            'user' => [
                'id' => 0,
                'type' => '',
                'name' => '',
                'email' => '',
                'avatar' => '',
                'avatar_original' => '',
                'phone' => ''
            ]
        ]);
    }


    public function account_deletion()
    {
        if (auth()->user()) {
            Cart::where('user_id', auth()->user()->id)->delete();
        }
        $auth_user = auth()->user();
        $auth_user->tokens()->where('id', $auth_user->currentAccessToken()->id)->delete();


        User::destroy(auth()->user()->id);

        return response()->json([
            "result" => true,
            "message" => translate('Your account deletion successfully done')
        ]);
    }

    public function account_deletion_request()
    {
        $auth_user = auth()->user();
        if(!$auth_user){
            return response()->json([
                "result" => false,
                "message" => translate('Unauthorized')
            ], 401);
        }
        if($auth_user->deletion_requested) {
            return response()->json([
                "result" => false,
                "message" => translate('Your account deletion request already sent')
            ]);
        }

        $auth_user->deletion_request = true;
        $auth_user->deletion_requested_at = Carbon::now();
        $auth_user->save();

        //logout the user
        $auth_user->tokens()->where('id', $auth_user->currentAccessToken()->id)->delete();

        event(new AccountDeletionRequested($auth_user));

        return response()->json([
            "result" => true,
            "message" => translate('Your account deletion request successfully sent')
        ]);
    }

    public function cancel_deletion_request(){
          try {
            $auth_user = auth('api')->user();
            if (!$auth_user) {
                return response()->json([
                    "result" => false,
                    "message" => translate('Unauthorized')
                ], 401);
            }
            if (!$auth_user->deletion_request) {
                return response()->json([
                    "result" => false,
                    "message" => translate('No deletion request found for this account')
                ]);
            }
            $auth_user->deletion_request = false;
            $auth_user->deletion_requested_at = null;
            $auth_user->save();

            return response()->json([
                "result" => true,
                "message" => translate('Account deletion request cancelled successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "result" => false,
                "message" => translate('Something went wrong')
            ], 500);
        }
    }
    public function getUserInfoByAccessToken(Request $request)
    {
        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return $this->loginFailed();
        }
        $user = $token->tokenable;

        if ($user == null) {
            return $this->loginFailed();
        }

        return $this->loginSuccess($user, $request->access_token);
    }
}
