<?php

namespace App\Http\Requests;

use App\Models\AuctionItem;
use App\Services\AuctionBiddingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PlaceBidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Generate bid token for idempotency if not provided
        if (!$this->has('bid_token')) {
            $this->merge([
                'bid_token' => Str::uuid()->toString()
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bid_token' => ['required', 'string', 'max:255']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Bid amount is required',
            'amount.numeric' => 'Bid amount must be a valid number',
            'amount.min' => 'Bid amount must be greater than 0',
            'bid_token.required' => 'Bid token is required for idempotency',
            'bid_token.string' => 'Bid token must be a string',
            'bid_token.max' => 'Bid token cannot exceed 255 characters'
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
                // Check if item can receive bids
                if (!$item->canReceiveBids()) {
                    $validator->errors()->add('item', 'This item is not currently accepting bids');
                    return;
                }

                $biddingService = app(AuctionBiddingService::class);
                $minimumBid = $biddingService->calculateMinimumBid($item);

                if ($this->input('amount') < $minimumBid) {
                    $validator->errors()->add('amount', "Bid amount must be at least {$minimumBid}");
                }

                // Check if user can bid (insurance deposit)
                if (!auth()->user()->canBid()) {
                    $validator->errors()->add('user', 'Insurance deposit required');
                }


                // Check if bidder is not the seller
                if (auth()->user()->id === $item->seller_id) {
                    $validator->errors()->add('user', 'You cannot bid on your own item');
                }
            }
        });
    }
}
