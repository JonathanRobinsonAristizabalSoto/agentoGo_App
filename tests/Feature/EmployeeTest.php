<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_listar_empleados_del_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        Employee::factory()->count(2)->create(['business_id' => $business->id, 'department_id' => $department->id]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}/employees");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_usuario_puede_crear_empleado(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/employees", [
                'department_id' => $department->id,
                'name' => 'Ana Pérez',
                'email' => 'ana@example.com',
                'position' => 'Recepcionista',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Ana Pérez',
                'status' => 'active',
            ]);
    }

    public function test_viewer_no_puede_crear_empleado(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'viewer']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/employees", [
                'department_id' => $department->id,
                'name' => 'No Permiso',
            ]);

        $response->assertStatus(403);
    }

    public function test_usuario_puede_actualizar_empleado(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->putJson("/api/businesses/{$business->id}/employees/{$employee->id}", [
                'name' => 'Ana Gómez',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Ana Gómez',
                'status' => 'inactive',
            ]);
    }

    public function test_usuario_puede_eliminar_empleado(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $employee = Employee::factory()->create(['business_id' => $business->id, 'department_id' => $department->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->deleteJson("/api/businesses/{$business->id}/employees/{$employee->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
