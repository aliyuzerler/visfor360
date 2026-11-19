<?php

namespace App\Domain\Projects\Models;

use App\Domain\Audit\Auditable;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Hizmet kataloğu kalemi; kendi pipeline şablonuna bağlıdır (her hizmetin
 * kendi Kanban'ı — tek Kanban yasak). Departman bağlantısı opsiyonel.
 *
 * @property Pipeline $pipeline
 */
class ServiceType extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['key', 'label', 'description', 'pipeline_id', 'department_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
