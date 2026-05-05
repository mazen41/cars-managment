<?php

namespace App\Http\Resources\V2;

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
            "vin"   => $this->vin,
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
            "map_location" => $this->user->user_type === 'admin' || $this->user->user_type === 'staff' ? [
                        "longtitude" => get_setting("google_map_longtitude"),
                        "latitude" => get_setting("google_map_latitude"),
                    ] :
                    [
                        "longitude" => $this->user->shop->delivery_pickup_longitude,
                        "latitude" => $this->user->shop->delivery_pickup_latitude
                    ],
            'moderation_status' => $this->moderation_status->getValue(),
            'car_status' => $this->car_status->getValue(),
            'reservation_status' => $this->reservation_status,
            'main_photo_url' => $this->main_photo_url,
            'photos' => $this->when($request->routeIs('api.v2.cars.show'), function () {
                $photoUrls = [];
                $photos = explode(',', $this->photos);
                if (is_array($photos)) {
                    foreach ($photos as $photoId) {
                        $photoUrls[] = uploaded_asset($photoId);
                    }
                }
                return $photoUrls;
            }),
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
                        //     'id' => $this->city->id ,
                        //     'name' => $this->city->name,
                        // ]),
                    ];
                }
            ),
            'custom_fields' => $this->when($request->routeIs('api.v2.cars.show'), function () {
                return $this->whenLoaded('customFieldValues', function () {
                    return $this->customFieldValues->map(function ($value) {
                        return [
                            'field_id' => $value->custom_field_id,
                            'field_name' => $value->customField->name ?? null,
                            'field_type' => $value->customField->type ?? null,
                            'value' => $value->display_value,
                        ];
                    });
                });
            }),
            'is_from_admin' => $this->user->user_type === 'admin' || $this->user->user_type === 'staff',
            'owner' => $this->when($request->routeIs('api.v2.cars.show'), function () {
                return $this->whenLoaded('user', function (){
                    if ($this->user->user_type === 'admin' || $this->user->user_type === 'staff'){
                        return [
                            'name' => translate('Internal')
                        ];
                    } else {
                        return [
                        'name' => $this->user->shop->name,
                        'logo' => uploaded_asset($this->user->shop->logo),
                        'id'   => $this->user->id,
                        'shop_id'     => $this->user->shop->id,
                        ];
                    }
                });
            }),
            'stats' => $this->when($request->routeIs('api.v2.cars.show'), function () {
                return [
                    'views_count' => $this->views_count ?? 0,
                    'favorites_count' => $this->favorites_count ?? 0,
                    'inquiries_count' => $this->inquiries_count ?? 0,
                ];
            }),
            'latest_inspection' => $this->when($request->routeIs('api.v2.cars.show'), function () {
                    $inspection = $this->latestCompletedInspection;
                    if($inspection){
                    $inspection->load('inspectionType');
                    return [
                        'id' => $inspection->id,
                        'type' => $inspection->inspectionType ? $inspection->inspectionType->name : null,
                        'date' => $inspection->created_at ? $inspection->created_at->toDateString() : null,
                        'report_url' => $inspection->report_url,
                    ];
                    }
            }),
            'can_be_reserved' => $this->canBeReserved(),
            'is_reserved' => $this->isReserved(),
            'is_sold' => $this->isSold(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
