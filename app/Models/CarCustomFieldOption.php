<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class CarCustomFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_id',
        'label',
        'value',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the custom field that owns the option
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CarCustomField::class, 'custom_field_id');
    }

    /**
     * Get the translations for the option
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarCustomFieldOptionTranslation::class, 'car_custom_field_option_id');
    }

    // Scopes

    /**
     * Scope a query to order options by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope a query to filter by custom field
     */
    public function scopeByCustomField($query, $customFieldId)
    {
        return $query->where('custom_field_id', $customFieldId);
    }

    // Accessors & Mutators

    /**
     * Get the option label with fallback to translation
     */
    public function getDisplayLabelAttribute()
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->label : $this->label;
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
     * Check if option is selected for a specific car
     */
    public function isSelectedForCar($carId)
    {
        return CarCustomFieldValue::where('car_id', $carId)
            ->where('custom_field_id', $this->custom_field_id)
            ->where('value', $this->value)
            ->exists();
    }

    /**
     * Get usage count (how many cars use this option)
     */
    public function getUsageCountAttribute()
    {
        return CarCustomFieldValue::where('custom_field_id', $this->custom_field_id)
            ->where('value', $this->value)
            ->count();
    }

    /**
     * Check if option can be deleted
     */
    public function canBeDeleted()
    {
        return $this->usage_count === 0;
    }
}
