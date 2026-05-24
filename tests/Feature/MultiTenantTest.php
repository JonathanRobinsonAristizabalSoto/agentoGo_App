<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_allows_access_when_member(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}");

        $response->assertStatus(200);
    }

    public function test_header_denies_access_when_not_member(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business = Business::factory()->create();

        $user1->businesses()->attach($business->id, ['role' => 'owner']);

        $token = $user2->createToken('default')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->withHeader('X-Business-Id', $business->id)
            ->getJson("/api/businesses/{$business->id}");

        $response->assertStatus(403);
    }
}
