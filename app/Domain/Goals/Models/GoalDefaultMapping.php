<?php

namespace App\Domain\Goals\Models;

use App\Domain\Projects\Models\ServiceType;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Departman/hizmet → varsayılan hedef kuralı. Görev oluşturulurken eşleşen
 * hizmet/departman varsa bu hedef önerilir (link yine yetkili kullanıcıyla).
 *
 * @property StrategicGoal $goal
 */
class GoalDefaultMapping extends Model
{
    protected $fillable = ['goal_id', 'service_type_id', 'department_id'];

    /**
     * @return BelongsTo<StrategicGoal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(StrategicGoal::class, 'goal_id');
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

    /** Görevin hizmet/departmanına uyan varsayılan hedefi bul. */
    public static function resolveFor(?int $serviceTypeId, ?int $departmentId): ?self
    {
        return self::query()
            ->where(function ($q) use ($serviceTypeId, $departmentId): void {
                $q->where(function ($q2) use ($serviceTypeId): void {
                    $q2->where('service_type_id', $serviceTypeId)->orWhereNull('service_type_id');
                })->where(function ($q2) use ($departmentId): void {
                    $q2->where('department_id', $departmentId)->orWhereNull('department_id');
                });
            })
            ->orderByRaw('service_type_id IS NULL, department_id IS NULL') // en spesifik önce
            ->first();
    }
}
