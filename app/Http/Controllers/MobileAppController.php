<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Artisan;
use Illuminate\Http\Request;

class MobileAppController extends Controller{

    public function __construct(){
        $this->middleware(['permission:mobile_app_settings']);
    }
    public function app_version(){
        return view('backend.mobile_app_settings.version');
    }

    public function update_app_version(Request $request){

        $all_settings = $request->settings;
        foreach($all_settings as $key =>$value){
            if($value == 'on'){
                $value = 1;
            }
            if($value == 'off'){
                $value == 0;
            }
            BusinessSetting::updateOrCreate(['type' => $key], ['type' => $key, 'value' => $value]);
        }
        Artisan::call('cache:clear');
        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function app_sliders(Request $request){
        $lang = $request->lang;
        return view('backend.mobile_app_settings.sliders', compact('lang'));
    }
}
