<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_registrarse(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
        ]);
    }

    public function test_registro_requiere_validacion(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'correo-invalido',
            'password' => 'corta',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_email_debe_ser_unico(): void
    {
        User::factory()->create(['email' => 'existe@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Otro Usuario',
            'email' => 'existe@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_usuario_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_login_falla_con_credenciales_invalidas(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'contraseña_incorrecta',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_usuario_autenticado_puede_obtener_su_perfil(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_usuario_sin_autenticacion_no_puede_acceder_a_me(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'default',
        ]);
    }
}
