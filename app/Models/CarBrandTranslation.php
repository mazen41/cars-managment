<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarBrandTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_brand_id',
        'name',
    ];

    public $timestamps = false;

    // Relationships

    /**
     * Get the car brand that owns the translation
     */
    public function carBrand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class);
    }

    // Scopes

    /**
     * Scope a query to filter by language
     */
    public function scopeByLanguage($query, $lang)
    {
        return $query->where('lang', $lang);
    }

    /**
     * Scope a query to filter by car brand
     */
    public function scopeByCarBrand($query, $carBrandId)
    {
        return $query->where('car_brand_id', $carBrandId);
    }

    // Helper methods

    /**
     * Check if translation is complete
     */
    public function isComplete()
    {
        return !empty($this->name);
    }

    /**
     * Get missing fields
     */
    public function getMissingFields()
    {
        $missing = [];

        if (empty($this->name)) {
            $missing[] = 'name';
        }

        return $missing;
    }
}
