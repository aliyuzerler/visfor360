<?php

namespace App\Domain\Portal\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Portal kullanıcısı: iç staff'tan AYRI tablo/model (Prompt 15).
 * İç modüllerin Gate'leri bu modeli tanımaz; web guard'ında oturum açamaz.
 *
 * @property Collection<int, PortalMembership> $memberships
 * @property Carbon|null $last_login_at
 */
class PortalUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'portal_users';

    protected $fillable = ['name', 'email', 'password', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<PortalMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(PortalMembership::class, 'portal_user_id');
    }

    /** Aktif üyelikler (customer scope kaynağı). */
    public function activeMemberships(): HasMany
    {
        return $this->memberships()->where('is_active', true);
    }

    public function belongsToCustomer(int $customerId): bool
    {
        return $this->activeMemberships()
            ->where('customer_id', $customerId)
            ->exists();
    }
}
