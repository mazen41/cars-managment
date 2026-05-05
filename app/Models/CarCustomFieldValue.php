<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CarCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'custom_field_id',
        'value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the car that owns the value
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the custom field that owns the value
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CarCustomField::class, 'custom_field_id');
    }

    // Scopes

    /**
     * Scope a query to filter by car
     */
    public function scopeByCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    /**
     * Scope a query to filter by custom field
     */
    public function scopeByCustomField($query, $customFieldId)
    {
        return $query->where('custom_field_id', $customFieldId);
    }

    /**
     * Scope a query to filter by value
     */
    public function scopeByValue($query, $value)
    {
        return $query->where('value', $value);
    }

    // Accessors & Mutators

    /**
     * Get the formatted value based on field type
     */
    public function getFormattedValueAttribute()
    {
        if (!$this->customField) {
            return $this->value;
        }

        switch ($this->customField->type) {
            case CarCustomField::TYPE_NUMBER:
                return is_numeric($this->value) ? number_format($this->value) : $this->value;
            case CarCustomField::TYPE_DATE:
                return $this->value ? date('Y-m-d', strtotime($this->value)) : null;
            case CarCustomField::TYPE_DATETIME:
                return $this->value ? date('Y-m-d H:i:s', strtotime($this->value)) : null;
            case CarCustomField::TYPE_CHECKBOX:
                return is_string($this->value) ? json_decode($this->value, true) : $this->value;
            case CarCustomField::TYPE_URL:
                return $this->value ? '<a href="' . $this->value . '" target="_blank">' . $this->value . '</a>' : null;
            case CarCustomField::TYPE_EMAIL:
                return $this->value ? '<a href="mailto:' . $this->value . '">' . $this->value . '</a>' : null;
            default:
                return $this->value;
        }
    }

    /**
     * Get the display value with option label if applicable
     */
    public function getDisplayValueAttribute()
    {
        if (!$this->customField) {
            return $this->value;
        }

        if ($this->customField->hasOptions()) {
            if ($this->customField->type === CarCustomField::TYPE_CHECKBOX) {
                $values = is_string($this->value) ? json_decode($this->value, true) : (array)$this->value;
                $labels = [];

                foreach ($values as $value) {
                    $option = $this->customField->options()->where('value', $value)->first();
                    $labels[] = $option ? $option->display_label : $value;
                }

                return implode(', ', $labels);
            } else {
                $option = $this->customField->options()->where('value', $this->value)->first();
                return $option ? $option->display_label : $this->value;
            }
        }

        return $this->formatted_value;
    }

    // Helper methods

    /**
     * Set value for checkbox type (array to JSON)
     */
    public function setValueForCheckbox($values)
    {
        if ($this->customField->type === CarCustomField::TYPE_CHECKBOX) {
            $this->value = is_array($values) ? json_encode($values) : $values;
        } else {
            $this->value = $values;
        }
    }

    /**
     * Get value for checkbox type (JSON to array)
     */
    public function getValueForCheckbox()
    {
        if ($this->customField->type === CarCustomField::TYPE_CHECKBOX) {
            return is_string($this->value) ? json_decode($this->value, true) : (array)$this->value;
        }

        return $this->value;
    }

    /**
     * Check if value is valid for the field type
     */
    public function isValid()
    {
        return $this->customField->validateValue($this->value);
    }

    /**
     * Get the option object if value corresponds to an option
     */
    public function getOption()
    {
        if ($this->customField->hasOptions()) {
            return $this->customField->options()->where('value', $this->value)->first();
        }

        return null;
    }

    /**
     * Check if value is empty
     */
    public function isEmpty()
    {
        if ($this->customField->type === CarCustomField::TYPE_CHECKBOX) {
            $values = $this->getValueForCheckbox();
            return empty($values);
        }

        return empty($this->value);
    }

    /**
     * Get searchable value (for search functionality)
     */
    public function getSearchableValue()
    {
        if ($this->customField->hasOptions()) {
            return $this->display_value;
        }

        return $this->value;
    }
}
