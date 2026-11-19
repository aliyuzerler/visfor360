<?php

namespace App\Domain\Goals\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * İlerleme snapshot'ı: hesap zamanı, kaynak görev sayıları, formül sürümü
 * ve hesaplayan aktör ile izlenebilir. needs_configuration durumunda
 * progress_value null'dır (uydurma yüzden koruması).
 *
 * @property StrategicGoal $goal
 * @property Carbon|null $calculated_at
 */
class GoalProgressSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'goal_id', 'progress_value', 'formula_version', 'source_task_count',
        'source_done_count', 'status', 'actor_id', 'actor_name', 'calculated_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'progress_value' => 'decimal:4',
            'calculated_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<StrategicGoal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(StrategicGoal::class, 'goal_id');
    }
}
