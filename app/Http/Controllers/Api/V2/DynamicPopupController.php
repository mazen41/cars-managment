<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\V2\DynamicPopupResource;
use App\Models\DynamicPopup;

class DynamicPopupController extends Controller
{
    public function index (Request $request)
    {
        $popups = DynamicPopup::where('status', 1)->whereNull('show_subscribe_form')->get();
        return new DynamicPopupResource($popups);
    }
}
