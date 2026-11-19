<?php

namespace App\Policies\Tasks;

use App\Domain\Tasks\Models\Task;
use App\Models\User;

/**
 * Görev policy (Prompt 07):
 * - viewAny: tasks.view (modül erişimi; "benim/ekip" filtresi UI'da)
 * - view: tasks.view
 * - update/transition/assign: tasks.update İZİNİ ya da görevin sorumlusu
 *   (sorumlu kendi görevini ilerletebilir — ADR-0008 notu)
 * - archive: tasks.archive
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') || $this->isAssignee($user, $task);
    }

    public function transition(User $user, Task $task): bool
    {
        return $user->can('tasks.update') || $this->isAssignee($user, $task);
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->can('tasks.update');
    }

    public function archive(User $user, Task $task): bool
    {
        return $user->can('tasks.archive');
    }

    private function isAssignee(User $user, Task $task): bool
    {
        return $task->assignments()->where('user_id', $user->id)->exists();
    }
}
