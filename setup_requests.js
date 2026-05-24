const fs = require('fs');
const path = require('path');

const baseDir = __dirname;
const requestsDir = path.join(baseDir, 'app', 'Http', 'Requests');

// Create directory structure
if (!fs.existsSync(requestsDir)) {
    fs.mkdirSync(requestsDir, { recursive: true });
    console.log(`Directory created: ${requestsDir}`);
} else {
    console.log(`Directory already exists: ${requestsDir}`);
}

// StoreBusinessRequest.php content
const storeContent = `<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

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
`;

// UpdateBusinessRequest.php content
const updateContent = `<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

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
`;

// Write StoreBusinessRequest.php
const storePath = path.join(requestsDir, 'StoreBusinessRequest.php');
fs.writeFileSync(storePath, storeContent);
console.log(`✓ Created: ${storePath}`);

// Write UpdateBusinessRequest.php
const updatePath = path.join(requestsDir, 'UpdateBusinessRequest.php');
fs.writeFileSync(updatePath, updateContent);
console.log(`✓ Created: ${updatePath}`);

// Verify files
console.log('\nVerification:');
console.log(`StoreBusinessRequest.php exists: ${fs.existsSync(storePath)}`);
console.log(`UpdateBusinessRequest.php exists: ${fs.existsSync(updatePath)}`);

if (fs.existsSync(storePath)) {
    const storeStats = fs.statSync(storePath);
    console.log(`StoreBusinessRequest.php size: ${storeStats.size} bytes`);
}

if (fs.existsSync(updatePath)) {
    const updateStats = fs.statSync(updatePath);
    console.log(`UpdateBusinessRequest.php size: ${updateStats.size} bytes`);
}
