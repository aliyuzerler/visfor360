<?php

namespace App\Policies\Finance;

use App\Domain\Finance\Models\FinancialEntry;
use App\Models\User;

/**
 * Finans policy (Prompt 09): görüntüleme/ekleme/düzeltme/onay/export
 * AYRI izinler; approve yalnız finance.approve; export finance.export.
 */
class FinancialEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('finance.view');
    }

    public function view(User $user, FinancialEntry $entry): bool
    {
        return $user->can('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('finance.create');
    }

    public function update(User $user, FinancialEntry $entry): bool
    {
        return $user->can('finance.update');
    }

    public function approve(User $user, FinancialEntry $entry): bool
    {
        return $user->can('finance.approve');
    }

    public function export(User $user): bool
    {
        return $user->can('finance.export');
    }
}
