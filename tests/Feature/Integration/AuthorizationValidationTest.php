<?php

namespace Tests\Feature\Integration;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_propietario_recibe_403_al_modificar_negocio(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownerToken = $owner->createToken('default')->plainTextToken;
        $otherToken = $other->createToken('default')->plainTextToken;

        // Crear negocio directamente y asignar owner
        $business = Business::factory()->create(['name' => 'Negocio Propietario']);
        $owner->businesses()->attach($business->id, ['role' => 'owner']);
        $businessId = $business->id;

        // Otro usuario intenta actualizar -> 403
        $update = $this->withHeader('Authorization', "Bearer $otherToken")
            ->putJson("/api/businesses/{$businessId}", ['name' => 'Cambio Malicioso']);

        $update->assertStatus(403);
    }

    public function test_crear_reserva_sin_campos_obligatorios_devuelve_422(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        // Crear negocio para el usuario
        $create = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', ['name' => 'Negocio Reserva']);

        $businessId = $create->json('data.id');

        // Intentar crear reserva sin campos requeridos
        $payload = [
            // suponemos que 'client_id' y 'scheduled_at' son obligatorios
        ];

        $resp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$businessId}/reservations", $payload);

        $resp->assertStatus(422)->assertJsonValidationErrors(['client_id', 'scheduled_at']);
    }
}
