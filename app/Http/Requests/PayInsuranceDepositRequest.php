<?php

namespace App\Http\Requests;

use App\Models\UserInsuranceDeposit;
use Illuminate\Foundation\Http\FormRequest;

class PayInsuranceDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string'],
            'code' => ['sometimes', 'string'],
            'metadata' => ['sometimes', 'array']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Insurance deposit amount is required',
            'amount.numeric' => 'Insurance deposit amount must be a valid number',
            'amount.min' => 'Insurance deposit amount must be greater than 0',
            'provider.required' => 'Payment provider is required'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();

            // Check if user already has an active insurance deposit
            $existingDeposit = UserInsuranceDeposit::where('user_id', $user->id)
                ->where('status', 'paid')
                ->first();

            if ($existingDeposit) {
                $validator->errors()->add('user', 'You already have an active insurance deposit');
            }

            // Check if user has pending deposit
            $pendingDeposit = UserInsuranceDeposit::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingDeposit) {
                $validator->errors()->add('user', 'You have a pending insurance deposit payment');
            }
        });
    }
}
