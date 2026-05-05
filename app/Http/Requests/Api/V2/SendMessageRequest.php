<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'nullable|exists:conversations,id',
            'receiver_id' => 'nullable|exists:users,id|different:' . auth('api')->id(),
            'message' => 'required|string|max:1000',
            'title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'conversation_id.exists' => translate('Invalid conversation'),
            'receiver_id.exists' => translate('Invalid receiver'),
            'receiver_id.different' => translate('You cannot send a message to yourself'),
            'message.required' => translate('Message is required'),
            'message.max' => translate('Message cannot exceed 1000 characters'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // If no conversation_id and no receiver_id, assume it's to admin
        if (!$this->conversation_id && !$this->receiver_id) {
            $this->merge(['receiver_id' => null]); // Will be handled as admin conversation
        }
    }
}