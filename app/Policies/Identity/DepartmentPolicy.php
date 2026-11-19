<?php

namespace App\Policies\Identity;

use App\Models\Department;
use App\Models\User;

/**
 * Departman policy: izin + kayıt kapsamı sunucu tarafında uygulanır.
 */
class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('departments.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can('departments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('departments.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('departments.update');
    }

    public function archive(User $user, Department $department): bool
    {
        return $user->can('departments.archive');
    }
}
