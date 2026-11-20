<?php

namespace App\Policies\Partners;

use App\Domain\Partners\Models\Partner;
use App\Models\User;

/**
 * Çözüm ortağı policy (Prompt 14): partners izinleri Registry'de
 * partners modülü olarak tanımlı.
 */
class PartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('partners.view');
    }

    public function view(User $user, Partner $partner): bool
    {
        return $user->can('partners.view');
    }

    public function create(User $user): bool
    {
        return $user->can('partners.create');
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->can('partners.update');
    }

    public function archive(User $user, Partner $partner): bool
    {
        return $user->can('partners.archive');
    }
}
