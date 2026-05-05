<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Notifications\EmailVerificationNotification;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use NotificationChannels\WebPush\HasPushSubscriptions;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable,
        HasApiTokens,
        HasRoles,
        Impersonate,
        HasPushSubscriptions,
        Sortable,
        HasFactory;

    public function canImpersonate()
    {
        return $this->user_type == "admin" ||
            $this->hasPermissionTo("login_as_seller");
    }

    public function canBeImpersonated()
    {
        return $this->user_type != "admin";
    }
    public function routeNotificationForFCM()
    {
        return $this->device_token;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "user_type",
        "email",
        "password",
        "address",
        "city",
        "postal_code",
        "phone",
        "country",
        "provider_id",
        "email_verified_at",
        "verification_code",
        "phone_verified_at",
    ];
    protected $casts = [
        "deletion_requested_at" => "datetime",
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["password", "remember_token"];

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function affiliate_user()
    {
        return $this->hasOne(AffiliateUser::class);
    }

    public function affiliate_withdraw_request()
    {
        return $this->hasMany(AffiliateWithdrawRequest::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function seller_orders()
    {
        return $this->hasMany(Order::class, "seller_id");
    }
    public function seller_sales()
    {
        return $this->hasMany(OrderDetail::class, "seller_id");
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class)->orderBy("created_at", "desc");
    }

    public function club_point()
    {
        return $this->hasOne(ClubPoint::class);
    }

    public function customer_package()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SellerPackagePayment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function product_queries()
    {
        return $this->hasMany(ProductQuery::class, "customer_id");
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function userCoupon()
    {
        return $this->hasOne(UserCoupon::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class, "user_id");
    }
    public function getPaidAmountAttribute()
    {
        $internal_orders = $this->orders
            ->where("payment_status", "paid")
            ->sum("grand_total");
        return $internal_orders;
    }
    public function getUnpaidAmountAttribute()
    {
        $internal_orders = $this->orders
            ->where("payment_status", "unpaid")
            ->where("delivery_status", "!=", "cancelled")
            ->sum("grand_total");
        return $internal_orders;
    }
    public function getRefundAmountAttribute()
    {
        return $this->refund_requests
            ->where("refund_status", "1")
            ->sum("refund_amount");
    }
    public function getOrdersCountAttribute()
    {
        return $this->orders->count();
    }
    public function getWalletSpentAmountAttribute()
    {
        return $this->wallets()->where("amount", "<", 0)->sum("amount");
    }

    public function getApprovedWalletAmountAttribute()
    {
        return $this->wallets()
            ->approved()
            ->where("amount", ">", 0)
            ->sum("amount");
    }

    public function coupons()
    {
        return $this->belongsToMany(
            Coupon::class,
            "users_coupons",
            "user_id",
            "coupon_id",
        );
    }

    public function carInspector()
    {
        return $this->hasOne(CarInspector::class);
    }

    public function carInspections()
    {
        return $this->hasMany(CarInspection::class, "inspector_id");
    }

    /**
     * Decrement user balance with wallet record
     * @param mixed $amount
     * @param mixed $payment_details
     */

    public function decrementBalance($amount, $payment_details)
    {
        $this->balance -= $amount;
        $this->save();

        $walletRecord = new Wallet();
        $walletRecord->amount = -$amount;
        $walletRecord->payment_method = $payment_details['payment_type'] ?? 'unknown';
        $walletRecord->payment_details = $payment_details;
        $walletRecord->approval = 1;
        $this->wallets()->save($walletRecord);
        return $walletRecord->id;
    }

    /**
     * Increment user balance with wallet record
     * @param mixed $amount
     * @param mixed $payment_details
     */

    public function incrementBalance($amount, $payment_method, $payment_details)
    {
        $this->balance += $amount;
        $this->save();

        $walletRecord = new Wallet();
        $walletRecord->amount = $amount;
        $walletRecord->payment_method = $payment_method;
        $walletRecord->payment_details = $payment_details;
        $walletRecord->approval = 1;
        $this->wallets()->save($walletRecord);
        return $walletRecord->id;
    }

    /**
     * Get cars owned by this user
     */
    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    /**
     * Get the user's insurance deposits.
     */
    public function insuranceDeposit()
    {
        return $this->hasMany(UserInsuranceDeposit::class);
    }
    /**
     *
     * Get paid insurance deposit
     */
    public function paidInsuranceDeposit()
    {
        return $this->insuranceDeposit()
        ->where('status', 'paid')
        ->first();
    }

    /**
     * Get all bids placed by this user.
     */
    public function auctionBids()
    {
        return $this->hasMany(Bid::class, 'bidder_id');
    }

    /**
     * Get all offers made by this user as a buyer.
     */
    public function auctionOffers()
    {
        return $this->hasMany(AuctionOffer::class, 'buyer_id');
    }

    /**
     * Get all auction items where this user is the seller.
     */
    public function sellerAuctionItems()
    {
        return $this->hasMany(AuctionItem::class, 'seller_id');
    }

    /**
     * Get all auction invoices for this user.
     */
    public function auctionInvoices()
    {
        return $this->hasMany(AuctionInvoice::class);
    }


    /**
     * Check if the user has an active insurance deposit.
     */
    public function hasInsuranceDeposit(): bool
    {
        return $this->insuranceDeposit()
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Check if the user can place bids.
     */
    public function canBid(): bool
    {
        if(get_setting('allow_all_user_to_bid') == 1) {
            return true;
        }
        return  $this->hasInsuranceDeposit();
    }

    /**
     * Get the sum of the user's active insurance deposit.
     */
    public function getActiveInsuranceDepositAmount(): float
    {
        $deposit = $this->insuranceDeposit()
            ->where('status', 'paid')
            ->sum('amount');
        return $deposit;
    }

    /**
     * Get all unpaid auction invoices for this user.
     */
    public function getUnpaidAuctionInvoices()
    {
        return $this->auctionInvoices()
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Check if user has unpaid auction invoices
     */

    public function hasUnpaidAuctionInvoices()
    {
        return $this->getUnpaidAuctionInvoices()->count() > 0;
    }

    /**
     * Check if the user can refund their insurance deposit.
     */
    public function canRefundDeposit(): bool
    {
        if (!$this->hasInsuranceDeposit()) {
            return false;
        }

        // Cannot refund if there are unpaid invoices
        return $this->getUnpaidAuctionInvoices()->isEmpty();
    }

}
