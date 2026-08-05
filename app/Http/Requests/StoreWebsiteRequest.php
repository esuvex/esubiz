<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'workspace_id' => [
                'nullable',
                'integer',
                'exists:workspaces,id',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Website name is required.',
            'name.min'      => 'Website name must be at least 3 characters.',
            'name.max'      => 'Website name cannot exceed 150 characters.',
        ];
    }
}
