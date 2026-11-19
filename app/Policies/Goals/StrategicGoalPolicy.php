<?php

namespace App\Policies\Goals;

use App\Domain\Goals\Models\GoalTaskLink;
use App\Domain\Goals\Models\StrategicGoal;
use App\Models\User;

/**
 * Hedef policy (Prompt 08): görüntüleme goals.view; hedef CRUD
 * goals.manage_settings; task-goal bağlama goals.update izni gerektirir
 * (yetkisiz kullanıcı hassas hedef detayını göremez).
 */
class StrategicGoalPolicy
{
    public function view(User $user, StrategicGoal $goal): bool
    {
        return $user->can('goals.view');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('goals.view');
    }

    public function create(User $user): bool
    {
        return $user->can('goals.manage_settings');
    }

    public function update(User $user, StrategicGoal $goal): bool
    {
        return $user->can('goals.manage_settings');
    }

    public function archive(User $user, StrategicGoal $goal): bool
    {
        return $user->can('goals.manage_settings');
    }

    public function linkTask(User $user, StrategicGoal $goal): bool
    {
        return $user->can('goals.update');
    }

    public function unlinkTask(User $user, GoalTaskLink $link): bool
    {
        return $user->can('goals.update');
    }
}
