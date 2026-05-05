<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CarInspectionField;

class CarInspectionFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You may want to implement your own authorization logic here
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'field_type' => ['required', 'string', 'in:' . implode(',', array_keys(CarInspectionField::FIELD_TYPES))],
            'field_options' => ['nullable', 'array'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
     protected function prepareForValidation(): void
    {
        $options = $this->field_options ? explode(PHP_EOL, $this->field_options) : null;
        $options = $options ? array_map('trim', $options) : null;
        $this->merge([
            'field_options' => $options,
            'is_active' => $this->is_active == "on" ? true : false,
            'is_required' => $this->is_required == "on" ? true : false,
        ]);
    }
}
