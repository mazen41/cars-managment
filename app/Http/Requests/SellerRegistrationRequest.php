<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class SellerRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

     /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Combine country code and phone number
        if ($this->has(['country_code', 'phone_number'])) {
            $this->merge([
                'phone' => '+'.$this->country_code . $this->phone_number
            ]);
        }
    }

    public function rules()
    {
        $rules = [];
        // Add validation for individual parts
        $rules['country_code']  = 'required|string|max:5';
        $rules['phone_number']  = 'required|string|max:20';
        // Validate the combined phone number
        $rules['phone']         = 'required|string|max:255|unique:users';
        $rules['name']          = 'required|string|max:255';
        //$rules['email']         = 'required|email|unique:users|max:255';
        $rules['password']      = 'required|string|min:6|confirmed';
        $rules['shop_name']     = 'required|max:255';
        $rules['address']       = 'required';

        return $rules;
    }

    public function messages()
    {
        return [
            'country_code.required' => translate('Country code is required'),
            'country_code.max'      => translate('Country code is too long'),
            'phone_number.required' => translate('Phone number is required'),
            'phone_number.max'      => translate('Phone number is too long'),
            'phone.unique'          => translate('This phone number is already registered'),
            'name.required'         => translate('Name is required'),
            'name.string'           => translate('Name should be string type'),
            'name.max'              => translate('Max 255 characters'),
            'email.required'        => translate('Email is required'),
            'email.email'           => translate('Please type a valid email'),
            'email.unique'          => translate('Email should be unique'),
            'email.max'             => translate('Max 255 characters'),
            'password.required'     => translate('Password is required'),
            'password.string'       => translate('Password should be string type'),
            'password.min'          => translate('Min 6 characters'),
            'password.confirmed'    => translate('Confirm password do not matched'),
            'shop_name.required'    => translate('Shop name is required'),
            'shop_name.max'         => translate('Max 255 characters'),
            'address.required'      => translate('Address is required'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => $validator->errors()->all(),
                'result' => false
            ], 422));
        } else {
            throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
        }
    }
}
