<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CarInspectorCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            "data" => $this->collection->map(function ($inspector) {
                return [
                    "id" => $inspector->id,
                    "shop_name" => $inspector->shop_name,
                    "full_name" => $inspector->full_name,
                    "phone" => $inspector->phone,
                    "email" => $inspector->email,
                    "address" => $inspector->address,
                    "description" => $inspector->description,
                    "certification_number" => $inspector->certification_number,
                    "experience_years" => $inspector->experience_years,
                    "is_active" => $inspector->is_active,
                    "status_display" => $inspector->status_display,
                    "rating" => $inspector->rating,
                    "image_url" => $inspector->image_url,
                    "banner_image_url" => $inspector->banner_image_url,
                    "location" => [
                        "latitude" => $inspector->latitude,
                        "longitude" => $inspector->longitude,
                    ],
                    "working_hours" => $inspector->working_hours,
                    "services_offered" => $inspector->services_offered,
                    "user" => [
                        "id" => $inspector->user->id ?? null,
                        "name" => $inspector->user->name ?? null,
                        "country" => $inspector->user->country ?? null,
                        "city" => $inspector->user->city ?? null,
                    ],
                    "geographic_location" => [
                        "country" => $inspector->country
                            ? [
                                "id" => $inspector->country->id,
                                "name" => $inspector->country->name,
                                "code" => $inspector->country->code,
                            ]
                            : null,
                        "state" => $inspector->state
                            ? [
                                "id" => $inspector->state->id,
                                "name" => $inspector->state->name,
                                "country_id" => $inspector->state->country_id,
                            ]
                            : null,
                        "city" => $inspector->city
                            ? [
                                "id" => $inspector->city->id,
                                "name" => $inspector->city->getTranslation(
                                    "name",
                                ),
                                "state_id" => $inspector->city->state_id,
                                "country_id" => $inspector->city->country_id,
                            ]
                            : null,
                    ],
                    "statistics" => [
                        "total_inspections" => $inspector
                            ->inspections()
                            ->count(),
                        "completed_inspections" => $inspector
                            ->completedInspections()
                            ->count(),
                        "rating" => $inspector->rating,
                    ],
                    "created_at" => $inspector->created_at,
                    "updated_at" => $inspector->updated_at,
                ];
            }),
        ];
    }

    public function with($request)
    {
        return [
            "success" => true,
            "status" => 200,
        ];
    }
}
