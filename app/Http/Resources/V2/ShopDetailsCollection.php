<?php

namespace App\Http\Resources\V2;

use App\Models\FollowSeller;
use Illuminate\Http\Resources\Json\JsonResource;
use \App\Models\Product;

class ShopDetailsCollection extends JsonResource
{
    public function toArray($request)
    {
        return
        [
            'id' => $this->id,
            'user_id' => intval($this->user_id) ,
            'name' => $this->name,
            'title' => $this->meta_title,
            'description' => $this->meta_description,
            'delivery_pickup_latitude' => $this->delivery_pickup_latitude,
            'delivery_pickup_longitude' => $this->delivery_pickup_longitude,
            'logo' => uploaded_asset($this->logo),
            'sliders' => get_images_path($this->sliders),
            'address' => $this->address,
            'phone' => $this->phone,
            'facebook' => $this->facebook,
            'google' => $this->google,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'cash_on_delivery_status' => $this->cash_on_delivery_status,
            'is_followed'   => $this->getIsFollowed(auth('api')->user(), $this->id),
            'rating' => (double) $this->rating,
            'verified'=> $this->verification_status==1,
            'verify_text'=> $this->verification_status==1?translate("Verified seller"):translate("Non-Verified seller"),
            'email'=> $this->user->email,
            'products'=> $this->user->products()->count(),
            'orders'=> $this->user->seller_orders()->where("delivery_status","delivered")->count(),
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertPhotos($data){
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }

    protected function getIsFollowed($user, $shop_id){
        if($user){
            return  FollowSeller::where('user_id', $user->id)->where('shop_id', $shop_id)->exists();
        }
         return false;
    }
}
