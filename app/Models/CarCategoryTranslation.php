<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_category_id',
        'name',
    ];

    public $timestamps = false;

    // Relationships

    /**
     * Get the car category that owns the translation
     */
    public function carCategory(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class);
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
     * Scope a query to filter by car category
     */
    public function scopeByCarCategory($query, $carCategoryId)
    {
        return $query->where('car_category_id', $carCategoryId);
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
