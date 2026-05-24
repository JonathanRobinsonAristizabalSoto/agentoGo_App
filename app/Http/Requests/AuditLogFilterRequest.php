<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'model_type' => 'sometimes|string|max:255',
            'model_id' => 'sometimes|integer|min:1',
            'action' => 'sometimes|string|in:created,updated,deleted',
            'user_id' => 'sometimes|integer|min:1|exists:users,id',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'El máximo de items por página es 100',
            'model_id.exists' => 'El ID del modelo debe ser un número válido',
            'user_id.exists' => 'El usuario especificado no existe',
            'action.in' => 'La acción debe ser created, updated o deleted',
            'date_from.date' => 'La fecha desde debe ser una fecha válida',
            'date_to.date' => 'La fecha hasta debe ser una fecha válida',
            'date_to.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde',
        ];
    }

    public function getPerPage(): int
    {
        return (int) $this->get('per_page', 15);
    }

    public function getPage(): int
    {
        return (int) $this->get('page', 1);
    }
}
