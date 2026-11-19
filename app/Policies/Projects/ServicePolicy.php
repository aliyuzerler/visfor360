<?php

namespace App\Policies\Projects;

use App\Models\User;

/**
 * Hizmet/pipeline yönetimi ayrı izin: services.manage (tanimlar müşteri
 * kararı ile kalıcılaşır; örnek şablonlar değiştirilebilir).
 */
class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('services.manage');
    }
}
