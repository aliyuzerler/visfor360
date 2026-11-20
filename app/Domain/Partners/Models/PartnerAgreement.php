<?php

namespace App\Domain\Partners\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ortak anlaşması: komisyon formülü YALNIZ approved iken etkindir.
 * commission_type seti ÖRNEK (ADR-0013); KDV/matrah/vade uydurulmaz.
 *
 * @property Carbon|null $approved_at
 * @property Carbon|null $archived_at
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_until
 */
class PartnerAgreement extends Model
{
    use Auditable, SoftDeletes;

    public const TYPE_ALLOWLIST = ['fixed_amount', 'percent_of_value'];

    public const STATUS_ALLOWLIST = ['draft', 'approved'];

    protected $fillable = [
        'partner_id', 'commission_type', 'commission_rate', 'currency_code',
        'status', 'valid_from', 'valid_until', 'approved_by', 'approved_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
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

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_at !== null;
    }
}
