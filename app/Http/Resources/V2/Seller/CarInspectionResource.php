<?php

namespace App\Http\Resources\V2\Seller;

use App\Models\CarInspection;
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
            'id'    => $this->id,
            'inspection_number' => $this->inspection_number,
            'status'    => $this->status,
            'completed_at'  => $this->completed_at ? Carbon::parse($this->completed_at)->format('d M Y, H:i') : null,
            'total_score'   => $this->total_score,
            'overall_condition' => $this->overall_condition,
            'requested_at'   => $this->created_at ? Carbon::parse($this->created_at)->format('d M Y, H:i') : null,
            "delivered_to_inspector" => $this->delivered_to_inspector,
            'can_be_marked_as_delivered' => !$this->delivered_to_inspector && $this->status == CarInspection::STATUS_PENDING && $this->inspector !=null,
            'can_download_report' => $this->status == CarInspection::STATUS_COMPLETED,
            "inspector" => $this->whenLoaded("inspector", function (){
                return [
                    "id"    => $this->inspector->id,
                    "name"  => $this->inspector->shop_name,
                    "avatar"=> uploaded_asset($this->inspector->avatar_original),
                ];
            }),
            "requester"      => [
                "id"    => $this->requester->id,
                "name"  => $this->requester->name,
                "avatar"=> uploaded_asset($this->requester->avatar_original),
            ],
            "inspection_type"=> [
                "id"    => $this->inspectionType->id,
                "name"  => $this->inspectionType->name,
            ],
            "payment"        => $this->whenLoaded('payment', function (){
                return [
                    "id"    => $this->payment->id,
                    "amount"=> $this->payment->amount,
                    "status"=> $this->payment->status,
                    "paid_at"=> $this->payment->paid_at ? Carbon::parse($this->payment->paid_at)->format("d M Y, H:i") : null,
                    "payment_method"=> $this->payment->method,
                ];
            }),
            'section_data'  => $this->when($request->routeIs('api.seller.car-inspections.show') && $this->status == 'completed', function (){
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
        ];
    }
}
