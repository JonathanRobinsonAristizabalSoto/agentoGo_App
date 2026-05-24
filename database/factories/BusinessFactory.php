<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'timezone' => fake()->timezone(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
            'status' => 'active',
        ];
    }

    /**
     * Indica que el negocio debe estar inactivo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Adjunta un usuario owner al negocio después de crearlo.
     */
    public function withOwner(?User $user = null): static
    {
        return $this->afterCreating(function ($business) use ($user) {
            $owner = $user ?? User::factory()->create();
            $business->users()->attach($owner->id, ['role' => 'owner']);
        });
    }
}
