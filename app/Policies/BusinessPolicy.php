<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    /**
     * Determina si el usuario puede ver el negocio.
     * Permitido: owner, editor, viewer
     */
    public function view(User $user, Business $business): bool
    {
        return $user->businesses()
            ->where('business_id', $business->id)
            ->whereIn('role', ['owner', 'editor', 'viewer'])
            ->exists();
    }

    /**
     * Determina si el usuario puede actualizar el negocio.
     * Permitido: owner, editor
     */
    public function update(User $user, Business $business): bool
    {
        return $user->businesses()
            ->where('business_id', $business->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Determina si el usuario puede eliminar el negocio.
     * Permitido: solo owner
     */
    public function delete(User $user, Business $business): bool
    {
        return $user->businesses()
            ->where('business_id', $business->id)
            ->where('role', 'owner')
            ->exists();
    }
}
