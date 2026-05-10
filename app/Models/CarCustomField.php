<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class CarCustomField extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'name',
        'type',
        'order',
        'required',
        'icon',
    ];

    protected $casts = [
        'order' => 'integer',
        'required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Field types constants
    const TYPE_TEXT = 'text';
    const TYPE_NUMBER = 'number';
    const TYPE_EMAIL = 'email';
    const TYPE_URL = 'url';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'datetime';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';

    // Relationships

    /**
     * Get the options for the custom field
     */
    public function options(): HasMany
    {
        return $this->hasMany(CarCustomFieldOption::class, 'custom_field_id');
    }

    /**
     * Get the values for the custom field
     */
    public function values(): HasMany
    {
        return $this->hasMany(CarCustomFieldValue::class, 'custom_field_id');
    }

    /**
     * Get the translations for the custom field
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarCustomFieldTranslation::class, 'car_custom_field_id');
    }

    // Scopes

    /**
     * Scope a query to only include required fields
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    /**
     * Scope a query to only include optional fields
     */
    public function scopeOptional($query)
    {
        return $query->where('required', false);
    }

    /**
     * Scope a query to order fields by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope a query to filter by field type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators

    /**
     * Get the icon URL
     */
    public function getIconUrlAttribute()
    {
        return $this->icon ? public_storage_url($this->icon) : null;
    }

    /**
     * Get the field name with fallback to translation
     */
    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->name : $this->name;
    }

    /**
     * Get available field types
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_URL => 'URL',
            self::TYPE_TEXTAREA => 'Textarea',
            self::TYPE_SELECT => 'Select',
            self::TYPE_RADIO => 'Radio',
            self::TYPE_CHECKBOX => 'Checkbox',
            self::TYPE_DATE => 'Date',
            self::TYPE_TIME => 'Time',
            self::TYPE_DATETIME => 'DateTime',
            self::TYPE_FILE => 'File',
            self::TYPE_IMAGE => 'Image',
        ];
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
     * Check if field has options
     */
    public function hasOptions()
    {
        return in_array($this->type, [self::TYPE_SELECT, self::TYPE_RADIO, self::TYPE_CHECKBOX]);
    }

    /**
     * Check if field is required
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Check if field type supports multiple values
     */
    public function supportsMultipleValues()
    {
        return $this->type === self::TYPE_CHECKBOX;
    }

    /**
     * Get field value for a specific car
     */
    public function getValueForCar($carId)
    {
        return $this->values()->where('car_id', $carId)->first()?->value;
    }

    /**
     * Get all values for the field
     */
    public function getAllValues()
    {
        return $this->values()->pluck('value', 'car_id');
    }

    /**
     * Get unique values for the field
     */
    public function getUniqueValues()
    {
        return $this->values()->distinct('value')->pluck('value');
    }

    /**
     * Get values count
     */
    public function getValuesCountAttribute()
    {
        return $this->values()->count();
    }

    /**
     * Get options count
     */
    public function getOptionsCountAttribute()
    {
        return $this->options()->count();
    }

    /**
     * Validate field value
     */
    public function validateValue($value)
    {
        if ($this->required && empty($value)) {
            return false;
        }

        switch ($this->type) {
            case self::TYPE_EMAIL:
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case self::TYPE_URL:
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case self::TYPE_NUMBER:
                return is_numeric($value);
            case self::TYPE_DATE:
                return strtotime($value) !== false;
            case self::TYPE_SELECT:
            case self::TYPE_RADIO:
                return $this->options()->where('value', $value)->exists();
            case self::TYPE_CHECKBOX:
                if (is_array($value)) {
                    foreach ($value as $val) {
                        if (!$this->options()->where('value', $val)->exists()) {
                            return false;
                        }
                    }
                    return true;
                }
                return $this->options()->where('value', $value)->exists();
            default:
                return true;
        }
    }

    /**
     * Get HTML input type
     */
    public function getHtmlInputType()
    {
        $typeMap = [
            self::TYPE_TEXT => 'text',
            self::TYPE_NUMBER => 'number',
            self::TYPE_EMAIL => 'email',
            self::TYPE_URL => 'url',
            self::TYPE_DATE => 'date',
            self::TYPE_TIME => 'time',
            self::TYPE_DATETIME => 'datetime-local',
            self::TYPE_FILE => 'file',
            self::TYPE_IMAGE => 'file',
        ];

        return $typeMap[$this->type] ?? 'text';
    }
}
