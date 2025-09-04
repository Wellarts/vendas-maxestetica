<?php

namespace App\Policies;

use App\Models\PDV;
use App\Models\User;

class PDVPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('View PDV');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PDV $pDV)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Create PDV');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PDV $pDV): bool
    {
        return $user->hasPermissionTo('Edit PDV');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PDV $pDV): bool
    {
        return $user->hasPermissionTo('Delete PDV');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PDV $pDV)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PDV $pDV)
    {
        //
    }
}
