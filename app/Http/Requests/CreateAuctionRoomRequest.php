<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAuctionRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->user_type === 'admin' || auth()->user()->can('create_auction_room'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'bid_increment_type' => 'required|in:percentage,flat',
            'bid_increment_value' => 'required|numeric|min:0',
            'base_timer_seconds' => 'required|integer|min:30',
            'extension_seconds' => 'required|integer|min:10',
            //'insurance_deposit_amount' => 'required|numeric|min:0',
            //'currency_id' => 'required|exists:currencies,id',
            'scheduled_start_at' => 'nullable|date|after:now'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Auction room name is required',
            'commission_percentage.required' => 'Commission percentage is required',
            'commission_percentage.min' => 'Commission percentage must be at least 0%',
            'commission_percentage.max' => 'Commission percentage cannot exceed 100%',
            'bid_increment_type.required' => 'Bid increment type is required',
            'bid_increment_type.in' => 'Bid increment type must be either percentage or flat',
            'bid_increment_value.required' => 'Bid increment value is required',
            'bid_increment_value.min' => 'Bid increment value must be greater than 0',
            'base_timer_seconds.required' => 'Base timer is required',
            'base_timer_seconds.min' => 'Base timer must be at least 30 seconds',
            'base_timer_seconds.max' => 'Base timer cannot exceed 600 seconds (10 minutes)',
            'extension_seconds.required' => 'Extension time is required',
            'extension_seconds.min' => 'Extension time must be at least 10 seconds',
            'extension_seconds.max' => 'Extension time cannot exceed 300 seconds (5 minutes)',
            //'insurance_deposit_amount.required' => 'Insurance deposit amount is required',
            //'insurance_deposit_amount.min' => 'Insurance deposit amount must be at least 0',
            //'currency_id.required' => 'Currency is required',
            //'currency_id.exists' => 'Selected currency does not exist',
            'scheduled_start_at.after' => 'Scheduled start time must be in the future'
        ];
    }
}
