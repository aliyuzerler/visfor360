<?php

namespace App\Policies\Identity;

use App\Models\Role;
use App\Models\User;

/**
 * Rol yönetimi policy. Sistem rolleri (is_system) arşivlenemez.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.update');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function archive(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $user->can('roles.archive');
    }
}
