<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CarInspectionResource extends JsonResource
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
            'inspection_type' => $this->inspectionType->name,
            'inspection_number' => $this->inspection_number,
            'status'    => $this->status,
            'completed_at'  => $this->completed_at ? Carbon::parse($this->completed_at)->format('d M Y, H:i') : null,
            'total_score'   => $this->total_score,
            'overall_condition' => $this->overall_condition,
            "delivered_to_inspector" => $this->delivered_to_inspector,
            'created_at' => $this->created_at,
            'car'   => new CarResource($this->whenLoaded('car')),
            'section_data'  => $this->when($request->routeIs('api.car-inspections.show'), function (){
                $sectionData = [];
                foreach ($this->inspectionType->sections as $section) {
                    $sectionData[$section->id] = [
                        "section" => [
                            'name'  => $section->name,
                            'description'   => $section->description
                        ],
                    ];

                    foreach ($section->fields as $field) {
                        $fieldValue = $this->fieldValues
                            ->where("field_id", $field->id)
                            ->first();

                        $sectionData[$section->id]["fields"][] = [
                            "field_name" => $field->name,
                             "value" => $fieldValue->formatted_value ?? null,
                            "notes" => $fieldValue->notes ?? null,
                            "photos" => $fieldValue? $fieldValue->getAttachmentUrls() : null,
                        ];
                    }
                }
                return $sectionData;
            }),
            'report_url'   => $this->when($this->status === 'completed', function (){
                return $this->report_url;
            }),
        ];
    }
}
