<?php

namespace App\Http\Controllers\Seller;

use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\ShopVerificationNotification;
use Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;
        return view('seller.shop', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = Shop::find($request->shop_id);

        if ($request->has('name') && $request->has('address')) {
            // Basic info update logic
            if ($request->has('shipping_cost')) {
                $shop->shipping_cost = $request->shipping_cost;
            }

            $shop->name             = $request->name;
            $shop->address          = $request->address;
            $shop->phone            = $request->phone;
            $shop->slug             = preg_replace('/\s+/', '-', $request->name) . '-' . $shop->id;
            $shop->meta_title       = $request->meta_title;
            $shop->meta_description = $request->meta_description;
            $shop->logo             = $request->logo;
            $shop->tax_number       = $request->tax_number;
            $shop->commercial_register = $request->commercial_register;
        } elseif ($request->has('delivery_pickup_longitude') && $request->has('delivery_pickup_latitude')) {
            // Delivery pickup update logic
            $shop->delivery_pickup_longitude    = $request->delivery_pickup_longitude;
            $shop->delivery_pickup_latitude     = $request->delivery_pickup_latitude;
        } elseif (
            $request->has('facebook') ||
            $request->has('google') ||
            $request->has('twitter') ||
            $request->has('youtube') ||
            $request->has('instagram')
        ) {
            // Social media update logic
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->google = $request->google;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;
        } elseif (
            $request->has('top_banner') ||
            $request->has('sliders') ||
            $request->has('banner_full_width_1') ||
            $request->has('banners_half_width') ||
            $request->has('banner_full_width_2')
        ) {
            // Banner settings update logic
            $shop->top_banner = $request->top_banner;
            $shop->sliders = $request->sliders;
            $shop->banner_full_width_1 = $request->banner_full_width_1;
            $shop->banners_half_width = $request->banners_half_width;
            $shop->banner_full_width_2 = $request->banner_full_width_2;
        }

        if ($shop->save()) {
            flash(translate('Your Shop has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }
    public function updatePaymentSettings(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'bank_payment_status' => 'boolean',
            'cash_on_delivery_status' => 'boolean',
        ]);

        // Additional validation when bank_payment_status is true
        if ($request->bank_payment_status) {
            $validator->after(function ($validator) use ($request) {
                // Check if at least one wallet is provided
                if (empty($request->wallet_names)) {
                    $validator->errors()->add('wallets', translate('At least one wallet is required when transfer payment is enabled'));
                }

                // Validate each wallet's data
                if (!empty($request->wallet_names)) {
                    foreach ($request->wallet_names as $index => $name) {
                        if (empty($name)) {
                            $validator->errors()->add("wallet_names.$index", translate('Wallet name is required'));
                        }
                        if (empty($request->account_holder_names[$index])) {
                            $validator->errors()->add("account_holder_names.$index", translate('Account holder name is required'));
                        }
                        if (empty($request->account_numbers[$index])) {
                            $validator->errors()->add("account_numbers.$index", translate('Account number is required'));
                        }
                    }
                }
            });
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $shop = Shop::find($request->shop_id);

        // Update shop payment settings
        $shop->bank_payment_status = $request->bank_payment_status ?? 0;
        $shop->cash_on_delivery_status = $request->cash_on_delivery_status ?? 0;

        // Handle wallets if bank payment is enabled
        if ($request->bank_payment_status) {
            if ($request->has('wallet_names')) {
                // Get arrays from request
                $walletIds = $request->wallet_ids ?? [];
                $walletNames = $request->wallet_names;
                $accountHolderNames = $request->account_holder_names;
                $accountNumbers = $request->account_numbers;

                // Delete wallets that were removed from the form
                $shop->wallets()->whereNotIn('id', array_filter($walletIds))->delete();

                // Update or create wallets
                foreach ($walletNames as $index => $name) {
                    $walletId = $walletIds[$index] ?? null;

                    $walletData = [
                        'wallet_name' => $name,
                        'account_holder_name' => $accountHolderNames[$index],
                        'account_number' => $accountNumbers[$index],
                    ];

                    if ($walletId) {
                        // Update existing wallet
                        $shop->wallets()->where('id', $walletId)->update($walletData);
                    } else {
                        // Create new wallet
                        $shop->wallets()->create($walletData);
                    }
                }
            }
        } else {
            // If bank payment is disabled, remove all wallets
            $shop->wallets()->delete();
        }

        if ($shop->save()) {
            flash(translate('Payment settings have been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function verify_form()
    {
        if (Auth::user()->shop->verification_info == null) {
            $shop = Auth::user()->shop;
            return view('seller.verify_form', compact('shop'));
        } else {
            flash(translate('Sorry! You have sent verification request already.'))->error();
            return back();
        }
    }

    public function verify_form_store(Request $request)
    {
        $data = array();
        $i = 0;
        foreach (json_decode(BusinessSetting::where('type', 'verification_form')->first()->value) as $key => $element) {
            $item = array();
            if ($element->type == 'text') {
                $item['type'] = 'text';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i];
            } elseif ($element->type == 'select' || $element->type == 'radio') {
                $item['type'] = 'select';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i];
            } elseif ($element->type == 'multi_select') {
                $item['type'] = 'multi_select';
                $item['label'] = $element->label;
                $item['value'] = json_encode($request['element_' . $i]);
            } elseif ($element->type == 'file') {
                $item['type'] = 'file';
                $item['label'] = $element->label;
                $item['value'] = $request['element_' . $i]->store('uploads/verification_form');
            }
            array_push($data, $item);
            $i++;
        }
        $shop = Auth::user()->shop;
        $shop->verification_info = json_encode($data);
        if ($shop->save()) {
            $users = User::findMany([User::where('user_type', 'admin')->first()->id]);
            $data = array();
            $data['shop'] = $shop;
            $data['status'] = 'submitted';
            $data['notification_type_id'] = get_notification_type('shop_verify_request_submitted', 'type')->id;
            Notification::send($users, new ShopVerificationNotification($data));

            flash(translate('Your shop verification request has been submitted successfully!'))->success();
            return redirect()->route('seller.dashboard');
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function show()
    {
    }
}
