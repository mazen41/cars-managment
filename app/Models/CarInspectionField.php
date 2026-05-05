<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarInspectionField extends Model
{
    use HasFactory;

    protected $table = "car_inspection_fields";

    protected $fillable = [
        "section_id",
        "name",
        "slug",
        "description",
        "field_type",
        "field_options",
        "is_required",
        "is_active",
        "sort_order",
        "placeholder",
        "help_text",
        "validation_rules",
        "metadata",
    ];

    protected $casts = [
        "section_id" => "integer",
        "field_options" => "array",
        "is_required" => "boolean",
        "is_active" => "boolean",
        "sort_order" => "integer",
        "validation_rules" => "array",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    protected $attributes = [
        "is_required" => false,
        "is_active" => true,
        "sort_order" => 0,
    ];

    // Field type constants
    public const FIELD_TYPE_TEXT = "text";
    public const FIELD_TYPE_TEXTAREA = "textarea";
    public const FIELD_TYPE_BOOLEAN = "boolean";
    public const FIELD_TYPE_NUMBER = "number";
    public const FIELD_TYPE_SELECT = "select";
    public const FIELD_TYPE_CHECKBOX = "checkbox";
    public const FIELD_TYPE_RADIO = "radio";
    public const FIELD_TYPE_DATE = "date";
    public const FIELD_TYPE_EMAIL = "email";
    public const FIELD_TYPE_URL = "url";

    public const FIELD_TYPES = [
        self::FIELD_TYPE_TEXT => "Text Input",
        self::FIELD_TYPE_TEXTAREA => "Textarea",
        self::FIELD_TYPE_BOOLEAN => "Yes/No",
        self::FIELD_TYPE_NUMBER => "Number",
        self::FIELD_TYPE_SELECT => "Dropdown Select",
        self::FIELD_TYPE_CHECKBOX => "Checkboxes",
        self::FIELD_TYPE_RADIO => "Radio Buttons",
        self::FIELD_TYPE_DATE => "Date Picker",
        self::FIELD_TYPE_EMAIL => "Email",
        self::FIELD_TYPE_URL => "URL",
    ];

    // Relationships

    /**
     * Get the section that owns this field
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CarInspectionSection::class, "section_id");
    }

    /**
     * Get all field values for this field
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(CarInspectionFieldValue::class, "field_id");
    }

    /**
     * Get field values with inspections
     */
    public function fieldValuesWithInspections(): HasMany
    {
        return $this->fieldValues()->with("inspection");
    }

    // Scopes

    /**
     * Scope to get only active fields
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Scope to get only required fields
     */
    public function scopeRequired($query)
    {
        return $query->where("is_required", true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy("sort_order");
    }

    /**
     * Scope to filter by section
     */
    public function scopeForSection($query, $sectionId)
    {
        return $query->where("section_id", $sectionId);
    }

    /**
     * Scope to filter by field type
     */
    public function scopeOfType($query, $fieldType)
    {
        return $query->where("field_type", $fieldType);
    }

    /**
     * Scope to search by name or description
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where("name", "like", "%{$term}%")
                ->orWhere("description", "like", "%{$term}%")
                ->orWhere("slug", "like", "%{$term}%");
        });
    }

    // Accessors & Mutators

    /**
     * Get the field's display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get the field type display name
     */
    public function getFieldTypeDisplayAttribute()
    {
        return self::FIELD_TYPES[$this->field_type] ??
            ucfirst($this->field_type);
    }

    /**
     * Get options for select/radio/checkbox fields
     */
    public function getOptionsAttribute()
    {
        if (
            in_array($this->field_type, [
                self::FIELD_TYPE_SELECT,
                self::FIELD_TYPE_RADIO,
                self::FIELD_TYPE_CHECKBOX,
            ]) &&
            isset($this->field_options["options"])
        ) {
            return $this->field_options["options"];
        }

        return [];
    }

    /**
     * Check if field accepts multiple values
     */
    public function getIsMultipleAttribute()
    {
        return $this->field_type === self::FIELD_TYPE_CHECKBOX;
    }

    /**
     * Check if field has options
     */
    public function getHasOptionsAttribute()
    {
        return in_array($this->field_type, [
            self::FIELD_TYPE_SELECT,
            self::FIELD_TYPE_RADIO,
            self::FIELD_TYPE_CHECKBOX,
        ]);
    }

    /**
     * Get field validation rules as array
     */
    public function getValidationRulesArrayAttribute()
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = "required";
        } else {
            $rules[] = "nullable";
        }

        // Add type-specific validation
        switch ($this->field_type) {
            case self::FIELD_TYPE_EMAIL:
                $rules[] = "email";
                break;
            case self::FIELD_TYPE_URL:
                $rules[] = "url";
                break;
            case self::FIELD_TYPE_NUMBER:
                $rules[] = "numeric";
                break;
            case self::FIELD_TYPE_DATE:
                $rules[] = "date";
                break;
            case self::FIELD_TYPE_BOOLEAN:
                $rules[] = "boolean";
                break;
        }

        // Add custom validation rules
        if (!empty($this->validation_rules)) {
            $rules = array_merge($rules, $this->validation_rules);
        }

        return $rules;
    }

    /**
     * Get the total number of responses for this field
     */
    public function getTotalResponsesAttribute()
    {
        return $this->fieldValues()->whereNotNull("value")->count();
    }

    /**
     * Check if field is editable (no completed inspections)
     */
    public function getIsEditableAttribute()
    {
        return !$this->fieldValues()
            ->whereHas("inspection", function ($query) {
                $query->where("status", "completed");
            })
            ->exists();
    }

    // Helper Methods

    /**
     * Get field value for a specific inspection
     */
    public function getValueForInspection($inspectionId)
    {
        $fieldValue = $this->fieldValues()
            ->where("inspection_id", $inspectionId)
            ->first();

        return $fieldValue ? $fieldValue->value : null;
    }

    /**
     * Check if field has value for a specific inspection
     */
    public function hasValueForInspection($inspectionId)
    {
        return $this->fieldValues()
            ->where("inspection_id", $inspectionId)
            ->whereNotNull("value")
            ->exists();
    }

    /**
     * Get field statistics
     */
    public function getStatistics()
    {
        $fieldValues = $this->fieldValues()->whereNotNull("value");
        $totalResponses = $fieldValues->count();

        $stats = [
            "total_responses" => $totalResponses,
            "response_rate" => 0,
            "field_type" => $this->field_type,
            "is_required" => $this->is_required,
        ];

        if ($totalResponses > 0) {
            $totalInspections = CarInspection::whereHas(
                "inspectionType.sections.fields",
                function ($query) {
                    $query->where("id", $this->id);
                },
            )->count();

            $stats["response_rate"] =
                $totalInspections > 0
                    ? round(($totalResponses / $totalInspections) * 100, 2)
                    : 0;

            // Type-specific statistics
            switch ($this->field_type) {
                case self::FIELD_TYPE_BOOLEAN:
                    $stats[
                        "value_distribution"
                    ] = $this->getBooleanDistribution();
                    break;
                case self::FIELD_TYPE_SELECT:
                case self::FIELD_TYPE_RADIO:
                    $stats[
                        "value_distribution"
                    ] = $this->getSelectDistribution();
                    break;
                case self::FIELD_TYPE_NUMBER:
                    $stats["numeric_stats"] = $this->getNumericStatistics();
                    break;
                case self::FIELD_TYPE_CHECKBOX:
                    $stats["checkbox_stats"] = $this->getCheckboxStatistics();
                    break;
            }
        }

        return $stats;
    }

    /**
     * Get boolean field distribution
     */
    protected function getBooleanDistribution()
    {
        return $this->fieldValues()
            ->selectRaw("value, COUNT(*) as count")
            ->whereNotNull("value")
            ->groupBy("value")
            ->pluck("count", "value")
            ->toArray();
    }

    /**
     * Get select/radio field distribution
     */
    protected function getSelectDistribution()
    {
        return $this->fieldValues()
            ->selectRaw("value, COUNT(*) as count")
            ->whereNotNull("value")
            ->groupBy("value")
            ->pluck("count", "value")
            ->toArray();
    }

    /**
     * Get numeric field statistics
     */
    protected function getNumericStatistics()
    {
        $values = $this->fieldValues()
            ->whereNotNull("value")
            ->pluck("value")
            ->map(function ($value) {
                return (float) $value;
            })
            ->filter(function ($value) {
                return is_numeric($value);
            });

        if ($values->isEmpty()) {
            return [];
        }

        return [
            "min" => $values->min(),
            "max" => $values->max(),
            "average" => round($values->average(), 2),
            "median" => $values->median(),
        ];
    }

    /**
     * Get checkbox field statistics
     */
    protected function getCheckboxStatistics()
    {
        $allValues = [];
        $fieldValues = $this->fieldValues()->whereNotNull("value")->get();

        foreach ($fieldValues as $fieldValue) {
            $values = is_array($fieldValue->value)
                ? $fieldValue->value
                : json_decode($fieldValue->value, true);
            if (is_array($values)) {
                $allValues = array_merge($allValues, $values);
            }
        }

        $distribution = array_count_values($allValues);
        arsort($distribution);

        return [
            "value_distribution" => $distribution,
            "total_selections" => count($allValues),
        ];
    }

    /**
     * Clone this field
     */
    public function duplicate(
        $newSectionId = null,
        $newName = null,
        $newSlug = null,
    ) {
        $newSectionId = $newSectionId ?: $this->section_id;
        $newName = $newName ?: $this->name . " (Copy)";
        $newSlug = $newSlug ?: $this->slug . "-copy";

        $clone = $this->replicate();
        $clone->section_id = $newSectionId;
        $clone->name = $newName;
        $clone->slug = $newSlug;
        $clone->save();

        return $clone;
    }

    /**
     * Move field up in sort order within section
     */
    public function moveUp()
    {
        $previousField = static::where("section_id", $this->section_id)
            ->where("sort_order", "<", $this->sort_order)
            ->orderBy("sort_order", "desc")
            ->first();

        if ($previousField) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $previousField->sort_order;
            $previousField->sort_order = $tempOrder;

            $this->save();
            $previousField->save();
        }

        return $this;
    }

    /**
     * Move field down in sort order within section
     */
    public function moveDown()
    {
        $nextField = static::where("section_id", $this->section_id)
            ->where("sort_order", ">", $this->sort_order)
            ->orderBy("sort_order", "asc")
            ->first();

        if ($nextField) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $nextField->sort_order;
            $nextField->sort_order = $tempOrder;

            $this->save();
            $nextField->save();
        }

        return $this;
    }

    /**
     * Validate a value against this field's rules
     */
    public function validateValue($value)
    {
        $validator = \Validator::make(
            ["value" => $value],
            ["value" => $this->validation_rules_array],
        );

        return $validator->passes();
    }

    /**
     * Format value for display
     */
    public function formatValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        switch ($this->field_type) {
            case self::FIELD_TYPE_BOOLEAN:
                return $value ? "Yes" : "No";
            case self::FIELD_TYPE_CHECKBOX:
                $values = is_array($value) ? $value : json_decode($value, true);
                return is_array($values) ? implode(", ", $values) : $value;
            case self::FIELD_TYPE_DATE:
                try {
                    return \Carbon\Carbon::parse($value)->format("Y-m-d");
                } catch (\Exception $e) {
                    return $value;
                }
            default:
                return $value;
        }
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = \Str::slug($model->name);
            }

            // Set sort order if not provided
            if (empty($model->sort_order)) {
                $maxOrder = static::where(
                    "section_id",
                    $model->section_id,
                )->max("sort_order");
                $model->sort_order = ($maxOrder ?? 0) + 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty("name") && empty($model->slug)) {
                $model->slug = \Str::slug($model->name);
            }
        });

        // Clean up field values when field is deleted
        static::deleting(function ($model) {
            $model->fieldValues()->delete();
        });
    }
}
