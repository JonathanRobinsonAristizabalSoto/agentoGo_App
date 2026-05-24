<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del departamento es requerido',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no puede exceder 190 caracteres',
            'slug.alpha_dash' => 'El slug solo puede contener letras, números y guiones',
            'status.in' => 'El estado debe ser active o inactive',
        ];
    }
}