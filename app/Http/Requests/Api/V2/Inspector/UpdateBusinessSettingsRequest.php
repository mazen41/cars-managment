<?php

namespace App\Http\Requests\Api\V2\Inspector;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->user_type === 'car_inspector';
    }

    public function rules(): array
    {
        return [
            'shop_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'address' => 'sometimes|string|max:500',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'country_id' => 'sometimes|exists:countries,id',
            'state_id' => 'sometimes|exists:states,id',
            'city_id' => 'sometimes|exists:cities,id',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            // 'working_hours' => 'sometimes|array',
            // 'working_hours.*.day' => 'required_with:working_hours|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            // 'working_hours.*.start_time' => 'required_with:working_hours|date_format:H:i',
            // 'working_hours.*.end_time' => 'required_with:working_hours|date_format:H:i|after:working_hours.*.start_time',
            // 'working_hours.*.is_open' => 'required_with:working_hours|boolean',
            'services_offered' => 'sometimes|array',
            'services_offered.*' => 'string|max:255',
            'certification_number' => 'sometimes|string|max:100',
            'experience_years' => 'sometimes|integer|min:0|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.max' => 'Shop name cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
            'address.max' => 'Address cannot exceed 500 characters',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'country_id.exists' => 'Selected country is invalid',
            'state_id.exists' => 'Selected state is invalid',
            'city_id.exists' => 'Selected city is invalid',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'email.email' => 'Please provide a valid email address',
            'working_hours.*.day.in' => 'Day must be a valid weekday',
            'working_hours.*.start_time.date_format' => 'Start time must be in HH:MM format',
            'working_hours.*.end_time.date_format' => 'End time must be in HH:MM format',
            'working_hours.*.end_time.after' => 'End time must be after start time',
            'services_offered.*.max' => 'Each service cannot exceed 255 characters',
            'certification_number.max' => 'Certification number cannot exceed 100 characters',
            'experience_years.min' => 'Experience years cannot be negative',
            'experience_years.max' => 'Experience years cannot exceed 50',
        ];
    }
}
