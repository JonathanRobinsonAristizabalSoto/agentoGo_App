<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:190'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:64'],
            'primary_color' => ['sometimes', 'nullable', 'string', 'max:32'],
            'secondary_color' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no puede exceder 190 caracteres',
        ];
    }
}