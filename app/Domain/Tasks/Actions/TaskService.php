<?php

namespace App\Domain\Tasks\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Tasks\Models\Task;
use App\Domain\Tasks\Models\TaskAssignment;
use App\Domain\Tasks\Models\TaskHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Görev iş kuralları (Prompt 07): durum değişikliği, atama ve tarih
 * güncellemeleri history + audit'e yazılır; gecikme hesabı done/cancelled
 * hariç tutar. OKR ilerlemesi otomatik artırılmaz (SRS sınırı).
 */
final class TaskService
{
    public function changeStatus(Task $task, string $newStatus, ?User $actor): Task
    {
        if (! in_array($newStatus, Task::STATUS_ALLOWLIST, true)) {
            throw ValidationException::withMessages(['status' => __('Geçersiz görev durumu.')]);
        }

        if ($task->status === $newStatus) {
            return $task;
        }

        return DB::transaction(function () use ($task, $newStatus, $actor): Task {
            $old = $task->status;

            $task->update([
                'status' => $newStatus,
                'completed_at' => $newStatus === 'done' ? now() : null,
            ]);

            TaskHistory::create([
                'task_id' => $task->id,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'field' => 'status',
                'old_value' => $old,
                'new_value' => $newStatus,
                'changed_at' => now(),
            ]);

            app(AuditLogger::class)->log('status_changed', $task, ['from' => $old], ['to' => $newStatus]);

            return $task;
        });
    }

    /**
     * Görevi kullanıcıya ata (yeniden atama destekli; history yazılır).
     */
    public function assign(Task $task, User $assignee, ?User $actor): Task
    {
        return DB::transaction(function () use ($task, $assignee, $actor): Task {
            $previous = TaskAssignment::query()
                ->where('task_id', $task->id)
                ->where('is_primary', true)
                ->first();

            TaskAssignment::query()->where('task_id', $task->id)->delete();

            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $assignee->id,
                'is_primary' => true,
                'assigned_by' => $actor?->id,
                'assigned_at' => now(),
            ]);

            TaskHistory::create([
                'task_id' => $task->id,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'field' => 'assignment',
                'old_value' => $previous?->user_id !== null ? (string) $previous->user_id : null,
                'new_value' => (string) $assignee->id,
                'changed_at' => now(),
            ]);

            app(AuditLogger::class)->log('assignment_changed', $task, ['from' => $previous?->user_id], ['to' => $assignee->id]);

            return $task;
        });
    }

    /**
     * @param  bool  $forceHistory  controller güncellemesi sonrası çağrılıyorsa
     *                              değer zaten değişmiştir; history zorla yazılır.
     */
    public function updateDueDate(Task $task, ?string $dueDate, ?User $actor, bool $forceHistory = false, ?string $oldForced = null): Task
    {
        $old = $oldForced ?? $task->due_date?->toDateString();

        if ($old !== $dueDate) {
            $task->update(['due_date' => $dueDate]);
        }

        if ($forceHistory || $old !== $dueDate) {
            TaskHistory::create([
                'task_id' => $task->id,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'field' => 'due_date',
                'old_value' => $old,
                'new_value' => $dueDate,
                'changed_at' => now(),
            ]);

            app(AuditLogger::class)->log('due_date_changed', $task, ['from' => $old], ['to' => $dueDate]);
        }

        return $task;
    }
}
