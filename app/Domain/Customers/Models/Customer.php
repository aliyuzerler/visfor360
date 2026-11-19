<?php

namespace App\Domain\Customers\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Müşteri (firma) sicil kartı. Çekirdek alanlar minimum; esnek alanlar
 * Custom Fields ile yönetilir (P0: zorunlu alan listesi müşteri girdisi).
 * Silme yerine arşivleme (archived_at); geçmiş korunur.
 */
/**
 * @property Carbon|null $archived_at
 */
class Customer extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_type_id', 'name', 'trade_name', 'tax_number', 'tax_office',
        'email', 'phone', 'city', 'address', 'created_by', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CustomerType, $this>
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class)->orderByDesc('is_primary')->orderBy('name');
    }

    /**
     * @return HasMany<CustomFieldValue, $this>
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
