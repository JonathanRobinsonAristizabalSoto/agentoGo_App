<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
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
            'employee_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'client_id' => [
                'sometimes',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'scheduled_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:scheduled_at'],
            'status' => ['sometimes', 'string', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(fn($v) => is_string($v) ? trim(strip_tags($v)) : $v, $this->only(['notes', 'status'])));
    }
}
