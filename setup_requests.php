<?php
// Create the directory structure
$basePath = __DIR__;
$dirs = [
    'app/Http/Requests'
];

foreach ($dirs as $dir) {
    $fullPath = $basePath . '/' . str_replace('\\', '/', $dir);
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created directory: $fullPath\n";
    } else {
        echo "Directory already exists: $fullPath\n";
    }
}

// Now create the StoreBusinessRequest.php file
$storeContent = <<<'EOF'
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
EOF;

file_put_contents($basePath . '/app/Http/Requests/StoreBusinessRequest.php', $storeContent);
echo "Created: app/Http/Requests/StoreBusinessRequest.php\n";

// Create the UpdateBusinessRequest.php file
$updateContent = <<<'EOF'
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
EOF;

file_put_contents($basePath . '/app/Http/Requests/UpdateBusinessRequest.php', $updateContent);
echo "Created: app/Http/Requests/UpdateBusinessRequest.php\n";

// Verify files exist
if (file_exists($basePath . '/app/Http/Requests/StoreBusinessRequest.php')) {
    echo "\n✓ StoreBusinessRequest.php verified\n";
}
if (file_exists($basePath . '/app/Http/Requests/UpdateBusinessRequest.php')) {
    echo "✓ UpdateBusinessRequest.php verified\n";
}
?>
