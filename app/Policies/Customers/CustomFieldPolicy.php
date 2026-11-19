<?php

namespace App\Policies\Customers;

use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Models\User;

/**
 * Custom field tanım yönetimi ayrı izne bağlıdır; değer yazma
 * customers.update iznine tabidir.
 */
class CustomFieldPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('customers.manage_settings');
    }

    public function create(User $user): bool
    {
        return $user->can('customers.manage_settings');
    }

    public function update(User $user, CustomFieldDefinition $definition): bool
    {
        return $user->can('customers.manage_settings');
    }

    public function archive(User $user, CustomFieldDefinition $definition): bool
    {
        return $user->can('customers.manage_settings');
    }
}
