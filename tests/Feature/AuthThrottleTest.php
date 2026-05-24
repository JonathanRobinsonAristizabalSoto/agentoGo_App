<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_excede_intentos_devuelve_429(): void
    {
        $user = User::factory()->create([
            'email' => 'throttle@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Realizar 6 intentos con contraseña incorrecta (login throttle por defecto 5/min)
        for ($i = 0; $i < 6; $i++) {
            $resp = $this->postJson('/api/auth/login', [
                'email' => 'throttle@example.com',
                'password' => 'wrong-password',
            ]);

            if ($i < 5) {
                // primeros intentos pueden fallar con 422 (credenciales) o 429 dependiendo del timing
                $this->assertTrue(in_array($resp->status(), [422, 429]));
            } else {
                $resp->assertStatus(429);
            }
        }
    }
}
