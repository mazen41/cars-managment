<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $guarded = ['choice_attributes'];

    protected $with = ['product_translations', 'taxes', 'thumbnail'];

    protected $casts = ['specifications' => 'array'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $product_translations = $this->product_translations->where('lang', $lang)->first();
        return $product_translations != null ? $product_translations->$field : $this->$field;
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function main_category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function frequently_bought_products()
    {
        return $this->hasMany(FrequentlyBoughtProduct::class);
    }

    public function product_categories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOneThrough(
            Shop::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
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

    public function product_queries()
    {
        return $this->hasMany(ProductQuery::class);
    }

    public function wishlists()
    {
        return $this->morphMany(Wishlist::class, 'wishlistable');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function taxes()
    {
        return $this->hasMany(ProductTax::class);
    }

    public function flash_deal_products()
    {
        return $this->hasMany(FlashDealProduct::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function thumbnail()
    {
        return $this->belongsTo(Upload::class, 'thumbnail_img');
    }

    public function scopePhysical($query)
    {
        return $query->where('digital', 0);
    }

    public function scopeDigital($query)
    {
        return $query->where('digital', 1);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeIsApprovedPublished($query)
    {
        return $query->where('approved', '1')->where('published', 1);
    }

    public function last_viewed_products()
    {
        return $this->hasMany(LastViewedProduct::class);
    }

    public function conversations()
    {
        return $this->morphMany(Conversation::class, 'conversable');
    }

     /**
     * Scope a query to filter products by price range
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween("unit_price", [$minPrice, $maxPrice]);
    }

    /**
     * Filter products by attributes
     */

    public function scopeByAttributes($query, $attributes)
{
    foreach ($attributes as $attribute_id => $values) {
        foreach ((array)$values as $value) {
            $query->whereJsonContains('choice_options', [
                'attribute_id' => (string) $attribute_id,
                'values' => [$value],
            ]);
        }
    }
    return $query;
}

public function getSubcategoryIdAttribute()
{
    $subcategory = $this->categories()->where('id', '!=', $this->category_id)->first();
    return $subcategory ? $subcategory->id : null;

}
public function subcategories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
            ->where('categories.id', '!=', $this->category_id);
    }
    public function getSubcategoryAttribute()
{
    return $this->subcategories->first();
}
}
