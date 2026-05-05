<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BusinessSettingCollection;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessSettingController extends Controller
{

    public function get_settings(Request $request)
{
    $allowedSettings = [
        "facebook_login", "google_login", "twitter_login", "pickup_point",
        "apple_login", "wallet_system", "email_verification", "conversation_system",
        "shipping_type", "google_recaptcha", "vendor_system_activation",
        "last_viewed_product_activation", "notification_show_type", "contact_phone",
        "recharge_wallet_active", "car_reservation_amount", "insurance_deposit_amount", "contact_email"
    ];
    $messages = [
        'settings.*.in' => "This setting is not valid"
    ];
    $validator = Validator::make($request->all(),[
        'settings'   => 'nullable|array',
        'settings.*' => 'string|in:' . implode(',', $allowedSettings),
    ], $messages);

    if($validator->fails()){
        return response()->json([
            'success'   => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ]);
    }

    $requested = $request->input('settings');
    $keysToFetch = !empty($requested)
        ? array_intersect($allowedSettings, $requested)
        : $allowedSettings;

    $settings = BusinessSetting::whereIn('type', $keysToFetch)->get();

    return new BusinessSettingCollection($settings);
}
}
