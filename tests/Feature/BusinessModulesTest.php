<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_modules_de_negocio_crean_relaciones_correctas(): void
    {
        $business = Business::factory()->create();
        $creator = User::factory()->create();

        $department = Department::factory()->create([
            'business_id' => $business->id,
        ]);

        $employee = Employee::factory()->create([
            'business_id' => $business->id,
            'department_id' => $department->id,
        ]);

        $client = Client::factory()->create([
            'business_id' => $business->id,
        ]);

        $reservation = Reservation::factory()->create([
            'business_id' => $business->id,
            'department_id' => $department->id,
            'employee_id' => $employee->id,
            'client_id' => $client->id,
            'created_by' => $creator->id,
        ]);

        $this->assertCount(1, $business->departments);
        $this->assertCount(1, $business->employees);
        $this->assertCount(1, $business->clients);
        $this->assertCount(1, $business->reservations);

        $this->assertSame($business->id, $department->business_id);
        $this->assertSame($department->id, $employee->department_id);
        $this->assertSame($client->id, $reservation->client_id);
        $this->assertSame($creator->id, $reservation->created_by);
        $this->assertSame($employee->id, $reservation->employee_id);
    }
}