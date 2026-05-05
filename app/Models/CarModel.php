<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTranslation;

class CarModel extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'name',
        'brand_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the brand that owns the model
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, 'brand_id');
    }

    /**
     * Get the cars for the model
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'model_id');
    }

    /**
     * Get the translations for the model
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarModelTranslation::class, 'car_model_id');
    }

    // Scopes

    /**
     * Scope a query to only include published models
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include active models
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending models
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter models by brand
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    // Accessors & Mutators

    /**
     * Get the model name with fallback to translation
     */
    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->name : $this->name;
    }

    /**
     * Get the full model name with brand
     */
    public function getFullNameAttribute()
    {
        return $this->brand->name . ' ' . $this->name;
    }

    // Helper methods

    /**
     * Get translated attribute
     */
    public function getTranslatedAttribute($attribute, $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->$attribute : $this->$attribute;
    }

    /**
     * Check if model has cars
     */
    public function hasCars()
    {
        return $this->cars()->exists();
    }

    /**
     * Get cars count
     */
    public function getCarsCountAttribute()
    {
        return $this->cars()->count();
    }

    /**
     * Check if model belongs to a specific brand
     */
    public function belongsToBrand($brandId)
    {
        return $this->brand_id == $brandId;
    }

      /**
     * Check if model can be deleted
     */
    public function canBeDeleted()
    {
        return !$this->hasCars();
    }
}
