<?php

namespace App\Domain\Partners\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ortak iş/yönlendirme: müşteri + hizmet + (opsiyonel) proje bağlantılı.
 * value: komisyon matrahı kaynağı (müşteri kararıyla doldurulur).
 *
 * @property Carbon|null $archived_at
 */
class PartnerWork extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'partner_id', 'customer_id', 'project_id', 'service_type_id', 'title',
        'referral_type', 'value', 'currency_code', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
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
     * @return HasMany<PartnerCommission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(PartnerCommission::class, 'partner_work_id');
    }
}
