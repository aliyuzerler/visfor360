<?php

namespace App\Policies\Checkups;

use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Models\User;

/**
 * Check-Up policy (Prompt 11, madde 8): görüntüleme checkups.view;
 * oturum açma/ücretlendirme checkups.create; yönetim checkups.manage_settings.
 */
class CheckupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('checkups.view');
    }

    public function view(User $user, CheckupSession $session): bool
    {
        return $user->can('checkups.view');
    }

    public function viewTemplate(User $user, CheckupTemplate $template): bool
    {
        return $user->can('checkups.view');
    }

    public function create(User $user): bool
    {
        return $user->can('checkups.create');
    }

    public function manage(User $user): bool
    {
        return $user->can('checkups.manage_settings');
    }
}
