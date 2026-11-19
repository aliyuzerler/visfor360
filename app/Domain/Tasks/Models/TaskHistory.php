<?php

namespace App\Domain\Tasks\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Görev değişiklik geçmişi: durum, atama, tarih değişiklikleri.
 *
 * @property Task $task
 * @property Carbon $changed_at
 */
class TaskHistory extends Model
{
    protected $table = 'task_history';

    public $timestamps = false;

    protected $fillable = [
        'task_id', 'actor_id', 'actor_name', 'field', 'old_value', 'new_value',
        'changed_at', 'meta',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime', 'meta' => 'array'];
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
