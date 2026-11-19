<?php

namespace App\Domain\Tasks\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Görev: müşteri/proje/hizmet/departman bağlantılı ekip iş kalemi.
 * Durumlar: open, in_progress, done, cancelled. Gecikme hesabında
 * done/cancelled hariç tutulur.
 *
 * @property Collection<int, TaskAssignment> $assignments
 * @property Carbon|null $archived_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $due_date
 * @property Carbon|null $start_date
 */
class Task extends Model
{
    use Auditable, SoftDeletes;

    public const STATUS_ALLOWLIST = ['open', 'in_progress', 'done', 'cancelled'];

    public const PRIORITY_ALLOWLIST = ['low', 'medium', 'high', 'urgent'];

    protected $fillable = [
        'title', 'description', 'status', 'priority', 'customer_id', 'project_id',
        'service_type_id', 'department_id', 'created_by', 'start_date', 'due_date',
        'completed_at', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<TaskAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * @return HasMany<TaskHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest('changed_at');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && ! in_array($this->status, ['done', 'cancelled'], true)
            && $this->due_date->isPast();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
