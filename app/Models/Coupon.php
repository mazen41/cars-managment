<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'user_id', 'type', 'is_welcome_coupon','code','details','discount', 'discount_type', 'start_date', 'end_date', 'status'
    ];

    //admins
    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class);
    }

// customers
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_coupons', 'coupon_id', 'user_id');
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

}
