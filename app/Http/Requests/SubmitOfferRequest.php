<?php

namespace App\Http\Requests;

use App\Models\AuctionItem;
use Illuminate\Foundation\Http\FormRequest;

class SubmitOfferRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'message' => ['nullable', 'string', 'max:1000']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Offer amount is required',
            'amount.numeric' => 'Offer amount must be a valid number',
            'amount.min' => 'Offer amount must be greater than 0',
            'message.string' => 'Message must be a string',
            'message.max' => 'Message cannot exceed 1000 characters'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Get the auction item from route parameter
            $item = $this->route('auctionItem');

            if ($item) {
                  // Check if item can receive offers (not started yet)
                if (!$item->canReceiveOffers()) {
                    $validator->errors()->add('item', 'Offers cannot be submitted after auction starts');
                    return;
                }
                // Check if offer amount meets starting price
                if ($this->input('amount') < $item->starting_price) {
                    $validator->errors()->add('amount', "Offer amount must be at least {$item->starting_price}");
                }



                // Check if user is not the seller
                if (auth()->user()->id === $item->seller_id) {
                    $validator->errors()->add('user', 'You cannot submit offers on your own item');
                }

                // Check if user has insurance deposit
                if (!auth()->user()->canBid()) {
                    $validator->errors()->add('user', 'Insurance deposit required');
                }
            }
        });
    }
}
