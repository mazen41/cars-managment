<?php

namespace App\Http\Requests;

use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use App\Models\Car;
use App\Models\AuctionListingRequest;
use Illuminate\Foundation\Http\FormRequest;

class SubmitListingRequest extends FormRequest
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
            'car_id' => 'required|integer|exists:cars,id',
            'requested_starting_price' => 'required|numeric|min:1',
            'requested_reserve_price' => 'nullable|numeric|min:1|gte:requested_starting_price',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'car_id.required' => 'Car ID is required',
            'car_id.exists' => 'The selected car does not exist',
            'requested_starting_price.required' => 'Starting price is required',
            'requested_starting_price.min' => 'Starting price must be at least 1',
            'requested_reserve_price.gte' => 'Reserve price must be greater than or equal to starting price',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $carId = $this->input('car_id');

            if ($carId) {
                $car = Car::find($carId);

                // Check if car belongs to authenticated user
                if ($car && $car->user_id !== auth()->user()->id) {
                    $validator->errors()->add('car_id', 'You can only submit listing requests for your own cars');
                }

                // Check if car already has a pending or approved listing request
                $existingRequest = AuctionListingRequest::where('car_id', $carId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();

                if ($existingRequest) {
                    $status = $existingRequest->status === 'pending' ? 'pending approval' : 'already approved';
                    $validator->errors()->add('car_id', "This car already has a listing request that is {$status}");
                }

                // Check if car is in suitable condition for auction
                if ($car && $car->moderation_status != CarModerationStatusEnum::PUBLISHED) {
                    $validator->errors()->add('car_id', 'Car must be published to be listed for auction. Current status: ' . $car->moderation_status->getValue());
                }
                if ($car && $car->car_status != CarStatusEnum::AVAILABLE) {
                    $validator->errors()->add('car_id', 'Car must be in available status to be listed for auction. Current status: ' . $car->car_status->getValue());
                }
            }
        });
    }
}
