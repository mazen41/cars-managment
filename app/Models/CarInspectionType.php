<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarInspectionType extends Model
{
    use HasFactory;

    protected $table = "car_inspection_types";

    protected $fillable = [
        "name",
        "slug",
        "description",
        "price",
        "is_system_default",
        "is_active",
        "sort_order",
        "metadata",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "is_system_default" => "boolean",
        "sort_order" => "integer",
        "price" => "decimal:2",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    protected $attributes = [
        "is_active" => true,
        "sort_order" => 0,
        "price" => 0.0,
    ];

    // Relationships

    /**
     * Get all sections for this inspection type
     */
    public function sections(): HasMany
    {
        return $this->hasMany(
            CarInspectionSection::class,
            "inspection_type_id",
        )->orderBy("sort_order");
    }

    /**
     * Get active sections for this inspection type
     */
    public function activeSections(): HasMany
    {
        return $this->sections()->where("is_active", true);
    }

    /**
     * Get all inspections of this type
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(CarInspection::class, "inspection_type_id");
    }

    /**
     * Get completed inspections of this type
     */
    public function completedInspections(): HasMany
    {
        return $this->inspections()->where("status", "completed");
    }

    // Scopes

    /**
     * Scope to get only active inspection types
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
     * Get the inspection type's display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get the total number of sections
     */
    public function getTotalSectionsAttribute()
    {
        return $this->sections()->count();
    }

    /**
     * Get the total number of active sections
     */
    public function getActiveSectionsCountAttribute()
    {
        return $this->activeSections()->count();
    }

    /**
     * Get the total number of fields across all sections
     */
    public function getTotalFieldsAttribute()
    {
        return $this->sections()
            ->with("fields")
            ->get()
            ->sum(function ($section) {
                return $section->fields->count();
            });
    }

    /**
     * Get the total number of inspections
     */
    public function getTotalInspectionsAttribute()
    {
        return $this->inspections()->count();
    }

    /**
     * Get the completion rate for this inspection type
     */
    public function getCompletionRateAttribute()
    {
        $total = $this->inspections()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedInspections()->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get formatted price attribute
     */
    public function getFormattedPriceAttribute()
    {
        return single_price($this->price);
    }

    /**
     * Get price without currency symbol
     */
    public function getPriceDisplayAttribute()
    {
        return number_format($this->price, 2);
    }

    /**
     * Check if inspection type is editable
     */
    public function getIsEditableAttribute()
    {
        return true;
        //return $this->inspections()->count() === 0;
    }

    // Helper Methods

    /**
     * Get all fields for this inspection type
     */
    public function getAllFields()
    {
        return CarInspectionField::whereHas("section", function ($query) {
            $query->where("inspection_type_id", $this->id);
        })
            ->with("section")
            ->orderBy("sort_order")
            ->get();
    }

    /**
     * Get active fields for this inspection type
     */
    public function getActiveFields()
    {
        return CarInspectionField::whereHas("section", function ($query) {
            $query
                ->where("inspection_type_id", $this->id)
                ->where("is_active", true);
        })
            ->where("is_active", true)
            ->with("section")
            ->orderBy("sort_order")
            ->get();
    }

    /**
     * Clone this inspection type with all its sections and fields
     */
    public function duplicate($newName = null, $newSlug = null)
    {
        $newName = $newName ?: $this->name . " (Copy)";
        $newSlug = $newSlug ?: $this->slug . "-copy";

        $clone = $this->replicate();
        $clone->name = $newName;
        $clone->slug = $newSlug;
        $clone->save();

        // Clone sections and fields
        foreach ($this->sections as $section) {
            $sectionClone = $section->replicate();
            $sectionClone->inspection_type_id = $clone->id;
            $sectionClone->save();

            foreach ($section->fields as $field) {
                $fieldClone = $field->replicate();
                $fieldClone->section_id = $sectionClone->id;
                $fieldClone->save();
            }
        }

        return $clone;
    }

    /**
     * Get inspection statistics for this type
     */
    public function getStatistics()
    {
        $inspections = $this->inspections();

        return [
            "total_inspections" => $inspections->count(),
            "pending" => $inspections->where("status", "pending")->count(),
            "in_progress" => $inspections
                ->where("status", "in_progress")
                ->count(),
            "completed" => $inspections->where("status", "completed")->count(),
            "cancelled" => $inspections->where("status", "cancelled")->count(),
            "failed" => $inspections->where("status", "failed")->count(),
            "completion_rate" => $this->completion_rate,
            "average_completion_time" => $this->getAverageCompletionTime(),
            "total_sections" => $this->total_sections,
            "total_fields" => $this->total_fields,
        ];
    }

    /**
     * Get average completion time in hours
     */
    protected function getAverageCompletionTime()
    {
        $completedInspections = $this->completedInspections()
            ->whereNotNull("started_at")
            ->whereNotNull("completed_at")
            ->get();

        if ($completedInspections->isEmpty()) {
            return 0;
        }

        $totalHours = $completedInspections->sum(function ($inspection) {
            return $inspection->started_at->diffInHours(
                $inspection->completed_at,
            );
        });

        return round($totalHours / $completedInspections->count(), 2);
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
        });

        static::updating(function ($model) {
            if ($model->isDirty("name") && empty($model->slug)) {
                $model->slug = \Str::slug($model->name);
            }
        });
    }
}
