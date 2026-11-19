<?php

namespace App\Policies\Customers;

use App\Domain\Customers\Models\Customer;
use App\Models\User;

/**
 * Müşteri policy (Prompt 04).
 * Kayıt kapsamı: ilk sürümde kurum geneli (satış sorumlusuna atanmış müşteri
 * kapsamı P0-07 müşteri kararı ile daraltılabilir); izinler server-side.
 */
class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('customers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('customers.update');
    }

    public function archive(User $user, Customer $customer): bool
    {
        return $user->can('customers.archive');
    }

    public function export(User $user): bool
    {
        return $user->can('customers.export');
    }
}
