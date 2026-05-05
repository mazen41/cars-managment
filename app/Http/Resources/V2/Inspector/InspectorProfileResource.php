<?php

namespace App\Http\Resources\V2\Inspector;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectorProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $inspector = $this->carInspector;
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Inspector specific information
            'inspector_profile' => $inspector ? [
                'id' => $inspector->id,
                'shop_name' => $inspector->shop_name,
                'inspector_address' => $inspector->address,
                'latitude' => $inspector->latitude,
                'longitude' => $inspector->longitude,
                'inspector_phone' => $inspector->phone,
                'inspector_email' => $inspector->email,
                'image' => $inspector->image_url,
                'banner_image' => $inspector->banner_image_url,
                'is_active' => $inspector->is_active,
                'description' => $inspector->description,
                'working_hours' => $inspector->working_hours,
                'services_offered' => $inspector->services_offered,
                'certification_number' => $inspector->certification_number,
                'experience_years' => $inspector->experience_years,
                'total_owed' => $inspector->total_owed,
                'total_paid' => $inspector->total_paid,
                'rating' => $inspector->rating,
                'status_display' => $inspector->status_display,
                'country' => $inspector->country ? [
                    'id' => $inspector->country->id,
                    'name' => $inspector->country->name,
                ] : null,
                'state' => $inspector->state ? [
                    'id' => $inspector->state->id,
                    'name' => $inspector->state->name,
                ] : null,
                'city' => $inspector->city ? [
                    'id' => $inspector->city->id,
                    'name' => $inspector->city->name,
                ] : null,
                'stats' => $inspector->stats,
                'created_at' => $inspector->created_at,
                'updated_at' => $inspector->updated_at,
            ] : null,
        ];
    }
}