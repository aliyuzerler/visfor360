<?php

namespace App\Domain\Goals\Strategies;

use App\Domain\Goals\Models\StrategicGoal;

/**
 * İlerleme hesaplama soyutlaması (Prompt 08). Yalnızca onaylı formül +
 * test fixture verildiğinde hesap etkinleşir; aksi halde uydurma yüzden
 * üretilmez (SRS sınırı).
 */
interface GoalCalculationStrategy
{
    public function supports(StrategicGoal $goal): bool;

    /** Formül sürümü — snapshot izlenebilirliği için. */
    public function version(): string;

    /**
     * @return array{progress: float|null, source_task_count: int, source_done_count: int, status: string, meta: array<string, mixed>}
     */
    public function calculate(StrategicGoal $goal): array;
}
