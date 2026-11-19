<?php

namespace App\Domain\Customers\Models;

use App\Domain\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Müşteri yetkilisi. Bir firmada birden fazla yetkili desteklenir
 * (öneri — müşteri teyidine açık).
 */
/**
 * @property Customer $customer
 */
class Contact extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['customer_id', 'name', 'title', 'email', 'phone', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
