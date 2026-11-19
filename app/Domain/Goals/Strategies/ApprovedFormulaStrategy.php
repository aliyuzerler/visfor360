<?php

namespace App\Domain\Goals\Strategies;

use App\Domain\Goals\Models\StrategicGoal;

/**
 * Onaylı formül stratejisi. DEMO: yalnızca calculation_config.approved=true
 * VE method='linked_tasks_ratio' iken çalışır — bu kombinasyon test
 * fixture'ları ile doğrulanır; müşteri formülü gelene kadar üretimde
 * hiçbir hedef bu konfigürasyona sahip değildir.
 */
class ApprovedFormulaStrategy implements GoalCalculationStrategy
{
    public function supports(StrategicGoal $goal): bool
    {
        return $goal->calculation_method === 'linked_tasks_ratio'
            && ($goal->calculation_config['approved'] ?? false) === true;
    }

    public function version(): string
    {
        return 'linked_tasks_ratio.v1';
    }

    public function calculate(StrategicGoal $goal): array
    {
        $links = $goal->taskLinks()->with('task')->get();

        $taskCount = $links->count();
        $doneCount = $links->filter(fn ($link) => $link->task?->status === 'done')->count();

        $progress = $taskCount > 0 ? round(($doneCount / $taskCount) * 100, 2) : null;

        return [
            'progress' => $progress,
            'source_task_count' => $taskCount,
            'source_done_count' => $doneCount,
            'status' => $progress === null ? 'needs_configuration' : 'calculated',
            'meta' => ['formula' => 'linked_tasks_ratio', 'note' => 'DEMO formül — müşteri formülü ile değiştirilir'],
        ];
    }
}
