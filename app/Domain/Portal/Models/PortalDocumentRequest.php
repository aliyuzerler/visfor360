<?php

namespace App\Domain\Portal\Models;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Portal belge talebi: iç kullanıcı tarafından açılır; müşteri yalnız
 * kendi firmasının talebine dosya yükler. İnceleme/onay müşteri kararı.
 *
 * @property Carbon|null $uploaded_at
 */
class PortalDocumentRequest extends Model
{
    protected $fillable = [
        'customer_id', 'document_type_id', 'requested_by', 'note',
        'status', 'document_id', 'uploaded_by', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<DocumentType, $this>
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}
