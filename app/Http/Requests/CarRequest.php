<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $carId = $this->route('car') ? $this->route('car')->id : null;

        return [
            'vin'   => 'required|min:17|max:17| unique:cars,vin,' . $carId,
            'description' => 'required|string|min:10',
            'brand_id' => 'required|exists:car_brands,id',
            'model_id' => 'required|exists:car_models,id',
            'category_id' => 'nullable|exists:car_categories,id',
            'color_id' => 'required|string|max:255',
            'condition' => 'required|in:new,used',
            'milage' => 'required|numeric|min:0|max:999999.99',
            'manufacture_year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . (date('Y') + 1)
            ],
            'transmission' => 'required|string|max:255',
            'fuel_type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'moderation_status' => 'required|in:draft,published,pending,rejected',
            'photos' => 'nullable',
            'main_photo' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'exists:car_features,id',

            // Custom fields validation (dynamic)
            'custom_field_*' => 'nullable'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'The car description is required.',
            'description.min' => 'The car description must be at least 10 characters.',
            'brand_id.required' => 'Please select a car brand.',
            'brand_id.exists' => 'The selected brand is invalid.',
            'model_id.required' => 'Please select a car model.',
            'model_id.exists' => 'The selected model is invalid.',
            'category_id.exists' => 'The selected category is invalid.',
            'color.required' => 'The car color is required.',
            'condition.required' => 'Please select the car condition.',
            'condition.in' => 'The car condition must be either new or used.',
            'milage.required' => 'The car mileage is required.',
            'milage.numeric' => 'The mileage must be a number.',
            'milage.min' => 'The mileage cannot be negative.',
            'milage.max' => 'The mileage is too high.',
            'manufacture_year.required' => 'The manufacture year is required.',
            'manufacture_year.integer' => 'The manufacture year must be a valid year.',
            'manufacture_year.min' => 'The manufacture year cannot be earlier than 1900.',
            'manufacture_year.max' => 'The manufacture year cannot be in the future.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price cannot be negative.',
            'price.max' => 'The price is too high.',
            'photos.array' => 'Photos must be an array.',
            'photos.max' => 'You can upload a maximum of 10 photos.',
            'photos.*.image' => 'Each photo must be an image file.',
            'photos.*.mimes' => 'Photos must be in JPEG, PNG, JPG, GIF, or WebP format.',
            'photos.*.max' => 'Each photo must be smaller than 5MB.',
            'features.array' => 'Features must be an array.',
            'features.*.exists' => 'One or more selected features are invalid.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be one of: draft, published, pending, rejected.',
            'country_id.exists' => 'The selected country is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
            'city_id.exists' => 'The selected city is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'description' => 'car description',
            'brand_id' => 'brand',
            'model_id' => 'model',
            'category_id' => 'category',
            'color' => 'color',
            'condition' => 'condition',
            'milage' => 'mileage',
            'manufacture_year' => 'manufacture year',
            'transmission' => 'transmission',
            'fuel_type' => 'fuel type',
            'location' => 'location',
            'price' => 'price',
            'country_id' => 'country',
            'state_id' => 'state',
            'city_id' => 'city',
            'moderation_status' => 'moderation_status',
            'car_status'    => 'car_status',
            'photos' => 'photos',
            'main_photo' => 'main photo',
            'features' => 'features',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that model belongs to the selected brand
            if ($this->filled('brand_id') && $this->filled('model_id')) {
                $model = \App\Models\CarModel::find($this->model_id);
                if ($model && $model->brand_id != $this->brand_id) {
                    $validator->errors()->add('model_id', 'The selected model does not belong to the selected brand.');
                }
            }


            // Validate price for published cars
            if ($this->moderation_status === 'published' && !$this->filled('price')) {
                $validator->errors()->add('price', 'Price is required for published cars.');
            }

            // Validate location for published cars
            if ($this->moderation_status === 'published' && !$this->filled('location')) {
                $validator->errors()->add('location', 'Location is required for published cars.');
            }

            // Validate custom fields
            $this->validateCustomFields($validator);
        });
    }

    /**
     * Validate custom fields based on their types and requirements.
     */
    protected function validateCustomFields($validator): void
    {
        $customFields = \App\Models\CarCustomField::all();

        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";
            $value = $this->input($fieldKey);

            // Check if required field is empty
            if ($field->required && empty($value)) {
                $validator->errors()->add($fieldKey, "The {$field->name} field is required.");
                continue;
            }

            // Skip validation if field is empty and not required
            if (empty($value) && !$field->required) {
                continue;
            }

            // Validate based on field type
            switch ($field->type) {
                case \App\Models\CarCustomField::TYPE_EMAIL:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $validator->errors()->add($fieldKey, "The {$field->name} must be a valid email address.");
                    }
                    break;

                case \App\Models\CarCustomField::TYPE_URL:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $validator->errors()->add($fieldKey, "The {$field->name} must be a valid URL.");
                    }
                    break;

                case \App\Models\CarCustomField::TYPE_NUMBER:
                    if (!is_numeric($value)) {
                        $validator->errors()->add($fieldKey, "The {$field->name} must be a number.");
                    }
                    break;

                case \App\Models\CarCustomField::TYPE_DATE:
                    if (!strtotime($value)) {
                        $validator->errors()->add($fieldKey, "The {$field->name} must be a valid date.");
                    }
                    break;

                case \App\Models\CarCustomField::TYPE_SELECT:
                case \App\Models\CarCustomField::TYPE_RADIO:
                    if (!$field->options()->where('value', $value)->exists()) {
                        $validator->errors()->add($fieldKey, "The selected {$field->name} is invalid.");
                    }
                    break;

                case \App\Models\CarCustomField::TYPE_CHECKBOX:
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            if (!$field->options()->where('value', $val)->exists()) {
                                $validator->errors()->add($fieldKey, "One or more selected {$field->name} options are invalid.");
                                break;
                            }
                        }
                    } else {
                        if (!$field->options()->where('value', $value)->exists()) {
                            $validator->errors()->add($fieldKey, "The selected {$field->name} is invalid.");
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Get the validated data with custom field processing.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Process custom fields
        $customFields = \App\Models\CarCustomField::all();
        foreach ($customFields as $field) {
            $fieldKey = "custom_field_{$field->id}";
            if ($this->has($fieldKey)) {
                $value = $this->input($fieldKey);

                // Handle checkbox fields (convert array to JSON)
                if ($field->type === \App\Models\CarCustomField::TYPE_CHECKBOX && is_array($value)) {
                    $validated[$fieldKey] = json_encode($value);
                } else {
                    $validated[$fieldKey] = $value;
                }
            }
        }

        return $validated;
    }
}
