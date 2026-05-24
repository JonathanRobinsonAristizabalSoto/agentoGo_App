<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationIndexRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled'])],
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
