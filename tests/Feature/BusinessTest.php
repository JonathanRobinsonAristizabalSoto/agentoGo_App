<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_puede_listar_sus_negocios(): void
    {
        $user = User::factory()->create();
        $business1 = Business::factory()->create(['name' => 'Negocio 1']);
        $business2 = Business::factory()->create(['name' => 'Negocio 2']);

        $user->businesses()->attach($business1->id, ['role' => 'owner']);
        $user->businesses()->attach($business2->id, ['role' => 'editor']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'status', 'created_at', 'updated_at'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_usuario_sin_autenticacion_no_puede_listar_negocios(): void
    {
        $response = $this->getJson('/api/businesses');

        $response->assertStatus(401);
    }

    public function test_usuario_autenticado_puede_crear_negocio(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => 'Mi Nuevo Negocio',
                'slug' => 'mi-nuevo-negocio',
                'timezone' => 'America/Bogota',
                'primary_color' => '#FF5733',
                'secondary_color' => '#33FF57',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'timezone', 'primary_color', 'secondary_color', 'status', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Mi Nuevo Negocio',
            'slug' => 'mi-nuevo-negocio',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('business_user', [
            'business_id' => $response->json('data.id'),
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_crear_negocio_requiere_validacion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => '',
                'slug' => 'caracteres_invalidos_aqui!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_slug_se_genera_automaticamente_si_no_se_proporciona(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => 'Mi Negocio Especial',
                'timezone' => 'America/Bogota',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Mi Negocio Especial',
            'slug' => 'mi-negocio-especial',
        ]);
    }

    public function test_slug_debe_ser_unico(): void
    {
        $user = User::factory()->create();
        Business::factory()->create(['slug' => 'slug-unico']);

        $token = $user->createToken('default')->plainTextToken;

        // Intentar crear otro negocio con el mismo slug
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => 'Otro Negocio',
                'slug' => 'slug-unico',
            ]);

        // Debería generar un slug único automáticamente
        $response->assertStatus(201);

        $this->assertDatabaseHas('businesses', [
            'slug' => 'slug-unico-2',
        ]);
    }

    public function test_slug_con_caracteres_invalidos_falla_validacion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => 'Negocio Válido',
                'slug' => 'slug-con-caracteres-!@#',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_usuario_puede_obtener_un_negocio_especifico(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $business->id,
                    'name' => $business->name,
                ],
            ]);
    }

    public function test_usuario_no_puede_acceder_negocio_de_otro(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business = Business::factory()->create();

        $user1->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user2->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}");

        $response->assertStatus(403);
    }

    public function test_usuario_puede_actualizar_su_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['name' => 'Negocio Original']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/businesses/{$business->id}", [
                'name' => 'Negocio Actualizado',
                'timezone' => 'America/Mexico_City',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Negocio Actualizado',
                    'timezone' => 'America/Mexico_City',
                ],
            ]);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Negocio Actualizado',
        ]);
    }

    public function test_usuario_no_puede_actualizar_negocio_ajeno(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business = Business::factory()->create();

        $user1->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user2->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/businesses/{$business->id}", [
                'name' => 'Nombre Modificado',
            ]);

        $response->assertStatus(403);
    }

    public function test_usuario_puede_eliminar_su_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/businesses/{$business->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('businesses', [
            'id' => $business->id,
        ]);
    }

    public function test_usuario_no_puede_eliminar_negocio_ajeno(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business = Business::factory()->create();

        $user1->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user2->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/businesses/{$business->id}");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
        ]);
    }

    public function test_paginacion_devuelve_estructura_correcta(): void
    {
        $user = User::factory()->create();
        Business::factory()->count(25)->create()->each(function ($business) use ($user) {
            $user->businesses()->attach($business->id, ['role' => 'owner']);
        });

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'status', 'created_at', 'updated_at'],
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

    public function test_paginacion_por_defecto_es_15_items(): void
    {
        $user = User::factory()->create();
        Business::factory()->count(20)->create()->each(function ($business) use ($user) {
            $user->businesses()->attach($business->id, ['role' => 'owner']);
        });

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses');

        $response->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJson([
                'pagination' => [
                    'total' => 20,
                    'per_page' => 15,
                    'current_page' => 1,
                    'last_page' => 2,
                ],
            ]);
    }

    public function test_paginacion_respeta_parametro_per_page(): void
    {
        $user = User::factory()->create();
        Business::factory()->count(10)->create()->each(function ($business) use ($user) {
            $user->businesses()->attach($business->id, ['role' => 'owner']);
        });

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJson([
                'pagination' => [
                    'per_page' => 5,
                    'current_page' => 1,
                ],
            ]);
    }

    public function test_busqueda_en_negocios_filtra_por_nombre_y_slug(): void
    {
        $user = User::factory()->create();
        $matchingBusiness = Business::factory()->create([
            'name' => 'Clinica Central',
            'slug' => 'clinica-central',
        ]);
        $otherBusiness = Business::factory()->create([
            'name' => 'Tienda Norte',
            'slug' => 'tienda-norte',
        ]);

        $user->businesses()->attach($matchingBusiness->id, ['role' => 'owner']);
        $user->businesses()->attach($otherBusiness->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses?search=clinica');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $matchingBusiness->id,
                'name' => 'Clinica Central',
            ]);
    }

    public function test_filtro_de_estado_en_negocios_funciona(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->create([
            'name' => 'Negocio Activo',
            'status' => 'active',
        ]);
        $inactiveBusiness = Business::factory()->inactive()->create([
            'name' => 'Negocio Inactivo',
        ]);

        $user->businesses()->attach($activeBusiness->id, ['role' => 'owner']);
        $user->businesses()->attach($inactiveBusiness->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses?status=inactive');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $inactiveBusiness->id,
                'status' => 'inactive',
            ]);
    }

    public function test_paginacion_respeta_parametro_page(): void
    {
        $user = User::factory()->create();
        Business::factory()->count(30)->create()->each(function ($business) use ($user) {
            $user->businesses()->attach($business->id, ['role' => 'owner']);
        });

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'per_page' => 10,
                    'current_page' => 2,
                    'from' => 11,
                    'to' => 20,
                ],
            ]);
    }

    public function test_paginacion_valida_per_page_maximo(): void
    {
        $user = User::factory()->create();
        Business::factory()->count(5)->create()->each(function ($business) use ($user) {
            $user->businesses()->attach($business->id, ['role' => 'owner']);
        });

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/businesses?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_crear_negocio_registra_auditoria(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/businesses', [
                'name' => 'Negocio con auditoría',
                'slug' => 'negocio-auditoria',
                'status' => 'active',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => 'App\\Models\\Business',
            'action' => 'created',
            'user_id' => $user->id,
        ]);
    }

    public function test_actualizar_negocio_registra_cambios_en_auditoria(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['name' => 'Nombre original']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/businesses/{$business->id}", [
                'name' => 'Nombre actualizado',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => 'App\\Models\\Business',
            'model_id' => $business->id,
            'action' => 'updated',
            'user_id' => $user->id,
        ]);
    }

    public function test_eliminar_negocio_registra_auditoria(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/businesses/{$business->id}");

        $response->assertStatus(204);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => 'App\\Models\\Business',
            'model_id' => $business->id,
            'action' => 'deleted',
            'user_id' => $user->id,
        ]);
    }
}
