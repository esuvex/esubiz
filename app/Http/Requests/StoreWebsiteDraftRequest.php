<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteDraftRequest extends FormRequest
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

            'type' => [

                'required',

                'string',

                'in:business,ecommerce,school,church,hotel,restaurant',

            ],

        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'type.required' => 'Please select a website type.',

            'type.in' => 'The selected website type is invalid.',

        ];
    }
}
