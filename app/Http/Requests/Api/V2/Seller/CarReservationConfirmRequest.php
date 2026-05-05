<?php

namespace App\Http\Requests\Api\V2\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CarReservationConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reservation_id' => 'required|integer|exists:car_reservations,id',
            'admin_notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reservation_id.required' => 'Reservation ID is required',
            'reservation_id.integer' => 'Reservation ID must be a valid number',
            'reservation_id.exists' => 'The specified reservation does not exist',
            'admin_notes.string' => 'Admin notes must be a valid text',
            'admin_notes.max' => 'Admin notes cannot exceed 1000 characters'
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
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}