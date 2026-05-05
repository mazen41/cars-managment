<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CustomerProduct;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For create operations, any authenticated customer can create
        if ($this->isMethod('POST')) {
            return auth()->check() && auth()->user()->user_type === 'customer';
        }

        // For update/delete operations, check ownership
        if ($this->isMethod('PUT') || $this->isMethod('PATCH') || $this->isMethod('DELETE')) {
            $customerProduct = $this->route('customer_product') ?? $this->route('customerProduct');

            if (!$customerProduct) {
                return false;
            }

            // Check if the authenticated user owns this product
            return auth()->check() &&
                   auth()->user()->user_type === 'customer' &&
                   $customerProduct->user_id == auth()->id();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $customerProductId = null;

        // Get the customer product ID for update operations
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $customerProduct = $this->route('customer_product') ?? $this->route('customerProduct');
            $customerProductId = $customerProduct ? $customerProduct->id : null;
        }

        $rules = [
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'condition' => 'required|in:new,used',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category_id' => 'required|exists:categories,id',
            'address' => 'required|string|min:5|max:500',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
        ];

        // Image validation rules
        if ($this->isMethod('POST')) {
            // For create operations, main_photo is required
            $rules['main_photo'] = 'required|image|mimes:jpeg,jpg,png,webp|max:2048';
            $rules['photos'] = 'nullable|array|max:5';
            $rules['photos.*'] = 'image|mimes:jpeg,jpg,png,webp|max:2048';
        } else {
            // For update operations, main_photo is optional
            $rules['main_photo'] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048';
            $rules['photos'] = 'nullable|array|max:5';
            $rules['photos.*'] = 'image|mimes:jpeg,jpg,png,webp|max:2048';
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.min' => 'The product name must be at least 3 characters.',
            'name.max' => 'The product name cannot exceed 255 characters.',
            'description.required' => 'The product description is required.',
            'description.min' => 'The product description must be at least 10 characters.',
            'description.max' => 'The product description cannot exceed 5000 characters.',
            'condition.required' => 'Please select the product condition.',
            'condition.in' => 'The product condition must be either new or used.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'price.max' => 'The price cannot exceed 999,999.99.',
            'category_id.required' => 'Please select a product category.',
            'category_id.exists' => 'The selected category is invalid.',
            'main_photo.required' => 'A main product photo is required.',
            'main_photo.image' => 'The main photo must be an image file.',
            'main_photo.mimes' => 'The main photo must be in JPEG, JPG, PNG, or WebP format.',
            'main_photo.max' => 'The main photo must be smaller than 2MB.',
            'photos.array' => 'Photos must be provided as an array.',
            'photos.max' => 'You can upload a maximum of 5 additional photos.',
            'photos.*.image' => 'Each photo must be an image file.',
            'photos.*.mimes' => 'Photos must be in JPEG, JPG, PNG, or WebP format.',
            'photos.*.max' => 'Each photo must be smaller than 2MB.',
            'address.required' => 'The product address is required.',
            'address.min' => 'The address must be at least 5 characters.',
            'address.max' => 'The address cannot exceed 500 characters.',
            'state_id.required' => 'Please select a state.',
            'state_id.exists' => 'The selected state is invalid.',
            'city_id.required' => 'Please select a city.',
            'city_id.exists' => 'The selected city is invalid.',
            'longitude.numeric' => 'The longitude must be a valid number.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
            'latitude.numeric' => 'The latitude must be a valid number.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'description' => 'product description',
            'condition' => 'product condition',
            'price' => 'price',
            'category_id' => 'category',
            'main_photo' => 'main photo',
            'photos' => 'additional photos',
            'address' => 'address',
            'state_id' => 'state',
            'city_id' => 'city',
            'longitude' => 'longitude',
            'latitude' => 'latitude',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that city belongs to the selected state
            if ($this->filled('state_id') && $this->filled('city_id')) {
                $city = \App\Models\City::find($this->city_id);
                if ($city && $city->state_id != $this->state_id) {
                    $validator->errors()->add('city_id', 'The selected city does not belong to the selected state.');
                }
            }

            // Validate coordinates consistency
            if ($this->filled('latitude') && !$this->filled('longitude')) {
                $validator->errors()->add('longitude', 'Longitude is required when latitude is provided.');
            }

            if ($this->filled('longitude') && !$this->filled('latitude')) {
                $validator->errors()->add('latitude', 'Latitude is required when longitude is provided.');
            }

            // Validate that product is not under moderation for updates
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                $customerProduct = $this->route('customer_product') ?? $this->route('customerProduct');
                if ($customerProduct && $customerProduct->moderation_status === 'pending') {
                    $validator->errors()->add('general', 'Cannot edit product while it is under moderation.');
                }
            }

            // Validate total image count (main_photo + photos)
            $photoCount = 0;
            if ($this->hasFile('main_photo')) {
                $photoCount++;
            }
            if ($this->hasFile('photos')) {
                $photoCount += count($this->file('photos'));
            }

            if ($photoCount > 6) { // 1 main + 5 additional
                $validator->errors()->add('photos', 'You can upload a maximum of 6 photos total (1 main photo + 5 additional photos).');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize text inputs
        if ($this->has('name')) {
            $this->merge([
                'name' => trim(strip_tags($this->name))
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim(strip_tags($this->description))
            ]);
        }

        if ($this->has('address')) {
            $this->merge([
                'address' => trim(strip_tags($this->address))
            ]);
        }

        // Convert string coordinates to float
        if ($this->has('longitude') && is_string($this->longitude)) {
            $this->merge([
                'longitude' => (float) $this->longitude
            ]);
        }

        if ($this->has('latitude') && is_string($this->latitude)) {
            $this->merge([
                'latitude' => (float) $this->latitude
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        // Throw custom response
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
