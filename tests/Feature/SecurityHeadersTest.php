<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_present_in_responses(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $resp = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/businesses/{$business->id}");

        $resp->assertStatus(200);
        $resp->assertHeader('X-Frame-Options', 'DENY');
        $resp->assertHeader('X-Content-Type-Options', 'nosniff');
        $resp->assertHeader('Content-Security-Policy');
        $resp->assertHeader('Access-Control-Allow-Origin');
    }

    public function test_options_preflight_returns_204_and_cors_headers(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('default')->plainTextToken;

        $resp = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Origin' => 'https://example.com',
        ])->optionsJson('/api/businesses');

        $this->assertContains($resp->status(), [200, 204]);
        $resp->assertHeader('Access-Control-Allow-Origin');
    }
}
