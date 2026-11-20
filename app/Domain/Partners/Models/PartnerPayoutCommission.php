<?php

namespace App\Domain\Partners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payout ↔ komisyon bağlantısı.
 *
 * @property PartnerPayout $payout
 * @property PartnerCommission $commission
 */
class PartnerPayoutCommission extends Model
{
    protected $fillable = ['payout_id', 'commission_id'];

    /**
     * @return BelongsTo<PartnerPayout, $this>
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(PartnerPayout::class, 'payout_id');
    }

    /**
     * @return BelongsTo<PartnerCommission, $this>
     */
    public function commission(): BelongsTo
    {
        return $this->belongsTo(PartnerCommission::class, 'commission_id');
    }
}
