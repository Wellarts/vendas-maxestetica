<?php

namespace App\Policies;

use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FornecedorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('View Fornecedores');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Fornecedor  $fornecedor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('Create Fornecedores');
    }


    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Fornecedor  $fornecedor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Fornecedor $fornecedor)
    {
        return $user->hasPermissionTo('Edit Fornecedores');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Fornecedor  $fornecedor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Fornecedor $fornecedor)
    {
        return $user->hasPermissionTo('Delete Fornecedores');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Fornecedor  $fornecedor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Fornecedor  $fornecedor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Fornecedor $fornecedor)
    {
        //
    }
}
