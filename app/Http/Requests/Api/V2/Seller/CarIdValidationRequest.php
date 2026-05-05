<?php

namespace App\Http\Requests\Api\V2\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CarIdValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'car_id' => $this->route('carId')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'car_id' => 'required|integer|min:1|exists:cars,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'car_id.required' => 'Car ID is required',
            'car_id.integer' => 'Car ID must be a valid number',
            'car_id.min' => 'Car ID must be a positive number',
            'car_id.exists' => 'The specified car does not exist'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid car ID provided',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}