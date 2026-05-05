<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarCustomFieldOptionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_custom_field_option_id',
        'label',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the car custom field option that owns the translation
     */
    public function carCustomFieldOption(): BelongsTo
    {
        return $this->belongsTo(CarCustomFieldOption::class);
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
     * Scope a query to filter by car custom field option
     */
    public function scopeByCarCustomFieldOption($query, $carCustomFieldOptionId)
    {
        return $query->where('car_custom_field_option_id', $carCustomFieldOptionId);
    }

    // Helper methods

    /**
     * Check if translation is complete
     */
    public function isComplete()
    {
        return !empty($this->label);
    }

    /**
     * Get missing fields
     */
    public function getMissingFields()
    {
        $missing = [];

        if (empty($this->label)) {
            $missing[] = 'label';
        }

        return $missing;
    }
}
