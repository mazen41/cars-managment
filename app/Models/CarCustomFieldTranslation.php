<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarCustomFieldTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'car_custom_field_id',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the car custom field that owns the translation
     */
    public function carCustomField(): BelongsTo
    {
        return $this->belongsTo(CarCustomField::class);
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
     * Scope a query to filter by car custom field
     */
    public function scopeByCarCustomField($query, $carCustomFieldId)
    {
        return $query->where('car_custom_field_id', $carCustomFieldId);
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
