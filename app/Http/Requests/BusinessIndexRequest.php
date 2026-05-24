<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'per_page debe ser un número entero',
            'per_page.min' => 'per_page debe ser al menos 1',
            'per_page.max' => 'per_page no puede ser mayor a 100',
            'page.integer' => 'page debe ser un número entero',
            'page.min' => 'page debe ser al menos 1',
            'search.string' => 'search debe ser texto',
            'search.max' => 'search no puede exceder 190 caracteres',
            'status.in' => 'status debe ser active o inactive',
        ];
    }

    public function getPerPage(): int
    {
        return $this->input('per_page', 15);
    }

    public function getPage(): int
    {
        return $this->input('page', 1);
    }

    public function getSearch(): ?string
    {
        $search = trim((string) $this->input('search', ''));

        return $search !== '' ? $search : null;
    }

    public function getStatus(): ?string
    {
        return $this->input('status');
    }
}