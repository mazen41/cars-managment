<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarInspectorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "shop_name" => $this->shop_name,
            "full_name" => $this->full_name,
            "phone" => $this->phone,
            "email" => $this->email,
            "address" => $this->address,
            "description" => $this->description,
            "certification_number" => $this->certification_number,
            "experience_years" => $this->experience_years,
            "is_active" => $this->is_active,
            "status_display" => $this->status_display,
            "rating" => $this->rating,
            "image_url" => $this->image_url,
            "banner_image_url" => $this->banner_image_url,
            "location" => [
                "latitude" => $this->latitude,
                "longitude" => $this->longitude,
            ],
            "working_hours" => $this->working_hours,
            "services_offered" => $this->services_offered,
            "user" => $this->whenLoaded("user", function () {
                return [
                    "id" => $this->user->id,
                    "name" => $this->user->name,
                    "email" => $this->user->email,
                    "phone" => $this->user->phone,
                    "country" => $this->user->country,
                    "city" => $this->user->city,
                    "address" => $this->user->address,
                ];
            }),
            "geographic_location" => [
                "country" => $this->whenLoaded("country", function () {
                    return [
                        "id" => $this->country->id,
                        "name" => $this->country->name,
                        "code" => $this->country->code,
                    ];
                }),
                "state" => $this->whenLoaded("state", function () {
                    return [
                        "id" => $this->state->id,
                        "name" => $this->state->name,
                        "country_id" => $this->state->country_id,
                    ];
                }),
                "city" => $this->whenLoaded("city", function () {
                    return [
                        "id" => $this->city->id,
                        "name" => $this->city->getTranslation("name"),
                        "state_id" => $this->city->state_id,
                        "country_id" => $this->city->country_id,
                    ];
                }),
            ],
            "statistics" => $this->when(
                $request->routeIs("api.v2.cars.inspectors.show"),
                function () {
                    return $this->stats;
                },
            ),
            "recent_inspections" => $this->when(
                $request->routeIs("api.v2.cars.inspectors.show"),
                function () {
                    return $this->whenLoaded("inspections", function () {
                        return $this->inspections()
                            ->with(["car", "inspectionType"])
                            ->latest()
                            ->limit(5)
                            ->get()
                            ->map(function ($inspection) {
                                return [
                                    "id" => $inspection->id,
                                    "car_name" =>
                                        $inspection->car->car_name ?? null,
                                    "type" =>
                                        $inspection->inspectionType->name ??
                                        null,
                                    "status" => $inspection->status,
                                    "date" => $inspection->created_at->toDateString(),
                                ];
                            });
                    });
                },
            ),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }

      /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request){
        return [
            'success'   => true,
            'message'   => 'Retrieved successfully'
        ];
    }
}
