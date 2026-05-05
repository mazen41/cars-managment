<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'receiver_id' => 'sometimes|exists:users,id|different:' . auth('api')->id(),
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.different' => translate('You cannot send a message to yourself'),
            'message.required' => translate('Message is required'),
            'message.max' => translate('Message cannot exceed 1000 characters'),
        ];
    }
}