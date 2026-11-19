<?php

namespace App\Domain\Goals\Models;

use App\Domain\Audit\Auditable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ana hedef (12 slot: goal-01..goal-12). slot_key sabittir; içerik müşteri
 * kararı. calculation_method null → ilerleme "needs-configuration".
 *
 * @property Carbon|null $archived_at
 * @property array<string, mixed>|null $calculation_config
 * @property User|null $owner
 */
class StrategicGoal extends Model
{
    use Auditable, SoftDeletes;

    public const SLOT_COUNT = 12;

    protected $fillable = [
        'slot_key', 'label', 'description', 'owner_user_id', 'period',
        'baseline', 'target', 'unit', 'calculation_method', 'calculation_config',
        'is_example', 'is_active', 'sort_order', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'baseline' => 'decimal:4',
            'target' => 'decimal:4',
            'calculation_config' => 'array',
            'is_example' => 'boolean',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<GoalTaskLink, $this>
     */
    public function taskLinks(): HasMany
    {
        return $this->hasMany(GoalTaskLink::class, 'goal_id');
    }

    /**
     * @return HasMany<GoalProgressSnapshot, $this>
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(GoalProgressSnapshot::class, 'goal_id')->latest('calculated_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** İlerleme hesabı konfigüre edilmiş mi? */
    public function hasApprovedCalculation(): bool
    {
        return $this->calculation_method !== null && ($this->calculation_config['approved'] ?? false) === true;
    }

    /** Placeholder (müşteri verisi bekleyen örnek hedef) mi? */
    public function isPlaceholder(): bool
    {
        return $this->is_example || $this->label === null;
    }
}
