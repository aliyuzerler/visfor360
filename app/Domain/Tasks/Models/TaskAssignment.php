<?php

namespace App\Domain\Tasks\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Görev ataması. Varsayılan tek atama (is_primary); çoklu atama müşteri
 * kararı (ADR-0008) ile açılırsa tablo genişletilebilir.
 *
 * @property Task $task
 * @property User $user
 */
class TaskAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['task_id', 'user_id', 'is_primary', 'assigned_by', 'assigned_at'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
