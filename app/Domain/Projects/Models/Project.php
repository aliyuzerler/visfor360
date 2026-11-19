<?php

namespace App\Domain\Projects\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Proje: müşteri + hizmet + departman + sorumlu + mevcut aşama.
 * Silme yerine arşivleme; aşama geçişleri history tablosuna yazılır.
 *
 * @property PipelineStage|null $currentStage
 * @property Carbon|null $archived_at
 */
class Project extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'customer_id', 'service_type_id', 'department_id',
        'responsible_user_id', 'pipeline_stage_id', 'start_date', 'due_date', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    /**
     * @return HasMany<ProjectStageHistory, $this>
     */
    public function stageHistory(): HasMany
    {
        return $this->hasMany(ProjectStageHistory::class)->latest('transitioned_at');
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
