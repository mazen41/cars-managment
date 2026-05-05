<?php

namespace App\Http\Requests\Inspector;

use App\Exceptions\Inspector\FileUploadException;
use App\Services\Inspector\FileUploadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check() && 
               auth('api')->user()->user_type === 'car_inspector';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $context = $this->route('context') ?? 'inspection_photos';
        $uploadService = new FileUploadService();
        
        $allowedTypes = implode(',', $uploadService->getAllowedTypes($context));
        $maxSize = $uploadService->getMaxSize($context) / 1024; // Convert to KB for validation

        return [
            'files' => 'required|array|min:1|max:10',
            'files.*' => [
                'required',
                'file',
                "mimes:{$allowedTypes}",
                "max:{$maxSize}",
            ],
            'context' => 'sometimes|string|in:inspection_photos,documents,avatar',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'At least one file is required',
            'files.array' => 'Files must be provided as an array',
            'files.min' => 'At least one file is required',
            'files.max' => 'Maximum 10 files can be uploaded at once',
            'files.*.required' => 'Each file is required',
            'files.*.file' => 'Each upload must be a valid file',
            'files.*.mimes' => 'File type not allowed for this context',
            'files.*.max' => 'File size exceeds maximum allowed size',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'files.*' => 'file',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            response()->json([
                'error' => [
                    'message' => 'File validation failed',
                    'code' => 'FILE_VALIDATION_ERROR',
                    'details' => $errors,
                ]
            ], 422)
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Additional custom validation can be added here
            $this->validateFileContents($validator);
        });
    }

    /**
     * Validate file contents beyond basic Laravel validation
     */
    protected function validateFileContents(Validator $validator): void
    {
        if (!$this->hasFile('files')) {
            return;
        }

        $files = $this->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $index => $file) {
            try {
                // Check if file is actually an image when it claims to be
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $imageInfo = @getimagesize($file->getPathname());
                    if ($imageInfo === false) {
                        $validator->errors()->add(
                            "files.{$index}",
                            'The file appears to be corrupted or is not a valid image'
                        );
                    }
                }

                // Check for minimum file size (avoid empty files)
                if ($file->getSize() < 100) { // 100 bytes minimum
                    $validator->errors()->add(
                        "files.{$index}",
                        'The file is too small or appears to be empty'
                    );
                }

            } catch (\Exception $e) {
                $validator->errors()->add(
                    "files.{$index}",
                    'The file could not be validated properly'
                );
            }
        }
    }
}