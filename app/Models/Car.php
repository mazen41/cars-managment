<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        "vin",
        "description",
        "model_id",
        "brand_id",
        "category_id",
        "color_id",
        "condition",
        "milage",
        "manufacture_year",
        "photos",
        "main_photo",
        "transmission",
        "fuel_type",
        "location",
        "price",
        "country_id",
        "state_id",
        "city_id",
        "user_id",
        "moderation_status",
        "car_status",
        "todays_deal",
        "featured",
        "view_count"
    ];

    protected $casts = [
        "milage" => "decimal:2",
        "price" => "decimal:2",
        "manufacture_year" => "integer",
        "main_photo" => "integer",
        "required" => "boolean",
        "moderation_status" => CarModerationStatusEnum::class,
        "car_status" => CarStatusEnum::class,
    ];

    protected $dates = ["created_at", "updated_at"];

    // Legacy constants for backward compatibility - deprecated
    const STATUS_PUBLISHED = 'published';
    const STATUS_DRAFT = 'draft';
    const STATUS_RESERVED = 'reserved';
    const STATUS_SOLD = 'sold';

    // Relationships

    /**
     * Get the brand that owns the car
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, "brand_id");
    }

    /**
     * Alias for brand relationship (for consistency with naming conventions)
     */
    public function carBrand(): BelongsTo
    {
        return $this->brand();
    }

    /**
     * Get the model that owns the car
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, "model_id");
    }

    /**
     * Alias for model relationship (for consistency with naming conventions)
     */
    public function carModel(): BelongsTo
    {
        return $this->model();
    }

    /**
     * Get the category that owns the car
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class, "category_id");
    }

    /**
     * Alias for category relationship (for consistency with naming conventions)
     */
    public function carCategory(): BelongsTo
    {
        return $this->category();
    }

    /**
     * Get the user that owns the car
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the features for the car
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            CarFeature::class,
            "car_has_feature",
            "car_id",
            "feature_id",
        );
    }

    /**
     * Get the custom field values for the car
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CarCustomFieldValue::class);
    }

    /**
     * Get the car country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    /**
     * Get the car color
     */

    public function color(): BelongsTo
    {
        return $this->belongsTo(CarColor::class, "color_id", "id");
    }

    /**
     * Alias for color relationship (for consistency with naming conventions)
     */
    public function carColor(): BelongsTo
    {
        return $this->color();
    }

    /**
     * Get the car state
     */

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the car city
     */

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the translations for the car
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarTranslation::class);
    }

    /**
     * Get all auction items for this car
     */
    public function auctionItems(): HasMany
    {
        return $this->hasMany(AuctionItem::class);
    }

    /**
     * Get all auction listing requests for this car
     */
    public function auctionListingRequests(): HasMany
    {
        return $this->hasMany(AuctionListingRequest::class);
    }

    /**
     * Get all reservations for this car
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(CarReservation::class);
    }

    /**
     * Get active reservations for this car
     */
    public function activeReservations(): HasMany
    {
        return $this->reservations()->active();
    }

    /**
     * Get the current active reservation for this car
     */
    public function currentReservation()
    {
        return $this->reservations()->active()->latest()->first();
    }

    /**
     * Get all inspections for this car
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(CarInspection::class);
    }

    /**
     * Get completed inspections for this car
     */
    public function completedInspections(): HasMany
    {
        return $this->inspections()->where("status", "completed");
    }

    /**
     * Get pending inspections for this car
     */
    public function pendingInspections(): HasMany
    {
        return $this->inspections()->where("status", "pending");
    }

    /**
     * Get the latest inspection for this car
     */
    public function latestInspection()
    {
        return $this->inspections()->latest()->first();
    }

     /**
     * Get the latest completed inspection for this car
     */
    public function getLatestCompletedInspectionAttribute()
    {
        return $this->inspections()->where("status", CarInspection::STATUS_COMPLETED)->latest()->first();
    }

    /**
     * Get wishlisted cars
     */

    public function wishlists() : MorphMany
    {
        return $this->morphMany(Wishlist::class, 'wishlistable');
    }

    // Scopes

    /**
     * Scope a query to only include published cars
     */
    public function scopePublished($query)
    {
        return $query->where("moderation_status", CarModerationStatusEnum::PUBLISHED);
    }

    /**
     * Scope a query to only include available cars
     */
    public function scopeAvailable($query)
    {
        return $query->where("car_status", CarStatusEnum::AVAILABLE);
    }

    /**
     * Scope a query to only include reserved cars
     */
    public function scopeReserved($query)
    {
        return $query->where("car_status", CarStatusEnum::RESERVED);
    }

    /**
     * Scope a query to only include sold cars
     */
    public function scopeSold($query)
    {
        return $query->where("car_status", CarStatusEnum::SOLD);
    }

    /**
     * Scope a query to only include cars in auction
     */
    public function scopeInAuction($query)
    {
        return $query->where("car_status", CarStatusEnum::IN_AUCTION);
    }

    /**
     * Scope a query to only include cars by condition
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where("condition", $condition);
    }

    /**
     * Scope a query to only include cars by brand
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where("brand_id", $brandId);
    }

    /**
     * Scope a query to only include cars by model
     */
    public function scopeByModel($query, $modelId)
    {
        return $query->where("model_id", $modelId);
    }

    /**
     * Scope a query to only include cars by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where("category_id", $categoryId);
    }

    /**
     * Scope a query to filter cars by price range
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween("price", [$minPrice, $maxPrice]);
    }

    /**
     * Scope a query to filter cars by year range
     */
    public function scopeByYearRange($query, $minYear, $maxYear)
    {
        return $query->whereBetween("manufacture_year", [$minYear, $maxYear]);
    }

    // Accessors & Mutators
    /**
     * Get car name from brand , model, year, color
     */
    public function getCarNameAttribute()
    {
        return "{$this->brand->name} {$this->model->name} ({$this->manufacture_year}) - {$this->color?->getTranslation('name')}";
    }

    /**
     * Get the main photo URL
     */
    public function getMainPhotoUrlAttribute()
    {
        if ($this->main_photo !== null) {
            return uploaded_asset($this->main_photo);
        }
        return null;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return $this->price ? single_price($this->price) : null;
    }

    /**
     * Get formatted milage
     */
    public function getFormattedMilageAttribute()
    {
        return $this->milage ? number_format($this->milage, 0).' km' : null;
    }

    // Helper methods

    /**
     * Check if car has a specific feature
     */
    public function hasFeature($featureId)
    {
        return $this->features()->where("feature_id", $featureId)->exists();
    }

    /**
     * Check if car has auction listing request approved
     */
    public function hasApprovedAuctionListingRequest(): bool
    {
        return $this->auctionListingRequests()
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get custom field value
     */
    public function getCustomFieldValue($fieldId)
    {
        return $this->customFieldValues()
            ->where("custom_field_id", $fieldId)
            ->first()?->value;
    }

    /**
     * Check if car's custom field  has value
     */
    public function hasValueOfCustomField($fieldId): bool
    {
        return $this->customFieldValues()
        ->where('custom_field_id', $fieldId)
        ->exists();
    }
    /**
     * Wether the car can be deleted or not
     * @return bool
     */
    public function canBeDeleted()
    {
        return $this->inspections()->exists() ||
               $this->reservations()->exists() ||
               $this->isInAuction() ||
               $this->isSold()
            ? false
            : true;
    }

    /**
     * Get translated attribute
     */
    public function getTranslatedAttribute($attribute, $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        $translation = $this->translations()
            ->where("lang_code", $locale)
            ->first();

        return $translation ? $translation->$attribute : $this->$attribute;
    }
    /**
     * Convert photos atrribute to array
     */
    public function getPhotosArrayAttribute(){
        return explode(',', $this->photos);
    }

    /**
     * Check if car is currently reserved
     */
    public function isReserved()
    {
        return $this->car_status->getValue() === CarStatusEnum::RESERVED ||
            $this->activeReservations()->exists();
    }

    /**
     * Check if car is sold
     */
    public function isSold()
    {
        return $this->car_status->getValue() === CarStatusEnum::SOLD;
    }

    /**
     * Check if car is published
     */
    public function isPublished()
    {
        return $this->moderation_status->getValue() === CarModerationStatusEnum::PUBLISHED;
    }

     /**
     * Check if car is in Auction
     */

    public function isInAuction() {
        return $this->status == CarStatusEnum::IN_AUCTION;
    }

    /**
     * Check if car can be reserved
     */
    public function canBeReserved()
    {
        return $this->isPublished() &&
            !$this->isReserved() &&
            !$this->isSold() &&
            !$this->isInAuction();
    }


    /**
     * Check if car can be listed in auction
     */
    public function canBeInAuction() {
        $this->isPublished() &&
            !$this->isReserved() &&
            !$this->isSold() &&
            !$this->isInAuction();
    }
    /**
     * Get reservation status text
     */
    public function getReservationStatusAttribute()
    {
        if ($this->isSold()) {
            return "Sold";
        }

        if ($this->isReserved()) {
            $reservation = $this->currentReservation();
            return $reservation ? ucfirst($reservation->status) : "Reserved";
        }

        return "Available";
    }

    /**
     * Get reservation status badge class
     */
    public function getReservationStatusBadgeClassAttribute()
    {
        if ($this->isSold()) {
            return "badge-secondary";
        }

        if ($this->isReserved()) {
            $reservation = $this->currentReservation();
            if ($reservation) {
                return match ($reservation->status) {
                    "pending" => "badge-warning",
                    "confirmed" => "badge-info",
                    default => "badge-info",
                };
            }
            return "badge-info";
        }

        return "badge-success";
    }

    /**
     * Create a reservation for this car
     */
    public function createReservation(
        $userId,
        $notes = null,
    ) {
        if (!$this->canBeReserved()) {
            throw new \Exception("This car cannot be reserved.");
        }

        $reservation = $this->reservations()->create([
            "user_id" => $userId,
            "notes" => $notes,
            "reserved_at" => now(),
        ]);


        return $reservation;
    }

    public function getModerationStatusHtmlBadgeAttribute(){
        return match($this->moderation_status->getValue()){
            CarModerationStatusEnum::PUBLISHED => '<span class="badge badge-inline badge-success">'.translate('Published').'</span>',
            CarModerationStatusEnum::PENDING => '<span class="badge badge-inline badge-warning">'.translate('Pending').'</span>',
            CarModerationStatusEnum::REJECTED => '<span class="badge badge-inline badge-danger">'.translate('Rejected').'</span>',
            default => '<span class="badge badge-inline badge-secondary">'.translate('Unknown').'</span>',
        };
    }

    public function getCarStatusHtmlBadgeAttribute(){
        return match($this->car_status->getValue()){
            CarStatusEnum::AVAILABLE => '<span class="badge badge-inline badge-success">'.translate('Available').'</span>',
            CarStatusEnum::RESERVED => '<span class="badge badge-inline badge-info">'.translate('Reserved').'</span>',
            CarStatusEnum::IN_AUCTION => '<span class="badge badge-inline badge-primary">'.translate('In Auction').'</span>',
            CarStatusEnum::SOLD => '<span class="badge badge-inline badge-danger">'.translate('Sold').'</span>',
            default => '<span class="badge badge-inline badge-secondary">'.translate('Unknown').'</span>',
        };
    }

    // Legacy method for backward compatibility - deprecated
    public function getStatusHtmlBadgeAttribute(){
        // Combine both statuses for legacy support
        if (!$this->isPublished()) {
            return $this->getModerationStatusHtmlBadgeAttribute();
        }
        return $this->getCarStatusHtmlBadgeAttribute();
    }

    /**
     * Check if car has a valid inspection
     */
    public function hasValidInspection(): bool
    {
        $latestInspection = $this->latestInspection();

        if (!$latestInspection) {
            return false;
        }

        // Check if inspection is completed and not expired (valid for 6 months)
        return $latestInspection->status === 'completed' &&
               $latestInspection->created_at->isAfter(now()->subMonths(6));
    }

    /**
     *  Get car Insepction count
     */

    public function getInspectionsCountAttribute() : int
    {
        return $this->inspections()->count();
    }

    /**
     * Get Car Reservations count
     */
    public function getReservationsCountAttribute() : int
    {
        return $this->reservations()->count();
    }

    /**
     * Get Car favorites count
     */

    public function getFavoritesCountAttribute(): int{
        return $this->wishlists()->count();
    }

    /**
     * Get car badges based on reservation and inspection status
      * @return array
     */
    public function getBadgesAttribute(): array
    {

        // for future implementations
        return [];
    }

    /**
     * Get real-time view count from Redis
     * Falls back to database value if Redis is unavailable
     */
    public function getRealTimeViewCountAttribute(): int
    {
        try {
            $service = app(\App\Services\CarViewTrackingService::class);
            $redisCount = $service->getViewCount($this->id);

            // Return Redis count if it's greater than DB count
            return max($redisCount, $this->view_count);
        } catch (\Exception $e) {
            // Fallback to database value if Redis fails
            return $this->view_count;
        }
    }

    /**
     * Get view statistics for this car
     */
    public function getViewStatistics(): array
    {
        try {
            $service = app(\App\Services\CarViewTrackingService::class);
            return $service->getViewStatistics($this->id);
        } catch (\Exception $e) {
            return [
                'total_views' => $this->view_count,
                'today_views' => 0,
                'yesterday_views' => 0,
                'last_7_days' => 0,
            ];
        }
    }
}
