<?php

namespace App\Domain\Partners\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Çözüm ortağı (iç kayıt — portal/login yok, SRS sınırı).
 *
 * @property Carbon|null $archived_at
 */
class Partner extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name', 'expertise_type', 'contact_name', 'contact_email',
        'contact_phone', 'notes', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'archived_at' => 'datetime'];
    }

    /**
     * @return HasMany<PartnerAgreement, $this>
     */
    public function agreements(): HasMany
    {
        return $this->hasMany(PartnerAgreement::class)->latest('id');
    }

    /**
     * @return HasMany<PartnerWork, $this>
     */
    public function works(): HasMany
    {
        return $this->hasMany(PartnerWork::class)->latest('id');
    }
}
