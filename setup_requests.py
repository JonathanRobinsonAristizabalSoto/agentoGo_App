import os
import sys

# Create directory structure
base_dir = os.path.dirname(os.path.abspath(__file__))
requests_dir = os.path.join(base_dir, 'app', 'Http', 'Requests')

# Create directories
os.makedirs(requests_dir, exist_ok=True)
print(f"Directory created/verified: {requests_dir}")

# StoreBusinessRequest.php content
store_content = '''<?php

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
'''

# UpdateBusinessRequest.php content
update_content = '''<?php

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
'''

# Write StoreBusinessRequest.php
store_path = os.path.join(requests_dir, 'StoreBusinessRequest.php')
with open(store_path, 'w') as f:
    f.write(store_content)
print(f"✓ Created: {store_path}")

# Write UpdateBusinessRequest.php
update_path = os.path.join(requests_dir, 'UpdateBusinessRequest.php')
with open(update_path, 'w') as f:
    f.write(update_content)
print(f"✓ Created: {update_path}")

# Verify files
print("\nVerification:")
print(f"StoreBusinessRequest.php exists: {os.path.exists(store_path)}")
print(f"UpdateBusinessRequest.php exists: {os.path.exists(update_path)}")

# Check file sizes
if os.path.exists(store_path):
    print(f"StoreBusinessRequest.php size: {os.path.getsize(store_path)} bytes")
if os.path.exists(update_path):
    print(f"UpdateBusinessRequest.php size: {os.path.getsize(update_path)} bytes")
