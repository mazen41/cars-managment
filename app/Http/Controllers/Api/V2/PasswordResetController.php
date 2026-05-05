<?php

namespace App\Http\Controllers\Api\V2;

use App\Notifications\AppEmailVerificationNotification;
use App\Rules\YemenPhone;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\OTPVerificationController;

use Hash;
use Validator;

class PasswordResetController extends Controller
{

     public function __construct()
    {
        $this->middleware('throttle:5,1')->only('confirmReset', 'verifyCode');
        $this->middleware('throttle:1,2')->only('resendCode', 'forgetRequest');
        $this->middleware('auth:sanctum')->only('confirmReset');

    }
    public function forgetRequest(Request $request)
    {
       $validator = Validator::make($request->all(), [
        'phone' => [
            'required',
            new YemenPhone
            ]
       ]);
       if($validator->fails()){
        return response()->json([
              'result' => false,
                'messages' => $validator->errors()
        ]);
       }
       $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }


        $user->verification_code = generateVerificationCode();
        $user->save();

        $otpController = new OTPVerificationController();
        $otpController->send_code($user);

        return response()->json([
            'result' => true,
            'message' => translate('A code is sent')
        ], 200);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'exists:users,phone',
                new YemenPhone
            ],
            'verification_code'  => [
                'required',
                'min:6',
                'numeric'
            ]
        ]);

        $user = User::where('phone', $request->phone)->whereNotNull('verification_code')->first();
        if(!$user){
             return response()->json([
                'result' => false,
                'message' => translate('No user is found'),
            ], 200);
        }

       if ($user->verification_code == $request->verification_code) {
            $user->phone_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'access_token' => $user->createToken('API Token')->plainTextToken,
                'message' => translate('Verified successfully, you can reset your password now'),
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
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'access_token'=> '',
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }

    }

    public function confirmReset(Request $request)
    {
         $messages = array(
            'password.required' => translate('Password is required'),
            'password.confirmed' => translate('Password confirmation does not match'),
            'password.min' => translate('Minimum 6 digits required for password'),
        );

        $validator = Validator::make($request->all(), [
            'password' => [
                 'required','min:6','confirmed',
            ],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'result' => true,
            'message' => translate('Your password is reset successfully'),
        ], 200);

    }

    public function resendCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'exists:users,phone',
                new YemenPhone
            ]
        ]);
        if($validator->fails()){
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()
            ]);
        }

       $user = User::where('phone', $request->phone)->first();


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }

        $user->verification_code = generateVerificationCode();
        $user->save();

        $otpController = new OTPVerificationController();
        $otpController->send_code($user);



        return response()->json([
            'result' => true,
            'message' => translate('A code is sent again'),
        ], 200);
    }
}
