<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_listar_departamentos_del_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Department::factory()->count(2)->create(['business_id' => $business->id]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}/departments");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_usuario_puede_crear_departamento(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/departments", [
                'name' => 'Recepción',
                'description' => 'Área principal',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Recepción',
                'status' => 'active',
            ]);
    }

    public function test_usuario_puede_actualizar_departamento(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->putJson("/api/businesses/{$business->id}/departments/{$department->id}", [
                'name' => 'Recepción y Atención',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Recepción y Atención',
                'status' => 'inactive',
            ]);
    }

    public function test_usuario_puede_eliminar_departamento(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $department = Department::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->deleteJson("/api/businesses/{$business->id}/departments/{$department->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}