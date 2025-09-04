<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientePolicy
{
    use HandlesAuthorization;


    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('View Clientes');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Cliente $cliente)
    {
        //
    }


    public function create(User $user)
    {
        return $user->hasPermissionTo('Create Clientes');
    }


    public function update(User $user, Cliente $cliente)
    {
        return $user->hasPermissionTo('Edit Clientes');
    }


    public function delete(User $user, Cliente $cliente)
    {
        return $user->hasPermissionTo('View Clientes');
    }


    public function restore(User $user, Cliente $cliente)
    {
        //
    }


    public function forceDelete(User $user, Cliente $cliente)
    {
        //
    }
}
