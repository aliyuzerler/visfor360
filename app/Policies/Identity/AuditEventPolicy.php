<?php

namespace App\Policies\Identity;

use App\Models\User;

/**
 * Audit log yalnızca okunabilir (append-only); görüntüleme ayrı izne bağlıdır.
 */
class AuditEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.view');
    }
}
