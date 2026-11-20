<?php

namespace App\Domain\Opportunities\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fırsat: Check-Up tespitinin insan incelemesinden geçmiş kaydı.
 * Status seti ÖRNEK (ADR-0011); karar mutabakat notu olmadan geçerli olmaz.
 * Otomatik proje/görev dönüşümü YOK (Prompt 13'te onaylı dönüşüm akışı).
 *
 * @property Carbon|null $archived_at
 * @property Carbon|null $decided_at
 * @property Carbon|null $target_date
 * @property Carbon|null $converted_at
 */
class Opportunity extends Model
{
    use Auditable, SoftDeletes;

    /**
     * ÖRNEK statü seti — müşteri onayı bekliyor (ADR-0011).
     * Motor bu setle çalışır; müşteri seti gelince kalıcılaşır.
     */
    public const STATUS_EXAMPLE_SET = ['review_pending', 'customer_discussion', 'approved', 'rejected', 'deferred'];

    protected $fillable = [
        'customer_id', 'service_key', 'title', 'source_session_id', 'roadmap_item_id',
        'detection_reason', 'status', 'responsible_user_id', 'estimated_value',
        'estimated_currency', 'target_date', 'discussion_note', 'decision_note',
        'decided_by', 'decided_at', 'is_duplicate', 'duplicate_of_id', 'created_by',
        'converted_project_id', 'converted_at', 'converted_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:4',
            'target_date' => 'date',
            'is_duplicate' => 'boolean',
            'decided_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<OpportunityHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(OpportunityHistory::class)->latest('changed_at');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Dönüşümün ürettiği proje (1:1 — çoklu iş müşteri kararı, ADR-0012).
     *
     * @return BelongsTo<Project, $this>
     */
    public function convertedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'converted_project_id');
    }

    public function hasApprovedAgreement(): bool
    {
        return $this->status === 'approved'
            && $this->discussion_note !== null
            && trim($this->discussion_note) !== '';
    }
}
