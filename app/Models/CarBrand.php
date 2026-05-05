<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarBrand extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'name',
        'logo',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the models for the brand
     */
    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class, 'brand_id');
    }

    /**
     * Get the cars for the brand
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'brand_id');
    }

    /**
     * Get the translations for the brand
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarBrandTranslation::class, 'car_brand_id' ,'id');
    }

    /**
     * Get the CarCategories for the brand
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CarCategory::class, 'car_brand_car_category', 'car_brand_id', 'car_category_id');
    }


    // Scopes

    /**
     * Scope a query to only include active brands
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    // Accessors & Mutators

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo ? uploaded_asset($this->logo) : null;
    }

    /**
     * Get the brand name with fallback to translation
     */
    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->name : $this->name;
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
     * Check if brand has models
     */
    public function hasModels()
    {
        return $this->models()->exists();
    }

    /**
     * Check if brand has cars
     */
    public function hasCars()
    {
        return $this->cars()->exists();
    }

    /**
     * Get models count
     */
    public function getModelsCountAttribute()
    {
        return $this->models()->count();
    }

    /**
     * Get cars count
     */
    public function getCarsCountAttribute()
    {
        return $this->cars()->count();
    }

     /**
     * Check if brand can be deleted
     */
    public function canBeDeleted()
    {
        return !$this->hasCars();
    }
}
