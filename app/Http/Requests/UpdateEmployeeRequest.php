<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $business = $this->route('business');

        return [
            'department_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'name' => ['sometimes', 'string', 'max:190'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:64'],
            'position' => ['sometimes', 'nullable', 'string', 'max:120'],
            'hire_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(fn($v) => is_string($v) ? trim(strip_tags($v)) : $v, $this->only(['name', 'email', 'phone', 'position', 'status'])));
    }
}
