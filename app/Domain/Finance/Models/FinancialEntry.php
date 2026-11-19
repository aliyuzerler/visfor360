<?php

namespace App\Domain\Finance\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Finansal kayıt: gelir/gider x tahmini/gerçekleşen. Para decimal + ISO
 * currency. Düzeltme: yeni kayıt açılır, eski kayıt parent_entry_id ile
 * zincirlenir ve korunur (geçmiş kaybolmaz).
 *
 * @property FinancialCategory|null $category
 * @property Carbon|null $approved_at
 * @property Carbon|null $archived_at
 */
class FinancialEntry extends Model
{
    use Auditable, SoftDeletes;

    public const DIRECTION_ALLOWLIST = ['income', 'expense'];

    public const TYPE_ALLOWLIST = ['estimated', 'actual'];

    public const STATUS_ALLOWLIST = ['draft', 'pending', 'approved', 'cancelled'];

    protected $fillable = [
        'direction', 'entry_type', 'category_id', 'amount', 'currency_code',
        'entry_date', 'status', 'customer_id', 'project_id', 'partner_id',
        'created_by', 'approved_by', 'approved_at', 'parent_entry_id', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'entry_date' => 'date',
            'approved_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<FinancialCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
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
     * @return BelongsTo<self, $this>
     */
    public function parentEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_entry_id');
    }
}
