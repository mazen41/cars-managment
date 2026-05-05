<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarInspectionSection extends Model
{
    use HasFactory;

    protected $table = "car_inspection_sections";

    protected $fillable = [
        "inspection_type_id",
        "name",
        "slug",
        "description",
        "is_active",
        "sort_order",
        "metadata",
    ];

    protected $casts = [
        "inspection_type_id" => "integer",
        "is_active" => "boolean",
        "sort_order" => "integer",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    protected $attributes = [
        "is_active" => true,
        "sort_order" => 0,
    ];

    // Relationships

    /**
     * Get the inspection type that owns this section
     */
    public function inspectionType(): BelongsTo
    {
        return $this->belongsTo(CarInspectionType::class, "inspection_type_id");
    }

    /**
     * Get all fields for this section
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CarInspectionField::class, "section_id")->orderBy(
            "sort_order",
        );
    }

    /**
     * Get active fields for this section
     */
    public function activeFields(): HasMany
    {
        return $this->fields()->where("is_active", true);
    }

    /**
     * Get required fields for this section
     */
    public function requiredFields(): HasMany
    {
        return $this->fields()->where("is_required", true);
    }

    // Scopes

    /**
     * Scope to get only active sections
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy("sort_order");
    }

    /**
     * Scope to filter by inspection type
     */
    public function scopeForInspectionType($query, $inspectionTypeId)
    {
        return $query->where("inspection_type_id", $inspectionTypeId);
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
     * Get the section's display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get the total number of fields
     */
    public function getTotalFieldsAttribute()
    {
        return $this->fields()->count();
    }

    /**
     * Get the total number of active fields
     */
    public function getActiveFieldsCountAttribute()
    {
        return $this->activeFields()->count();
    }

    /**
     * Get the total number of required fields
     */
    public function getRequiredFieldsCountAttribute()
    {
        return $this->requiredFields()->count();
    }

    /**
     * Check if section has any required fields
     */
    public function getHasRequiredFieldsAttribute()
    {
        return $this->required_fields_count > 0;
    }

    /**
     * Check if section is editable (no inspections completed)
     */
    public function getIsEditableAttribute()
    {
        return !$this->inspectionType
            ->inspections()
            ->where("status", "completed")
            ->exists();
    }

    /**
     * Get completion percentage for this section across all inspections
     */
    public function getCompletionPercentageAttribute()
    {
        $totalInspections = $this->inspectionType->inspections()->count();

        if ($totalInspections === 0) {
            return 0;
        }

        $completedSections = CarInspectionFieldValue::whereHas(
            "field",
            function ($query) {
                $query->where("section_id", $this->id);
            },
        )
            ->whereHas("inspection", function ($query) {
                $query->where("status", "completed");
            })
            ->distinct("inspection_id")
            ->count();

        return round(($completedSections / $totalInspections) * 100, 2);
    }

    // Helper Methods

    /**
     * Get field values for a specific inspection
     */
    public function getFieldValuesForInspection($inspectionId)
    {
        return CarInspectionFieldValue::whereHas("field", function ($query) {
            $query->where("section_id", $this->id);
        })
            ->where("inspection_id", $inspectionId)
            ->with("field")
            ->get();
    }

    /**
     * Check if section is complete for a specific inspection
     */
    public function isCompleteForInspection($inspectionId)
    {
        $requiredFieldIds = $this->requiredFields()->pluck("id");

        if ($requiredFieldIds->isEmpty()) {
            return true;
        }

        $completedRequiredFields = CarInspectionFieldValue::where(
            "inspection_id",
            $inspectionId,
        )
            ->whereIn("field_id", $requiredFieldIds)
            ->whereNotNull("value")
            ->count();

        return $completedRequiredFields === $requiredFieldIds->count();
    }

    /**
     * Get section progress for a specific inspection
     */
    public function getProgressForInspection($inspectionId)
    {
        $totalFields = $this->fields()->count();

        if ($totalFields === 0) {
            return 100;
        }

        $completedFields = CarInspectionFieldValue::whereHas("field", function (
            $query,
        ) {
            $query->where("section_id", $this->id);
        })
            ->where("inspection_id", $inspectionId)
            ->whereNotNull("value")
            ->count();

        return round(($completedFields / $totalFields) * 100, 2);
    }

    /**
     * Clone this section with all its fields
     */
    public function duplicate(
        $newInspectionTypeId = null,
        $newName = null,
        $newSlug = null,
    ) {
        $newInspectionTypeId =
            $newInspectionTypeId ?: $this->inspection_type_id;
        $newName = $newName ?: $this->name . " (Copy)";
        $newSlug = $newSlug ?: $this->slug . "-copy";

        $clone = $this->replicate();
        $clone->inspection_type_id = $newInspectionTypeId;
        $clone->name = $newName;
        $clone->slug = $newSlug;
        $clone->save();

        // Clone fields
        foreach ($this->fields as $field) {
            $fieldClone = $field->replicate();
            $fieldClone->section_id = $clone->id;
            $fieldClone->save();
        }

        return $clone;
    }

    /**
     * Get section statistics
     */
    public function getStatistics()
    {
        return [
            "total_fields" => $this->total_fields,
            "active_fields" => $this->active_fields_count,
            "required_fields" => $this->required_fields_count,
            "completion_percentage" => $this->completion_percentage,
            "field_types" => $this->getFieldTypeDistribution(),
        ];
    }

    /**
     * Get field type distribution for this section
     */
    protected function getFieldTypeDistribution()
    {
        return $this->fields()
            ->selectRaw("field_type, COUNT(*) as count")
            ->groupBy("field_type")
            ->pluck("count", "field_type")
            ->toArray();
    }

    /**
     * Move section up in sort order
     */
    public function moveUp()
    {
        $previousSection = static::where(
            "inspection_type_id",
            $this->inspection_type_id,
        )
            ->where("sort_order", "<", $this->sort_order)
            ->orderBy("sort_order", "desc")
            ->first();

        if ($previousSection) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $previousSection->sort_order;
            $previousSection->sort_order = $tempOrder;

            $this->save();
            $previousSection->save();
        }

        return $this;
    }

    /**
     * Move section down in sort order
     */
    public function moveDown()
    {
        $nextSection = static::where(
            "inspection_type_id",
            $this->inspection_type_id,
        )
            ->where("sort_order", ">", $this->sort_order)
            ->orderBy("sort_order", "asc")
            ->first();

        if ($nextSection) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $nextSection->sort_order;
            $nextSection->sort_order = $tempOrder;

            $this->save();
            $nextSection->save();
        }

        return $this;
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
                    "inspection_type_id",
                    $model->inspection_type_id,
                )->max("sort_order");
                $model->sort_order = ($maxOrder ?? 0) + 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty("name") && empty($model->slug)) {
                $model->slug = \Str::slug($model->name);
            }
        });

        // Clean up field values when section is deleted
        static::deleting(function ($model) {
            $model->fields()->delete();
        });
    }
}
