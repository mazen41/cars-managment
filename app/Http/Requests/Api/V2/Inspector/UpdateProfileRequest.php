<?php

namespace App\Http\Requests\Api\V2\Inspector;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->user_type === 'car_inspector';
    }

    public function rules(): array
    {
        $userId = auth('api')->id();
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a valid string',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already taken',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'address.max' => 'Address cannot exceed 500 characters',
            'city.max' => 'City cannot exceed 100 characters',
            'postal_code.max' => 'Postal code cannot exceed 20 characters',
            'country.max' => 'Country cannot exceed 100 characters',
        ];
    }
}