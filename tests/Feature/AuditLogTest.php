<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_puede_listar_audit_logs(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Crear algunos cambios que generen logs
        $business->update(['name' => 'Nombre actualizado']);
        $business->delete();

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'model_type',
                        'model_id',
                        'action',
                        'old_values',
                        'new_values',
                        'ip_address',
                        'user_agent',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                    'has_more_pages',
                ],
            ]);
    }

    public function test_audit_logs_devuelve_paginacion_correcta(): void
    {
        $user = User::factory()->create();

        // Crear múltiples logs
        for ($i = 0; $i < 20; $i++) {
            AuditLog::create([
                'user_id' => $user->id,
                'model_type' => 'App\\Models\\Business',
                'model_id' => 1,
                'action' => 'created',
            ]);
        }

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs');

        $response->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJson([
                'pagination' => [
                    'per_page' => 15,
                    'current_page' => 1,
                    'from' => 1,
                    'to' => 15,
                    'total' => 20,
                ]
            ]);
    }

    public function test_audit_logs_filtra_por_model_type(): void
    {
        $user = User::factory()->create();

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 1,
            'action' => 'created',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => 2,
            'action' => 'created',
        ]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs?model_type=App\\Models\\Business');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'model_type' => 'App\\Models\\Business',
                        'model_id' => 1,
                    ]
                ]
            ]);
    }

    public function test_audit_logs_filtra_por_action(): void
    {
        $user = User::factory()->create();

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 1,
            'action' => 'created',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 1,
            'action' => 'updated',
        ]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs?action=updated');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'action' => 'updated',
                    ]
                ]
            ]);
    }

    public function test_audit_logs_filtra_por_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AuditLog::create([
            'user_id' => $user1->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 1,
            'action' => 'created',
        ]);

        AuditLog::create([
            'user_id' => $user2->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 2,
            'action' => 'created',
        ]);

        $token = $user1->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/audit-logs?user_id={$user2->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $user2->id,
                    ]
                ]
            ]);
    }

    public function test_audit_logs_filtra_por_model_id(): void
    {
        $user = User::factory()->create();

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 1,
            'action' => 'created',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'model_type' => 'App\\Models\\Business',
            'model_id' => 2,
            'action' => 'created',
        ]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs?model_id=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'model_id' => 1,
                    ]
                ]
            ]);
    }

    public function test_audit_logs_validacion_per_page_maximo(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_audit_logs_validacion_action_invalida(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/audit-logs?action=unknown');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }

    public function test_audit_logs_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/audit-logs');

        $response->assertStatus(401);
    }
}
