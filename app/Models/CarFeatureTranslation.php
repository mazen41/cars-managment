<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarFeatureTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_feature_id',
        'name',
    ];

    public $timestamps = false;

    // Relationships

    /**
     * Get the car feature that owns the translation
     */
    public function carFeature(): BelongsTo
    {
        return $this->belongsTo(CarFeature::class);
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
     * Scope a query to filter by car feature
     */
    public function scopeByCarFeature($query, $carFeatureId)
    {
        return $query->where('car_feature_id', $carFeatureId);
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
