<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarInspectionFieldValue extends Model
{
    use HasFactory;

    protected $table = "car_inspection_field_values";

    protected $fillable = [
        "inspection_id",
        "field_id",
        "value",
        "file_attachments",
        "score",
        "notes",
        "is_flagged",
        "flag_reason",
        "metadata",
    ];

    protected $casts = [
        "inspection_id" => "integer",
        "field_id" => "integer",
        "file_attachments" => "array",
        "score" => "decimal:2",
        "is_flagged" => "boolean",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    protected $attributes = [
        "is_flagged" => false,
    ];

    // Relationships

    /**
     * Get the inspection that owns this field value
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(CarInspection::class, "inspection_id");
    }

    /**
     * Get the field that this value belongs to
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(CarInspectionField::class, "field_id");
    }

    // Scopes

    /**
     * Scope to get only flagged values
     */
    public function scopeFlagged($query)
    {
        return $query->where("is_flagged", true);
    }

    /**
     * Scope to get values with scores
     */
    public function scopeWithScore($query)
    {
        return $query->whereNotNull("score");
    }

    /**
     * Scope to get values with files
     */
    public function scopeWithFiles($query)
    {
        return $query->whereNotNull("file_attachments");
    }

    /**
     * Scope to filter by inspection
     */
    public function scopeForInspection($query, $inspectionId)
    {
        return $query->where("inspection_id", $inspectionId);
    }

    /**
     * Scope to filter by field
     */
    public function scopeForField($query, $fieldId)
    {
        return $query->where("field_id", $fieldId);
    }

    /**
     * Scope to filter by section
     */
    public function scopeForSection($query, $sectionId)
    {
        return $query->whereHas("field", function ($fieldQuery) use (
            $sectionId,
        ) {
            $fieldQuery->where("section_id", $sectionId);
        });
    }

    /**
     * Scope to search values
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where("value", "like", "%{$term}%")
                ->orWhere("notes", "like", "%{$term}%")
                ->orWhere("flag_reason", "like", "%{$term}%");
        });
    }

    // Accessors & Mutators
    /**
     * Summary of setValueAttribute
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['value'] = $value;
        }
    }
    /**
     * Get the formatted value based on field type
     */
    public function getFormattedValueAttribute()
    {
        if (is_null($this->value)) {
            return null;
        }

        return $this->field
            ? $this->field->formatValue($this->value)
            : $this->value;
    }

    /**
     * Check if value has attachments
     */
    public function getHasAttachmentsAttribute()
    {
        return !empty($this->file_attachments);
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCountAttribute()
    {
        return is_array($this->file_attachments)
            ? count($this->file_attachments)
            : 0;
    }

    /**
     * Check if value is empty
     */
    public function getIsEmptyAttribute()
    {
        if (is_null($this->value)) {
            return true;
        }

        if (is_string($this->value) && trim($this->value) === "") {
            return true;
        }

        if (is_array($this->value) && empty($this->value)) {
            return true;
        }

        return false;
    }

    /**
     * Get field type from related field
     */
    public function getFieldTypeAttribute()
    {
        return $this->field ? $this->field->field_type : null;
    }

    /**
     * Get field name from related field
     */
    public function getFieldNameAttribute()
    {
        return $this->field ? $this->field->name : null;
    }

    /**
     * Get section name from related field
     */
    public function getSectionNameAttribute()
    {
        return $this->field && $this->field->section
            ? $this->field->section->name
            : null;
    }

    /**
     * Check if value passes field validation
     */
    public function getIsValidAttribute()
    {
        if (!$this->field) {
            return true;
        }

        return $this->field->validateValue($this->value);
    }

    // Helper Methods

    /**
     * Flag this field value with reason
     */
    public function flag($reason = null)
    {
        $this->is_flagged = true;
        $this->flag_reason = $reason;
        $this->save();

        return $this;
    }

    /**
     * Unflag this field value
     */
    public function unflag()
    {
        $this->is_flagged = false;
        $this->flag_reason = null;
        $this->save();

        return $this;
    }

    /**
     * Add file attachment
     */
    public function addAttachment(
        $uploadId,
        $uploadUrl
    ) {
        $attachments = $this->file_attachments ?? [];

        $attachment = [
            "id" => $uploadId,
            "url"=> $uploadUrl
        ];

        $attachments[] = $attachment;
        $this->file_attachments = $attachments;
        $this->save();

        return $this;
    }

    /**
     * Remove file attachment by index
     */
    public function removeAttachment($id)
    {
        $attachments = $this->file_attachments ?? [];

        foreach($attachments as $key => $attachment){
            if($attachment['id'] == $id){
                unset($attachments[$key]);
            }
        }
        $this->file_attachments = array_values($attachments);
        $this->save();

        return $this;
    }

    /**
     * Get attachment URLs
     */
    public function getAttachmentUrls()
    {
        if (!$this->has_attachments) {
            return [];
        }

        return collect($this->file_attachments)
            ->map(function ($attachment) {
                return [
                    "url" => $attachment["url"],
                    "original_name" => $attachment["original_name"] ?? null,
                    "uploaded_at" => $attachment["uploaded_at"] ?? null,
                    "size" => $attachment["size"] ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Set value with automatic type conversion
     */
    public function setValue($value)
    {
        if (!$this->field) {
            $this->value = $value;
            return $this;
        }

        // Convert value based on field type
        switch ($this->field->field_type) {
            case CarInspectionField::FIELD_TYPE_BOOLEAN:
                $this->value = is_bool($value)
                    ? $value
                    : filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case CarInspectionField::FIELD_TYPE_NUMBER:
                $this->value = is_numeric($value) ? (float) $value : $value;
                break;
            case CarInspectionField::FIELD_TYPE_CHECKBOX:
                $this->value = is_array($value)
                    ? $value
                    : json_decode($value, true);
                break;
            case CarInspectionField::FIELD_TYPE_DATE:
                if ($value && !is_null($value)) {
                    try {
                        $this->value = \Carbon\Carbon::parse($value)->format(
                            "Y-m-d",
                        );
                    } catch (\Exception $e) {
                        $this->value = $value;
                    }
                } else {
                    $this->value = null;
                }
                break;
            default:
                $this->value = $value;
        }

        return $this;
    }

    /**
     * Update score with validation
     */
    public function setScore($score)
    {
        if ($score !== null) {
            $score = (float) $score;
            if ($score < 0 || $score > 100) {
                throw new \InvalidArgumentException(
                    "Score must be between 0 and 100",
                );
            }
        }

        $this->score = $score;
        return $this;
    }

    /**
     * Get value history (if versioning is needed)
     */
    public function getValueHistory()
    {
        $metadata = $this->metadata ?? [];
        return $metadata["value_history"] ?? [];
    }

    /**
     * Track value changes in metadata
     */
    public function trackValueChange($oldValue, $newValue, $changedBy = null)
    {
        $metadata = $this->metadata ?? [];
        $history = $metadata["value_history"] ?? [];

        $history[] = [
            "old_value" => $oldValue,
            "new_value" => $newValue,
            "changed_at" => now()->toISOString(),
            "changed_by" => $changedBy,
        ];

        // Keep only last 10 changes
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        $metadata["value_history"] = $history;
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Clone field value for another inspection
     */
    public function duplicate($newInspectionId)
    {
        $clone = $this->replicate();
        $clone->inspection_id = $newInspectionId;
        $clone->save();

        return $clone;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Track changes
        static::updating(function ($model) {
            if ($model->isDirty("value")) {
                $model->trackValueChange(
                    $model->getOriginal("value"),
                    $model->value,
                    auth()->id(),
                );
            }
        });

        // Clean up file attachments when record is deleted
        static::deleting(function ($model) {
            if ($model->has_attachments) {
                foreach ($model->file_attachments as $attachment) {
                    $filePath = storage_path("app/" . $attachment["path"]);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        });
    }
}
