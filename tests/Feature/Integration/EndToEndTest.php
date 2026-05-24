<?php

namespace Tests\Feature\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_crea_negocio_y_endpoints_protegidos(): void
    {
        // Registrar usuario
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Integracion User',
            'email' => 'integ@example.com',
            'password' => 'password123',
        ]);

        $register->assertStatus(201)->assertJsonStructure(['user', 'token']);

        $token = $register->json('token');

        // Crear un negocio
        $businessPayload = [
            'name' => 'Negocio Integracion'
        ];

        $create = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', $businessPayload);

        $create->assertStatus(201)->assertJsonPath('data.name', 'Negocio Integracion');

        // Listar negocios con el mismo token
        $list = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses');

        $list->assertStatus(200)->assertJsonStructure(['data', 'pagination']);
    }
}
