<?php

namespace App\Http\Controllers\Api\V2;

class MobileAppController extends Controller
{

    public function get_app_version($os = null){
        if(!$os) {
            return response()->json([
                'result' => false,
                'message' => 'OS is required'
            ]);
        }

        switch($os) {
            case 'android':
                $min_app_version = get_setting('android_app_min_version');
                $should_force = get_setting('should_force_android_update');
                break;
            case 'ios':
                $min_app_version = get_setting('ios_app_min_version');
                $should_force = get_setting('should_force_ios_update');
                break;
            default:
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid OS'
                ]);
        }

        return response()->json([
            'result' => true,
            'min_app_version' => $min_app_version ?? '1.0.0',
            'should_force'  => $should_force ? true:  false
        ]);
    }
}
