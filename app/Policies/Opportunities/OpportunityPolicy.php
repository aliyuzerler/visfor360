<?php

namespace App\Policies\Opportunities;

use App\Domain\Opportunities\Models\Opportunity;
use App\Models\User;

/**
 * Fırsat policy (Prompt 12): karar (onay/red/erteleme) ayrı izin
 * opportunities.approve; güncelleme opportunities.update; görüntüleme view.
 */
class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('opportunities.view');
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.view');
    }

    public function create(User $user): bool
    {
        return $user->can('opportunities.create');
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.update');
    }

    public function decide(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.approve');
    }

    public function archive(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.archive');
    }

    /** Dönüşüm: onay yetkisi + proje oluşturma yetkisi birlikte gerekir. */
    public function convert(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.approve') && $user->can('projects.create');
    }
}
