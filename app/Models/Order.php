<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model
{
    protected $casts = ['manual_payment_data' => 'object'];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    /**
     * Summary of commissions
     * @return MorphOne<Commission, Order>
     */
    public function commission(): MorphOne
    {
        return $this->morphOne(Commission::class, 'commissionable');
    }

    public function combined_order() : BelongsTo{
        return $this->belongsTo(CombinedOrder::class, 'combined_order_id');
    }
    public function getSiblingOrdersAttribute() {
        if (!$this->combined_order_id) {
            return collect();
        }

        return $this->combined_order
            ->orders()
            ->where('id', '!=', $this->id)
            ->select(['id', 'code'])
            ->get();
    }
    public function deliveryHistory()
    {
        return $this->morphMany(DeliveryHistory::class, 'orderable');
    }
}
