<?php

namespace App\Http\Resources\V2\Inspector;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_number' => $this->inspection_number,
            'status' => $this->status,
            'status_display' => $this->status_display,
            'status_badge' => $this->status_badge,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'total_score' => $this->total_score,
            'overall_condition' => $this->overall_condition,
            'condition_display' => $this->condition_display,
            'inspector_notes' => $this->inspector_notes,
            'recommendations' => $this->recommendations,
            'summary' => $this->summary,
            'metadata' => $this->metadata,
            'duration' => $this->duration,
            'formatted_duration' => $this->formatted_duration,
            'completion_percentage' => $this->completion_percentage,
            'is_complete' => $this->is_complete,
            'is_editable' => $this->is_editable,
            'is_overdue' => $this->is_overdue,
            'can_start' => $this->can_start,
            'can_complete' => $this->can_complete,
            'can_cancel' => $this->can_cancel,
            'report_url' => $this->report_url,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Related data
            'car' => $this->whenLoaded('car', function () {
                return [
                    'id' => $this->car->id,
                    'name' => $this->car->name,
                    'brand' => $this->car->carBrand?->name,
                    'model' => $this->car->carModel?->name,
                    'year' => $this->car->year,
                    'color' => $this->car->carColor?->name,
                    'fuel_type' => $this->car->fuel_type,
                    'transmission_type' => $this->car->transmission_type,
                    'mileage' => $this->car->mileage,
                    'vin_number' => $this->car->vin_number,
                    'license_plate' => $this->car->license_plate,
                    'thumbnail_img' => $this->car->thumbnail_img ? uploaded_asset($this->car->thumbnail_img) : null,
                    'photos' => $this->car->photos ? collect($this->car->photos)->map(function ($photo) {
                        return uploaded_asset($photo);
                    }) : [],
                ];
            }),
            
            'inspection_type' => $this->whenLoaded('inspectionType', function () {
                return [
                    'id' => $this->inspectionType->id,
                    'name' => $this->inspectionType->name,
                    'description' => $this->inspectionType->description,
                    'price' => $this->inspectionType->price,
                    'formatted_price' => format_price($this->inspectionType->price),
                    'duration_minutes' => $this->inspectionType->duration_minutes,
                ];
            }),
            
            'inspector' => $this->whenLoaded('inspector', function () {
                return [
                    'id' => $this->inspector->id,
                    'shop_name' => $this->inspector->shop_name,
                    'phone' => $this->inspector->phone,
                    'email' => $this->inspector->email,
                    'rating' => $this->inspector->rating,
                    'user' => [
                        'id' => $this->inspector->user->id,
                        'name' => $this->inspector->user->name,
                    ],
                ];
            }),
            
            'requester' => $this->whenLoaded('requester', function () {
                return [
                    'id' => $this->requester->id,
                    'name' => $this->requester->name,
                    'email' => $this->requester->email,
                    'phone' => $this->requester->phone,
                ];
            }),
            
            'field_values' => $this->whenLoaded('fieldValuesWithRelations', function () {
                return $this->fieldValuesWithRelations->map(function ($fieldValue) {
                    return [
                        'id' => $fieldValue->id,
                        'field_id' => $fieldValue->field_id,
                        'value' => $fieldValue->value,
                        'score' => $fieldValue->score,
                        'notes' => $fieldValue->notes,
                        'is_flagged' => $fieldValue->is_flagged,
                        'photos' => $fieldValue->photos ? collect($fieldValue->photos)->map(function ($photo) {
                            return uploaded_asset($photo);
                        }) : [],
                        'field' => [
                            'id' => $fieldValue->field->id,
                            'name' => $fieldValue->field->name,
                            'description' => $fieldValue->field->description,
                            'field_type' => $fieldValue->field->field_type,
                            'is_required' => $fieldValue->field->is_required,
                            'options' => $fieldValue->field->options,
                            'section' => [
                                'id' => $fieldValue->field->section->id,
                                'name' => $fieldValue->field->section->name,
                                'description' => $fieldValue->field->section->description,
                            ],
                        ],
                    ];
                });
            }),
            
            'payment' => $this->whenLoaded('payment', function () {
                return [
                    'id' => $this->payment->id,
                    'amount' => $this->payment->amount,
                    'formatted_amount' => format_price($this->payment->amount),
                    'status' => $this->payment->payment_status,
                    'payment_method' => $this->payment->payment_method,
                    'created_at' => $this->payment->created_at->toISOString(),
                ];
            }),
        ];
    }
}