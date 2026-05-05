<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CarInspectionTypeCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'car_inspection_types'  => $this->collection->map(function ($type) {
                return [
                    "id"    => $type->id,
                    "name"      => $type->name,
                    "description"   => $type->description,
                    "price" => $type->price,
                    "formatted_price" => single_price($type->price),
                    "sections" => $type->sections->map(function ($section) {
                        return [
                            "name" => $section->name,
                        ];
                    })
                ];
            })
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Inspection types retrieved successfully',
        ];
    }
}
