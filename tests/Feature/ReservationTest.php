<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_listar_reservas_del_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $client = Client::factory()->create(['business_id' => $business->id]);
        Reservation::factory()->count(2)->create([
            'business_id' => $business->id,
            'department_id' => $department->id,
            'employee_id' => $employee->id,
            'client_id' => $client->id,
            'created_by' => $user->id,
        ]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}/reservations");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_usuario_puede_crear_reserva(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $client = Client::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/reservations", [
                'department_id' => $department->id,
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'scheduled_at' => '2026-06-01 10:00:00',
                'ends_at' => '2026-06-01 11:00:00',
                'status' => 'scheduled',
                'notes' => 'Reserva inicial',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'status' => 'scheduled',
                'notes' => 'Reserva inicial',
            ]);
    }

    public function test_viewer_no_puede_crear_reserva(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'viewer']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $client = Client::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/reservations", [
                'department_id' => $department->id,
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'scheduled_at' => '2026-06-01 10:00:00',
            ]);

        $response->assertStatus(403);
    }

    public function test_usuario_puede_actualizar_reserva(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $client = Client::factory()->create(['business_id' => $business->id]);
        $reservation = Reservation::factory()->create([
            'business_id' => $business->id,
            'department_id' => $department->id,
            'employee_id' => $employee->id,
            'client_id' => $client->id,
            'created_by' => $user->id,
        ]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->putJson("/api/businesses/{$business->id}/reservations/{$reservation->id}", [
                'status' => 'confirmed',
                'notes' => 'Cambio de horario',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'confirmed',
                'notes' => 'Cambio de horario',
            ]);
    }

    public function test_usuario_puede_eliminar_reserva(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $client = Client::factory()->create(['business_id' => $business->id]);
        $reservation = Reservation::factory()->create([
            'business_id' => $business->id,
            'department_id' => $department->id,
            'employee_id' => $employee->id,
            'client_id' => $client->id,
            'created_by' => $user->id,
        ]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->deleteJson("/api/businesses/{$business->id}/reservations/{$reservation->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }
}
