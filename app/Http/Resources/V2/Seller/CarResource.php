<?php

namespace App\Http\Resources\V2\Seller;

use App\Http\Resources\V2\UploadedFileCollection;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->car_name,
            'vin' => $this->vin,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'condition' => $this->condition,
            'milage' => $this->milage,
            'formatted_milage' => $this->formatted_milage,
            'manufacture_year' => $this->manufacture_year,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type,
            'location' => $this->location,
            'moderation_status' => $this->moderation_status->getValue(),
            'car_status' => $this->car_status->getValue(),
            'reservation_status' => $this->reservation_status,
            "badges" => $this->badges ?? [],
            "photos" => new UploadedFileCollection(Upload::whereIn("id", explode(",", $this->photos))->get()),
            "main_photo" => new UploadedFileCollection(Upload::whereIn("id", explode(",", $this->main_photo))->get()),
            "main_photo_url" => uploaded_asset($this->main_photo),
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->getTranslation('name'),
                    'logo_url' => $this->brand->logo_url,
                ];
            }),
            'model' => $this->whenLoaded('model', function () {
                return [
                    'id' => $this->model->id,
                    'name' => $this->model->getTranslation('name'),
                    'brand_id' => $this->model->brand_id,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->getTranslation('name'),
                    'image_url' => $this->category->image_url,
                    'parent_id' => $this->category->parent_id,
                ];
            }),
            'color' => $this->whenLoaded('color', function () {
                return [
                    'id' => $this->color->id,
                    'name' => $this->color->getTranslation('name'),
                    'hex_code' => $this->color->hex_code,
                ];
            }),
            'feature_sections' => $this->whenLoaded('features', function () {
                return $this->features
                    ->groupBy('section_id')
                    ->map(function ($features, $sectionId) {
                        $section = $features->first()->section;

                        return [
                            'section_name' => $section ? $section->name : 'General',
                            'features' => $features->map(function ($feature) {
                                return [
                                    'id' => $feature->id,
                                    'name' => $feature->name,
                                    'image_url' => $feature->image_url,
                                ];
                            })->values(),
                        ];
                    })->values();
            }),
            'location_details' => $this->when(
                $this->relationLoaded('country') ||
                    $this->relationLoaded('state') ||
                    $this->relationLoaded('city'),
                function () {
                    return [
                        'country' => $this->whenLoaded('country', [
                            'id' => $this->country->id,
                            'name' => $this->country->name,
                        ]),
                        'state' => $this->whenLoaded('state', [
                            'id' => $this->state->id,
                            'name' => $this->state->name,
                        ]),
                        // 'city' => $this->whenLoaded('city', [
                        //     'id' => $this->city->id,
                        //     'name' => $this->city->name,
                        // ]),
                    ];
                }
            ),
            'custom_fields' => $this->whenLoaded('customFieldValues', function () {
                return $this->customFieldValues->map(function ($value) {
                    return [
                        'field_id' => $value->custom_field_id,
                        'field_name' => $value->customField->name ?? null,
                        'field_type' => $value->customField->type ?? null,
                        'value' => $this->formatCustomFieldValue($value),
                        'display_value' => $value->display_value,
                    ];
                });
            }),
            'inspections_count' => $this->when(isset($this->inspections_count), $this->inspections_count),
            'reservations_count' => $this->when(isset($this->reservations_count), $this->reservations_count),
            'latest_inspection' => $this->when($this->relationLoaded('inspections'), function () {
                $inspection = $this->latestCompletedInspection;
                if ($inspection) {
                    $inspection->load('inspectionType');
                    return [
                        'id' => $inspection->id,
                        'type' => $inspection->inspectionType ? $inspection->inspectionType->name : null,
                        'date' => $inspection->created_at ? $inspection->created_at->toDateString() : null,
                        'report_url' => $inspection->report_url,
                    ];
                }
                return null;
            }),
            'can_be_reserved' => $this->canBeReserved(),
            'can_be_deleted' => $this->canBeDeleted(),
            'is_reserved' => $this->isReserved(),
            'is_sold' => $this->isSold(),
            'is_published' => $this->isPublished(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Format custom field value based on field type
     */
    private function formatCustomFieldValue($value)
    {
        if (!$value->customField) {
            return $value->value;
        }

        switch ($value->customField->type) {
            case 'checkbox':
                $decoded = json_decode($value->value, true);
                return is_array($decoded) ? implode(",", $decoded) : $value->value;
            case 'date':
                return $value->value ? date('Y-m-d', strtotime($value->value)) : null;
            case 'number':
                return is_numeric($value->value) ? $value->value : $value->value;
            default:
                return $value->value;
        }
    }
}
