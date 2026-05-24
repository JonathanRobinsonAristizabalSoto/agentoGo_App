<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\Business;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user, ?Business $business = null): bool
    {
        if (! $business) {
            return (bool) $user;
        }

        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->businesses()->where('business_id', $reservation->business_id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function create(User $user, ?Business $business = null): bool
    {
        if (! $business) {
            return (bool) $user;
        }

        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->businesses()->where('business_id', $reservation->business_id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->businesses()->where('business_id', $reservation->business_id)->where('role', 'owner')->exists();
    }
}
