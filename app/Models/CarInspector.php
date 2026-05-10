<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarInspector extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "user_id",
        "shop_name",
        "address",
        "latitude",
        "longitude",
        "country_id",
        "state_id",
        "city_id",
        "phone",
        "email",
        "image",
        "banner_image",
        "admin_to_pay",
        "is_active",
        "description",
        "working_hours",
        "services_offered",
        "certification_number",
        "experience_years",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "admin_to_pay" => "decimal:2",
        "latitude" => "decimal:8",
        "longitude" => "decimal:8",
        "working_hours" => "json",
        "services_offered" => "json",
        "experience_years" => "integer",
    ];

    protected $dates = ["deleted_at"];

    /**
     * Get the user that owns the car inspector.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country that the car inspector belongs to.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state that the car inspector belongs to.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city that the car inspector belongs to.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the payment history for the car inspector.
     */
    public function paymentHistory()
    {
        return $this->hasMany(CarInspectorPaymentHistory::class)->orderBy(
            "created_at",
            "desc",
        );
    }

    /**
     * Get the car inspections assigned to this inspector.
     */
    public function inspections()
    {
        return $this->hasMany(CarInspection::class, "inspector_id");
    }

    public function manualExaminationPermission(): HasOne
    {
        return $this->hasOne(ManualExaminationPermission::class, 'center_id');
    }

    public function manualExaminationInspectionTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            CarInspectionType::class,
            'manual_examination_center_inspection_types',
            'center_id',
            'inspection_type_id'
        );
    }

    public function canUseManualExaminations(): bool
    {
        // Default is "allowed" unless explicitly disabled.
        // This preserves backwards compatibility for centers created before the permission system existed.
        return $this->manualExaminationPermission?->can_manual_examination ?? true;
    }

    /**
     * Summary of commissions
     * @return MorphMany<Commission, CarInspector>
     */
    public function commissions(): MorphMany
    {
        return $this->morphMany(Commission::class, "ownable");
    }

    /**
     * Get the pending inspections for this inspector.
     */
    public function pendingInspections()
    {
        return $this->inspections()->pending();
    }

    /**
     * Get the completed inspections for this inspector.
     */
    public function completedInspections()
    {
        return $this->inspections()->completed();
    }

    /**
     * Scope a query to only include active inspectors.
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Scope a query to only include inactive inspectors.
     */
    public function scopeInactive($query)
    {
        return $query->where("is_active", false);
    }

    /**
     * Scope a query to search inspectors by name, shop name, or address.
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->whereHas("user", function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%");
            })
            ->orWhere("shop_name", "like", "%{$search}%")
            ->orWhere("address", "like", "%{$search}%");
    }

    /**
     * Get the total amount owed to the inspector.
     */
    public function getTotalOwedAttribute()
    {
        return $this->admin_to_pay ?? 0;
    }

    /**
     * Get the total amount paid to the inspector.
     */
    public function getTotalPaidAttribute()
    {
        return $this->paymentHistory()
            ->where("type", "payment")
            ->where("status", "completed")
            ->sum("amount");
    }

    /**
     * Get the inspector's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->user->name ?? "N/A";
    }

   /**
 * Get the inspector's profile image URL.
 */
public function getImageUrlAttribute()
{
    return file_asset_url($this->image, true) ?? static_asset("assets/img/avatar-place.png");
}

/**
 * Get the inspector's banner image URL.
 */
public function getBannerImageUrlAttribute()
{
    return file_asset_url($this->banner_image, true) ?? static_asset("assets/img/placeholder.jpg");
}

    /**
     * Get the inspector's status display.
     */
    public function getStatusDisplayAttribute()
    {
        return $this->is_active ? "Active" : "Inactive";
    }

    /**
     * Get the inspector's rating based on completed inspections.
     */
    public function getRatingAttribute()
    {
        $completedInspections = $this->completedInspections();
        $totalInspections = $completedInspections->count();

        if ($totalInspections === 0) {
            return 0;
        }

        // This is a simple rating calculation - you can modify based on your rating system
        return round($totalInspections > 0 ? 4.5 : 0, 1);
    }

    /**
     * Get the inspector's statistics.
     */
    public function getStatsAttribute()
    {
        return [
            "total_inspections" => $this->inspections()->count(),
            "pending_inspections" => $this->pendingInspections()->count(),
            "completed_inspections" => $this->completedInspections()->count(),
            "total_owed" => $this->total_owed,
            "total_paid" => $this->total_paid,
            "rating" => $this->rating,
        ];
    }

    /**
     * Add payment to inspector's balance.
     */
    public function addPayment(
        $amount,
        $description = null,
        $paymentMethod = null,
        $paymentDetails = null,
    ) {
        $this->increment("admin_to_pay", $amount);

        return $this->paymentHistory()->create([
            "type" => "earning",
            "amount" => $amount,
            "description" => $description ?? "Inspection payment",
            "payment_method" => $paymentMethod,
            "payment_details" => $paymentDetails,
            "status" => "completed",
            "processed_by" => auth("web")->id(),
        ]);
    }

    /**
     * Process payment to inspector.
     */
    public function processPayment(
        $amount,
        $paymentMethod,
        $paymentDetails = null,
        $description = null,
    ) {
        if ($amount > $this->admin_to_pay) {
            throw new \Exception(
                "Payment amount cannot exceed the amount owed.",
            );
        }

        $this->decrement("admin_to_pay", $amount);

        return $this->paymentHistory()->create([
            "type" => "payment",
            "amount" => $amount,
            "description" => $description ?? "Admin payment to inspector",
            "payment_method" => $paymentMethod,
            "payment_details" => $paymentDetails,
            "status" => "completed",
            "processed_by" => auth("web")->id(),
        ]);
    }

    /**
     * Increment the amount owed to the inspector.
     */

    public function incrementOwedAmount($amount)
    {
        $this->increment("admin_to_pay", $amount);
    }
}
