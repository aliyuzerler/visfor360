<?php

namespace App\Domain\Partners\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komisyon: matrah + oran + hesaplanan tutar + formül sürümü izlenebilir.
 *
 * @property PartnerWork $work
 * @property Carbon|null $approved_at
 */
class PartnerCommission extends Model
{
    use Auditable;

    public const STATUS_ALLOWLIST = ['calculated', 'approved', 'paid', 'cancelled'];

    protected $fillable = [
        'partner_work_id', 'agreement_id', 'base_amount', 'rate',
        'calculated_amount', 'currency_code', 'period', 'status',
        'formula_version', 'meta', 'approved_by', 'approved_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:4',
            'rate' => 'decimal:4',
            'calculated_amount' => 'decimal:4',
            'approved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<PartnerWork, $this>
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(PartnerWork::class, 'partner_work_id');
    }

    /**
     * @return BelongsTo<PartnerAgreement, $this>
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(PartnerAgreement::class, 'agreement_id');
    }
}
