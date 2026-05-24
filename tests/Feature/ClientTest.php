<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_listar_clientes_del_negocio(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Client::factory()->count(2)->create(['business_id' => $business->id]);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}/clients");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_usuario_puede_crear_cliente(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/clients", [
                'name' => 'Cliente Uno',
                'email' => 'cliente@example.com',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Cliente Uno',
                'status' => 'active',
            ]);
    }

    public function test_viewer_no_puede_crear_cliente(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'viewer']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/businesses/{$business->id}/clients", [
                'name' => 'No Permiso',
            ]);

        $response->assertStatus(403);
    }

    public function test_usuario_puede_actualizar_cliente(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $client = Client::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->putJson("/api/businesses/{$business->id}/clients/{$client->id}", [
                'name' => 'Cliente Actualizado',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Cliente Actualizado',
                'status' => 'inactive',
            ]);
    }

    public function test_usuario_puede_eliminar_cliente(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $client = Client::factory()->create(['business_id' => $business->id]);
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->deleteJson("/api/businesses/{$business->id}/clients/{$client->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
