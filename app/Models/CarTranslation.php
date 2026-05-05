<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang_code',
        'car_id',
        'name',
        'description',
    ];

    public $timestamps = false;

    // Relationships

    /**
     * Get the car that owns the translation
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    // Scopes

    /**
     * Scope a query to filter by language
     */
    public function scopeByLanguage($query, $langCode)
    {
        return $query->where('lang_code', $langCode);
    }

    /**
     * Scope a query to filter by car
     */
    public function scopeByCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    // Helper methods

    /**
     * Check if translation is complete
     */
    public function isComplete()
    {
        return !empty($this->name) && !empty($this->description);
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

        if (empty($this->description)) {
            $missing[] = 'description';
        }

        return $missing;
    }
}
