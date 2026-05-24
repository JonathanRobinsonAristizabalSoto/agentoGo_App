<?php

namespace Tests\Feature\Integration;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limiter_devuelve_429_despues_de_limite(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        RateLimiter::for('business', function ($request) use ($business) {
            $key = $request->header('X-Business-Id') ?: ($request->user()?->id ?: $request->ip());
            return Limit::perMinute(2)->by($key);
        });

        // Realizar 3 peticiones; las dos primeras pasan, la tercera debe 429
        $resp1 = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}/clients");
        $resp1->assertStatus(200);

        $resp2 = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}/clients");
        $resp2->assertStatus(200);

        $resp3 = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}/clients");
        $resp3->assertStatus(429);
    }
}
