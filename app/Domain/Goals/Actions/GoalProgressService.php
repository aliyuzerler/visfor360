<?php

namespace App\Domain\Goals\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Goals\Models\GoalProgressSnapshot;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Goals\Strategies\ApprovedFormulaStrategy;
use App\Domain\Goals\Strategies\GoalCalculationStrategy;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * İlerleme motoru: strategy interface üzerinden hesap; her hesap snapshot
 * (zaman, kaynak görevler, formül sürümü) + audit ile izlenebilir.
 * Formül onaylı değilse snapshot needs_configuration olarak yazılır ve
 * progress_value null kalır.
 */
final class GoalProgressService
{
    private GoalCalculationStrategy $strategy;

    public function __construct(?GoalCalculationStrategy $strategy = null)
    {
        $this->strategy = $strategy ?? new ApprovedFormulaStrategy;
    }

    public function snapshotFor(StrategicGoal $goal, ?User $actor = null): GoalProgressSnapshot
    {
        return DB::transaction(function () use ($goal, $actor): GoalProgressSnapshot {
            if ($this->strategy->supports($goal)) {
                $result = $this->strategy->calculate($goal);
            } else {
                $result = [
                    'progress' => null,
                    'source_task_count' => $goal->taskLinks()->count(),
                    'source_done_count' => 0,
                    'status' => 'needs_configuration',
                    'meta' => ['reason' => 'İlerleme formülü müşteri onayı bekliyor'],
                ];
            }

            $snapshot = GoalProgressSnapshot::create([
                'goal_id' => $goal->id,
                'progress_value' => $result['progress'],
                'formula_version' => $this->strategy->supports($goal) ? $this->strategy->version() : null,
                'source_task_count' => $result['source_task_count'],
                'source_done_count' => $result['source_done_count'],
                'status' => $result['status'],
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'calculated_at' => now(),
                'meta' => $result['meta'],
            ]);

            app(AuditLogger::class)->log('progress_snapshot', $goal, null, [
                'status' => $result['status'],
                'progress' => $result['progress'],
                'formula_version' => $snapshot->formula_version,
            ]);

            return $snapshot;
        });
    }
}
