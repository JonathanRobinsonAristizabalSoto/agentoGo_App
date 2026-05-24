<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Business;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user, ?Business $business = null): bool
    {
        if (! $business) {
            return (bool) $user;
        }

        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->businesses()->where('business_id', $client->business_id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function create(User $user, ?Business $business = null): bool
    {
        if (! $business) {
            return (bool) $user;
        }

        return $user->businesses()->where('business_id', $business->id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->businesses()->where('business_id', $client->business_id)->whereIn('role', ['owner', 'editor'])->exists();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->businesses()->where('business_id', $client->business_id)->where('role', 'owner')->exists();
    }
}
