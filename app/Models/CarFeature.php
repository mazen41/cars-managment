<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class CarFeature extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'name',
        'image',
        'section_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the cars that have this feature
     */
    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_has_feature', 'feature_id', 'car_id');
    }

    /**
     * Get the translations for the feature
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarFeatureTranslation::class, 'car_feature_id');
    }

    /**
     * Get the section this feature belongs to
     */
    public function section()
    {
        return $this->belongsTo(CarFeatureSection::class, 'section_id');
    }

    // Scopes

    /**
     * Scope a query to only include active features
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('name');
    }

    /**
     * Scope a query to order features by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    // Accessors & Mutators

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? public_storage_url($this->image) : null;
    }

    /**
     * Get the feature name with fallback to translation
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
     * Check if feature has cars
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
     * Check if feature is used by a specific car
     */
    public function isUsedByCar($carId)
    {
        return $this->cars()->where('car_id', $carId)->exists();
    }

    /**
     * Get popular features (most used)
     */
    public static function getPopular($limit = 10)
    {
        return self::withCount('cars')
            ->orderBy('cars_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
