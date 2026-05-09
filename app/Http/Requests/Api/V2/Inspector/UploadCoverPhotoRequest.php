<?php

namespace App\Http\Requests\Api\V2\Inspector;

use Illuminate\Foundation\Http\FormRequest;

class UploadCoverPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->user_type === 'car_inspector';
    }

    public function rules(): array
    {
        return [
            'cover_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'cover_photo.required' => 'Cover photo image is required',
            'cover_photo.image' => 'File must be an image',
            'cover_photo.mimes' => 'Cover photo must be a file of type: jpeg, png, jpg, gif, svg',
            'cover_photo.max' => 'Cover photo size cannot exceed 5MB',
        ];
    }
}
