<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
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

            'type' => [
                'required',
                'string',
                'in:business,ecommerce,school',
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
            'type.required' => 'Please select a website type.',
            'type.in' => 'Invalid website type selected.',
        ];
    }
}
