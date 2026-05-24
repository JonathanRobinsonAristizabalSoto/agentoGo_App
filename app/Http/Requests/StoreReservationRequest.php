<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
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
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('business_id', $business?->id)),
            ],
            'scheduled_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
            'status' => ['nullable', 'string', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(fn($v) => is_string($v) ? trim(strip_tags($v)) : $v, $this->only(['notes', 'status'])));
    }
}
