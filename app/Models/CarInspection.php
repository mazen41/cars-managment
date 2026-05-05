<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CarInspection extends Model
{
    use HasFactory;

    protected $table = "car_inspections";

    protected $fillable = [
        "car_id",
        "inspection_type_id",
        "inspector_id",
        "requested_by",
        "inspection_number",
        "status",
        "scheduled_at",
        "started_at",
        "completed_at",
        "total_score",
        "overall_condition",
        "inspector_notes",
        "recommendations",
        "summary",
        "metadata",
    ];

    protected $casts = [
        "car_id" => "integer",
        "inspection_type_id" => "integer",
        "inspector_id" => "integer",
        "requested_by" => "integer",
        "scheduled_at" => "datetime",
        "started_at" => "datetime",
        "completed_at" => "datetime",
        "total_score" => "decimal:2",
        "summary" => "array",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    protected $attributes = [
        "status" => "pending",
    ];

    // Status constants
    public const STATUS_PENDING = "pending";
    public const STATUS_IN_PROGRESS = "in_progress";
    public const STATUS_COMPLETED = "completed";
    public const STATUS_CANCELLED = "cancelled";
    public const STATUS_FAILED = "failed";

    public const STATUSES = [
        self::STATUS_PENDING => "Pending",
        self::STATUS_IN_PROGRESS => "In Progress",
        self::STATUS_COMPLETED => "Completed",
        self::STATUS_CANCELLED => "Cancelled",
        self::STATUS_FAILED => "Failed",
    ];

    // Condition constants
    public const CONDITION_EXCELLENT = "excellent";
    public const CONDITION_GOOD = "good";
    public const CONDITION_FAIR = "fair";
    public const CONDITION_POOR = "poor";
    public const CONDITION_CRITICAL = "critical";

    public const CONDITIONS = [
        self::CONDITION_EXCELLENT => "Excellent",
        self::CONDITION_GOOD => "Good",
        self::CONDITION_FAIR => "Fair",
        self::CONDITION_POOR => "Poor",
        self::CONDITION_CRITICAL => "Critical",
    ];

    // Relationships

    /**
     * Get the car being inspected
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the inspection type
     */
    public function inspectionType(): BelongsTo
    {
        return $this->belongsTo(CarInspectionType::class, "inspection_type_id");
    }

    /**
     * Get the inspector
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(CarInspector::class, "inspector_id");
    }

    /**
     * Get the user who requested the inspection
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, "requested_by");
    }

    /**
     * Get all field values for this inspection
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(CarInspectionFieldValue::class, "inspection_id");
    }

    /**
     * Get field values with fields and sections
     */
    public function fieldValuesWithRelations(): HasMany
    {
        return $this->fieldValues()->with("field.section");
    }

    /**
     * Get the payment for this inspection
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
    /**
     * Commission
     * @return MorphOne<Commission, CarInspection>
     */
    public function commission(): MorphOne
    {
        return $this->morphOne(Commission::class, 'commissionable');
    }
    // Scopes

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Scope to get pending inspections
     */
    public function scopePending($query)
    {
        return $query->where("status", self::STATUS_PENDING);
    }

    /**
     * Scope to get in progress inspections
     */
    public function scopeInProgress($query)
    {
        return $query->where("status", self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to get completed inspections
     */
    public function scopeCompleted($query)
    {
        return $query->where("status", self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by inspection type
     */
    public function scopeByType($query, $inspectionTypeId)
    {
        return $query->where("inspection_type_id", $inspectionTypeId);
    }

    /**
     * Scope to filter by inspector
     */
    public function scopeByInspector($query, $inspectorId)
    {
        return $query->where("inspector_id", $inspectorId);
    }

    /**
     * Scope to filter by car
     */
    public function scopeByCar($query, $carId)
    {
        return $query->where("car_id", $carId);
    }

    /**
     * Scope to filter by requester
     */
    public function scopeByRequester($query, $requesterId)
    {
        return $query->where("requested_by", $requesterId);
    }

    /**
     * Scope to get scheduled inspections
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull("scheduled_at");
    }

    /**
     * Scope to get overdue inspections
     */
    public function scopeOverdue($query)
    {
        return $query
            ->whereNotNull("scheduled_at")
            ->where("scheduled_at", "<", now())
            ->whereIn("status", [
                self::STATUS_PENDING,
                self::STATUS_IN_PROGRESS,
            ]);
    }
    /**
     * Scope to get by delivery to inspector flag
     */

    public function scopeDeliveredToInspector($query)
    {
        return $query->where('delivered_to_inspector', true);
    }

    /**
     * Scope to search inspections
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where("inspection_number", "like", "%{$term}%")
                ->orWhere("inspector_notes", "like", "%{$term}%")
                ->orWhere("recommendations", "like", "%{$term}%")
                ->orWhereHas("car", function ($carQuery) use ($term) {
                    $carQuery->whereHas("brand", function ($brandQuery) use ($term) {
                        $brandQuery->where("name", "like", "%{$term}%");
                    })
                    ->orWhereHas("model", function ($modelQuery) use ($term) {
                        $modelQuery->where("name", "like", "%{$term}%");
                    })
                    ->orWhere("vin", "like", "%{$term}%");
                })
                ->orWhereHas("inspector", function ($inspectorQuery) use (
                    $term,
                ) {
                    $inspectorQuery->where("shop_name", "like", "%{$term}%");
                });
        });
    }

    // Accessors & Mutators

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute()
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
    /**
     * Get the status badge
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->status;
        $badgeClass = match ($status) {
            self::STATUS_PENDING => 'badge-secondary',
            self::STATUS_IN_PROGRESS => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_FAILED => 'badge-warning',
            default => 'badge-secondary',
        };

        return "<span class='badge badge-inline {$badgeClass}'>" . translate($this->statusDisplay) . "</span>";
    }
    /**
     * Get the condition display name
     */
    public function getConditionDisplayAttribute()
    {
        return $this->overall_condition
            ? self::CONDITIONS[$this->overall_condition] ??
                    ucfirst($this->overall_condition)
            : null;
    }

    /**
     * Check if inspection is editable
     */
    public function getIsEditableAttribute()
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Check if inspection can be started
     */
    public function getCanStartAttribute()
    {
        return $this->status === self::STATUS_PENDING && $this->inspector_id;
    }

    /**
     * Check if inspection can be completed
     */
    public function getCanCompleteAttribute()
    {
        return $this->status === self::STATUS_IN_PROGRESS && $this->is_complete;
    }

    /**
     * Check if inspection can be cancelled
     */
    public function getCanCancelAttribute()
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Get inspection duration in hours
     */
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInHours($this->completed_at);
        }

        if ($this->started_at && $this->status === self::STATUS_IN_PROGRESS) {
            return $this->started_at->diffInHours(now());
        }

        return null;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration);
        $minutes = ($this->duration - $hours) * 60;

        return $hours . "h " . round($minutes) . "m";
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $totalFields = $this->inspectionType
            ->sections()
            ->with("fields")
            ->get()
            ->sum(function ($section) {
                return $section->fields->count();
            });

        if ($totalFields === 0) {
            return 100;
        }

        $completedFields = $this->fieldValues()->whereNotNull("value")->count();

        return round(($completedFields / $totalFields) * 100, 2);
    }

    /**
     * Check if all required fields are completed
     */
    public function getIsCompleteAttribute()
    {
        $requiredFields = CarInspectionField::whereHas("section", function (
            $query,
        ) {
            $query->where("inspection_type_id", $this->inspection_type_id);
        })
            ->where("is_required", true)
            ->pluck("id");

        if ($requiredFields->isEmpty()) {
            return true;
        }

        $completedRequiredFields = $this->fieldValues()
            ->whereIn("field_id", $requiredFields)
            ->whereNotNull("value")
            ->count();

        return $completedRequiredFields === $requiredFields->count();
    }

    /**
     * Check if inspection is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->scheduled_at &&
            $this->scheduled_at->isPast() &&
            !in_array($this->status, [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ]);
    }

    // Helper Methods

    /**
     * Start the inspection
     */
    public function start($inspectorId = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \Exception(
                "Inspection can only be started from pending status",
            );
        }

        if ($inspectorId) {
            $this->inspector_id = $inspectorId;
        }

        if (!$this->inspector_id) {
            throw new \Exception("Inspector must be assigned before starting");
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->started_at = now();
        $this->save();

        return $this;
    }

    /**
     * Complete the inspection
     */
    public function complete(
        $totalScore = null,
        $overallCondition = null,
        $notes = null,
    ) {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            throw new \Exception(
                "Inspection can only be completed from in-progress status",
            );
        }

        if (!$this->is_complete) {
            throw new \Exception(
                "All required fields must be completed before finishing inspection",
            );
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();

        if ($totalScore !== null) {
            $this->total_score = $totalScore;
        }

        if ($overallCondition !== null) {
            $this->overall_condition = $overallCondition;
        }

        if ($notes !== null) {
            $this->inspector_notes = $notes;
        }

        // Generate summary
        $this->summary = $this->generateSummary();

        $this->save();

        return $this;
    }

    /**
     * Cancel the inspection
     */
    public function cancel($reason = null)
    {
        if (
            in_array($this->status, [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ])
        ) {
            throw new \Exception(
                "Cannot cancel completed or already cancelled inspection",
            );
        }

        $this->status = self::STATUS_CANCELLED;

        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata["cancellation_reason"] = $reason;
            $metadata["cancelled_at"] = now()->toISOString();
            $this->metadata = $metadata;
        }

        $this->save();

        return $this;
    }

    /**
     * Generate inspection summary
     */
    protected function generateSummary()
    {
        $sections = $this->inspectionType->sections()->with("fields")->get();
        $summary = [];

        foreach ($sections as $section) {
            $sectionData = [
                "name" => $section->name,
                "total_fields" => $section->fields->count(),
                "completed_fields" => 0,
                "flagged_fields" => 0,
                "average_score" => 0,
            ];

            $sectionFieldValues = $this->fieldValues()
                ->whereHas("field", function ($query) use ($section) {
                    $query->where("section_id", $section->id);
                })
                ->get();

            $sectionData["completed_fields"] = $sectionFieldValues
                ->where("value", "!=", null)
                ->count();
            $sectionData["flagged_fields"] = $sectionFieldValues
                ->where("is_flagged", true)
                ->count();

            $scoresWithValues = $sectionFieldValues
                ->whereNotNull("score")
                ->pluck("score");
            if ($scoresWithValues->isNotEmpty()) {
                $sectionData["average_score"] = round(
                    $scoresWithValues->average(),
                    2,
                );
            }

            $summary["sections"][] = $sectionData;
        }

        // Overall summary
        $summary["overview"] = [
            "total_sections" => $sections->count(),
            "completion_percentage" => $this->completion_percentage,
            "total_flagged_fields" => $this->fieldValues()
                ->where("is_flagged", true)
                ->count(),
            "inspection_duration" => $this->duration,
        ];

        return $summary;
    }

    /**
     * Get field value for specific field
     */
    public function getFieldValue($fieldId)
    {
        return $this->fieldValues()->where("field_id", $fieldId)->first();
    }

    /**
     * Set field value
     */
    public function setFieldValue(
        $fieldId,
        $value,
        $score = null,
        $notes = null,
        $isFlag = false,
    ) {
        $fieldValue = $this->fieldValues()->updateOrCreate(
            ["field_id" => $fieldId],
            [
                "value" => $value,
                "score" => $score,
                "notes" => $notes,
                "is_flagged" => $isFlag,
            ],
        );

        return $fieldValue;
    }

    /**
     * Get section completion status
     */
    public function getSectionCompletion($sectionId)
    {
        $section = CarInspectionSection::with("fields")->find($sectionId);

        if (!$section) {
            return null;
        }

        $totalFields = $section->fields->count();
        $completedFields = $this->fieldValues()
            ->whereHas("field", function ($query) use ($sectionId) {
                $query->where("section_id", $sectionId);
            })
            ->whereNotNull("value")
            ->count();

        return [
            "section_name" => $section->name,
            "total_fields" => $totalFields,
            "completed_fields" => $completedFields,
            "completion_percentage" =>
                $totalFields > 0
                    ? round(($completedFields / $totalFields) * 100, 2)
                    : 100,
            "is_complete" => $completedFields === $totalFields,
        ];
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate inspection number
        static::creating(function ($model) {
            if (empty($model->inspection_number)) {
                $model->inspection_number = $model->generateInspectionNumber();
            }
        });

        // Clean up field values when inspection is deleted
        static::deleting(function ($model) {
            $model->fieldValues()->delete();
        });
    }

    /**
     * Generate unique inspection number
     */
    protected function generateInspectionNumber()
    {
        $prefix = "INS";
        $date = now()->format("Ymd");
        $count =
            static::whereDate("created_at", now()->toDateString())->count() + 1;
        $sequence = str_pad($count, 4, "0", STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }
    /**
     * Get the report URL
     */
    public function getReportUrlAttribute()
    {
        return route('api.car-inspections.download-pdf', $this->id);
    }
}
