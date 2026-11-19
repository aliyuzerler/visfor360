<?php

namespace App\Domain\Projects\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aşama geçiş geçmişi: actor, old stage, new stage, timestamp (audit).
 *
 * @property PipelineStage $toStage
 * @property PipelineStage|null $fromStage
 * @property Carbon $transitioned_at
 */
class ProjectStageHistory extends Model
{
    protected $table = 'project_stage_history';

    protected $fillable = [
        'project_id', 'from_stage_id', 'to_stage_id',
        'actor_id', 'actor_name', 'transitioned_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'transitioned_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function toStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'to_stage_id');
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'from_stage_id');
    }
}
