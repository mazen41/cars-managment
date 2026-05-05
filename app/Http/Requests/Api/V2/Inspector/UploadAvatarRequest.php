<?php

namespace App\Http\Requests\Api\V2\Inspector;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->user_type === 'car_inspector';
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB max
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Avatar image is required',
            'avatar.image' => 'File must be an image',
            'avatar.mimes' => 'Avatar must be a file of type: jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Avatar size cannot exceed 2MB',
        ];
    }
}