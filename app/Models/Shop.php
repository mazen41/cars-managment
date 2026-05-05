<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shop extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'meta_title', 'meta_description',
        'logo', 'shipping_cost', 'delivery_pickup_longitude',
        'delivery_pickup_latitude', 'facebook', 'instagram', 'google',
        'twitter', 'youtube', 'cash_on_delivery_status', 'bank_payment_status',
        'bank_name', 'bank_acc_name', 'bank_acc_no', 'bank_routing_no', 'sliders'
    ];
    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller_package()
    {
        return $this->belongsTo(SellerPackage::class);
    }

    public function followers()
    {
        return $this->hasMany(FollowSeller::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable')->where('status', 1);
    }

     public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

     public function getRatingCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            User::class,
            'id',
            'seller_id',
            'user_id',
            'id'
        );
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }

    public function getPaidOrdersAttribute()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('grand_total');
    }
    public function getTotalAdminCommissionAttribute()
    {
        return $this->commissions()
            ->sum('admin_commission');
    }
    public function payments()
    {
        return $this->hasManyThrough(
            Payout::class,
            User::class,
            'id',
            'seller_id',
            'user_id',
            'id'
        );
    }
    public function wallets()
{
    return $this->hasMany(ShopWallet::class);
}
    /**
     * Summary of commissions
     * @return MorphMany<Commission, Seller>
     */
    public function commissions(): MorphMany
    {
        return $this->morphMany(Commission::class, 'ownable');
    }

    public function hasCompletedPaymentInfo(){
        return $this->cash_on_delivery_status || $this->bank_payment_status;
    }
    public function hasCompletedDeliveryInfo(){
        return $this->delivery_pickup_longitude != null && $this->delivery_pickup_latitude != null;
    }
    public function hasCompletedProfileInfo(){
        return ($this->name != null && $this->phone != null && $this->address != null && $this->commercial_register !=null && $this->tax_number !=null);
    }
    public function getBasicInfoForm()
    {
        return view('seller.shop.forms.basic_info', [
            'shop' => $this
        ])->render();
    }

    public function getDeliveryPickupForm()
    {
        return view('seller.shop.forms.delivery_pickup', [
            'shop' => $this
        ])->render();
    }

    public function getBannerSettingsForm()
    {
        return view('seller.shop.forms.banner_settings', [
            'shop' => $this
        ])->render();
    }

    public function getSocialMediaForm()
    {
        return view('seller.shop.forms.social_media', [
            'shop' => $this
        ])->render();
    }
    public function getPaymentSettingsForm()
    {
        return view('seller.shop.forms.payment_settings', [
            'shop' => $this
        ])->render();
    }

     public function incrementOwedAmount($amount)
    {
        $this->increment("admin_to_pay", $amount);
    }

     public function decrementOwedAmount($amount)
    {
        $this->decrement("admin_to_pay", $amount);
    }
}
