<?php

namespace Tests\Feature\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;

class FullE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_completo_e2e(): void
    {
        // Registrar usuario
        $register = $this->postJson('/api/auth/register', [
            'name' => 'E2E User',
            'email' => 'e2e@example.com',
            'password' => 'password123',
        ]);

        $register->assertStatus(201)->assertJsonStructure(['user', 'token']);
        $token = $register->json('token');

        // Crear negocio
        $businessResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', ['name' => 'E2E Business']);

        $businessResp->assertStatus(201)->assertJsonPath('data.name', 'E2E Business');
        $businessId = $businessResp->json('data.id');

        // Crear departamento
        $deptResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$businessId}/departments", ['name' => 'E2E Dept']);
        $deptResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'name']]);
        $departmentId = $deptResp->json('data.id');

        // Crear empleado
        $empResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$businessId}/employees", ['name' => 'E2E Emp', 'email' => 'emp@example.com']);
        $empResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'name', 'email']]);
        $employeeId = $empResp->json('data.id');

        // Crear cliente
        $clientResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$businessId}/clients", ['name' => 'E2E Client', 'email' => 'client@example.com']);
        $clientResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'name', 'email']]);
        $clientId = $clientResp->json('data.id');

        // Crear reserva
        $reservationPayload = [
            'client_id' => $clientId,
            'employee_id' => $employeeId,
            'department_id' => $departmentId,
            'scheduled_at' => now()->addDays(3)->toDateTimeString(),
            'notes' => 'E2E full flow reservation'
        ];

        $resResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/businesses/{$businessId}/reservations", $reservationPayload);
        $resResp->assertStatus(201)->assertJsonStructure(['data' => ['id', 'client_id', 'employee_id', 'scheduled_at']]);
        $reservationId = $resResp->json('data.id');

        // Actualizar reserva (confirmar)
        $updateResp = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/businesses/{$businessId}/reservations/{$reservationId}", ['status' => 'confirmed']);
        $updateResp->assertStatus(200)->assertJsonPath('data.status', 'confirmed');

        // Eliminar reserva
        $delResp = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/businesses/{$businessId}/reservations/{$reservationId}");
        $delResp->assertStatus(204);
    }
}
