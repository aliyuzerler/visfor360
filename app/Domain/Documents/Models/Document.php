<?php

namespace App\Domain\Documents\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Evrak kaydı. Yeni sürüm geldiğinde kayıt supersede edilir (silinmez);
 * önceki sürümler ve audit geçmişi korunur.
 *
 * @property Customer $customer
 * @property DocumentType $documentType
 * @property Carbon|null $valid_until
 * @property Carbon|null $superseded_at
 */
class Document extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'customer_id', 'document_type_id', 'uploaded_by', 'original_name',
        'storage_key', 'mime_type', 'size_bytes', 'valid_until', 'superseded_at', 'superseded_by',
    ];

    protected function casts(): array
    {
        return ['valid_until' => 'datetime', 'superseded_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by');
    }

    /** Geçerlilik süresi dolmuş mu? */
    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    /** Geçerlilik süresi yaklaşıyor mu (uyarı penceresi)? */
    public function isExpiringSoon(int $warnDays): bool
    {
        return $this->valid_until !== null
            && ! $this->isExpired()
            && $this->valid_until->lessThanOrEqualTo(now()->addDays($warnDays));
    }
}
