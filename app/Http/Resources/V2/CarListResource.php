<?php

namespace App\Http\Resources\V2;

use App\Services\CarViewTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarListResource extends JsonResource
{
    /**
     * Transform the resource into an array for listing views.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewTrackingService = app(CarViewTrackingService::class);
        return [
            'id' => $this->id,
            'name' => $this->car_name,
            "vin"   => $this->vin,
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
            'main_photo_url' => $this->main_photo_url,
            'photos' =>  $this->formatPhotos($this->photos_array),
            'views_count'  => $viewTrackingService->getViewCount($this->id),
            'brand' => $this->whenLoaded('brand', [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'logo_url' => $this->brand->logo_url,
            ]),
            'model' => $this->whenLoaded('model', [
                'id' => $this->model->id,
                'name' => $this->model->name,
            ]),
            'category' => $this->whenLoaded('category', [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'image_url' => $this->category->image_url,
            ]),
            'color' => $this->whenLoaded('color', [
                'id' => $this->color->id,
                'name' => $this->color->name,
                'hex_code' => $this->color->hex_code,
            ]),
            'features_count' => $this->whenLoaded('features', function () {
                return $this->features->count();
            }),
            'location_summary' => $this->when(
                $this->relationLoaded('city') || $this->relationLoaded('state'),
                function () {
                    $location = [];
                    if ($this->relationLoaded('city') && $this->city) {
                        $location[] = $this->city->name;
                    }
                    if ($this->relationLoaded('state') && $this->state) {
                        $location[] = $this->state->name;
                    }
                    return implode(', ', $location);
                }
            ),
            'latest_inspection' => $this->whenLoaded('inspections', function () {
                $inspection = $this->latestCompletedInspection;
                $inspection->load('inspectionType');
                return [
                    'id' => $inspection->id,
                    'type' => $inspection->inspectionType ? $inspection->inspectionType->name : null,
                    'date' => $inspection->created_at ? $inspection->created_at->toDateString() : null,
                    'report_url' => $inspection->report_url,
                    ];
            }),
            'can_be_reserved' => $this->canBeReserved(),
            'is_reserved' => $this->isReserved(),
            'is_sold' => $this->isSold(),
            'is_featured' => $this->featured ?? false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    protected function formatPhotos($photos_array)
    {
        if (is_array($photos_array)) {
            foreach ($photos_array as $photoId) {
                $photoUrls[] = uploaded_asset($photoId);
            }
        }
        return $photoUrls;
    }
}
