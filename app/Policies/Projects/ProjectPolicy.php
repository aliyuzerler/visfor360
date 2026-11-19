<?php

namespace App\Policies\Projects;

use App\Domain\Projects\Models\Project;
use App\Models\User;

/**
 * Proje policy (Prompt 06). Kapsam: kurum geneli; sorumlu bazlı daraltma
 * müşteri kararı (P0) ile eklenebilir.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view');
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update');
    }

    public function transitionStage(User $user, Project $project): bool
    {
        return $user->can('projects.update');
    }

    public function archive(User $user, Project $project): bool
    {
        return $user->can('projects.archive');
    }
}
