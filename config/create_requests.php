<?php

// Auto-create FormRequest directory structure
$requestsDir = base_path('app/Http/Requests');
if (!is_dir($requestsDir)) {
    @mkdir($requestsDir, 0755, true);
}

// Create StoreBusinessRequest.php
$storeFile = $requestsDir . '/StoreBusinessRequest.php';
if (!file_exists($storeFile)) {
    file_put_contents($storeFile, <<<'EOF'
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
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
            'timezone' => ['nullable', 'string', 'max:64'],
            'primary_color' => ['nullable', 'string', 'max:32'],
            'secondary_color' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del negocio es requerido',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no puede exceder 190 caracteres',
            'slug.alpha_dash' => 'El slug solo puede contener letras, números y guiones',
        ];
    }
}
EOF
    );
}

// Create UpdateBusinessRequest.php
$updateFile = $requestsDir . '/UpdateBusinessRequest.php';
if (!file_exists($updateFile)) {
    file_put_contents($updateFile, <<<'EOF'
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
EOF
    );
}

return [];
