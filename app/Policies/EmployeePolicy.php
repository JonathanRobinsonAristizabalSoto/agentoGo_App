<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Business;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user, Business $business): bool
    {
        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->businesses()->where('business_id', $employee->business_id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function create(User $user, Business $business): bool
    {
        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->businesses()->where('business_id', $employee->business_id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->businesses()->where('business_id', $employee->business_id)->where('role', 'owner')->exists();
    }
}
