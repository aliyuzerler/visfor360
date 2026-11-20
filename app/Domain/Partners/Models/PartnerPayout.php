<?php

namespace App\Domain\Partners\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Hakediş ödemesi: komisyonların toplandığı payout kaydı; durum
 * değişimleri auditli (Auditable). Banka entegrasyonu YOK.
 *
 * @property Carbon|null $paid_at
 */
class PartnerPayout extends Model
{
    use Auditable;

    public const STATUS_ALLOWLIST = ['draft', 'paid'];

    protected $fillable = [
        'partner_id', 'amount', 'currency_code', 'status', 'paid_at', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<PartnerPayoutCommission, $this>
     */
    public function commissionLinks(): HasMany
    {
        return $this->hasMany(PartnerPayoutCommission::class, 'payout_id');
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
