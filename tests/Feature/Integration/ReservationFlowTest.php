<?php

namespace Tests\Feature\Integration;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_reserva_completo_crear_y_eliminar(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        // Crear negocio y asignar owner
        $business = Business::factory()->create(['name' => 'Negocio Flujo']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Crear cliente vía API
        $clientPayload = ['name' => 'Cliente Flow', 'email' => 'clientflow@example.com'];
        $clientResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$business->id}/clients", $clientPayload);

        $clientResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'name', 'email']]);
        $clientId = $clientResp->json('data.id');

        // Crear reserva
        $reservationPayload = [
            'client_id' => $clientId,
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
            'notes' => 'Reserva de prueba desde E2E'
        ];

        $resResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$business->id}/reservations", $reservationPayload);

        $resResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'client_id', 'scheduled_at']]);

        $reservationId = $resResp->json('data.id');

        // Eliminar reserva
        $delResp = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/businesses/{$business->id}/reservations/{$reservationId}");

        $delResp->assertStatus(204);
    }
}
