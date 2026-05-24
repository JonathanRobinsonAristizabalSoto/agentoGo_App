<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'department_id' => Department::factory(),
            'employee_id' => Employee::factory(),
            'client_id' => Client::factory(),
            'created_by' => User::factory(),
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'ends_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status' => 'scheduled',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }
}