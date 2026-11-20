<?php

namespace App\Domain\Portal\Models;

use App\Domain\Customers\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Portal üyeliği: kullanıcı ↔ müşteri. is_active=false → anında erişim kesilir.
 *
 * @property PortalUser $user
 */
class PortalMembership extends Model
{
    protected $fillable = ['portal_user_id', 'customer_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<PortalUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class, 'portal_user_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
