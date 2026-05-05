<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarModelTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_model_id',
        'name',
    ];

    public $timestamps = false;

    // Relationships

    /**
     * Get the car model that owns the translation
     */
    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
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
     * Scope a query to filter by car model
     */
    public function scopeByCarModel($query, $carModelId)
    {
        return $query->where('car_model_id', $carModelId);
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
