<?php

namespace App\Domain\Goals\Models;

use App\Domain\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Görev-hedef bağlantısı. Ağırlık müşteri kararı (ADR-0009); null iken
 * ilerleme hesabına katkı eşit varsayılmaz — hesap yapılmaz.
 *
 * @property StrategicGoal $goal
 * @property Task|null $task
 */
class GoalTaskLink extends Model
{
    protected $fillable = ['goal_id', 'task_id', 'weight', 'linked_by'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2'];
    }

    /**
     * @return BelongsTo<StrategicGoal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(StrategicGoal::class, 'goal_id');
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
