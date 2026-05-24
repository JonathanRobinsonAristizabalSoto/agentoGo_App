<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Sembrar la base de datos con registros de ejemplo.
     */
    public function run(): void
    {
        // Crear usuarios de ejemplo
        $user1 = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => 'María García',
            'email' => 'maria@example.com',
        ]);

        // Crear negocios de ejemplo
        $business1 = Business::factory()->create([
            'name' => 'Tech Solutions',
            'slug' => 'tech-solutions',
            'timezone' => 'America/Bogota',
            'primary_color' => '#007BFF',
            'secondary_color' => '#6C757D',
        ]);

        $business2 = Business::factory()->create([
            'name' => 'Digital Marketing Pro',
            'slug' => 'digital-marketing-pro',
            'timezone' => 'America/Mexico_City',
            'primary_color' => '#FF6B6B',
            'secondary_color' => '#4ECDC4',
        ]);

        $business3 = Business::factory()->create([
            'name' => 'E-Commerce Plus',
            'slug' => 'ecommerce-plus',
            'timezone' => 'America/New_York',
            'primary_color' => '#FFB347',
            'secondary_color' => '#87CEEB',
        ]);

        // Asociar usuarios con negocios
        $user1->businesses()->attach($business1->id, ['role' => 'owner']);
        $user1->businesses()->attach($business2->id, ['role' => 'editor']);

        $user2->businesses()->attach($business2->id, ['role' => 'owner']);
        $user2->businesses()->attach($business3->id, ['role' => 'owner']);

        // Crear más usuarios y negocios de forma masiva
        User::factory(5)->create()->each(function (User $user) {
            Business::factory(2)->create()->each(function (Business $business) use ($user) {
                $user->businesses()->attach($business->id, ['role' => 'owner']);
            });
        });
    }
}
