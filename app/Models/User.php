<?php

namespace App\Models;

use App\Domain\Audit\Auditable;
use App\Domain\Identity\PermissionRegistry;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Auditable;

    /**
     * @var array<string, bool>|null istek boyunca izin önbelleği
     */
    protected ?array $permissionCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return HasMany<DepartmentUser, $this>
     */
    public function departmentMemberships(): HasMany
    {
        return $this->hasMany(DepartmentUser::class);
    }

    /**
     * @return BelongsToMany<Department, $this>
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_user')
            ->withPivot('is_manager', 'joined_at');
    }

    /**
     * Kullanıcının üye olduğu departmanların id listesi.
     *
     * @return list<int>
     */
    public function departmentIds(): array
    {
        return $this->departmentMemberships()->pluck('department_id')->all();
    }

    /**
     * Kullanıcının yöneticisi olduğu departmanların id listesi.
     *
     * @return list<int>
     */
    public function managedDepartmentIds(): array
    {
        return $this->departmentMemberships()->where('is_manager', true)->pluck('department_id')->all();
    }

    public function hasRole(string $key): bool
    {
        return $this->roles()->where('key', $key)->exists();
    }

    /**
     * Sunucu taraflı izin kontrolü; Gate tanımları bunu kullanır.
     * UI görünürlüğü (nav filtreleme) yalnızca yardımcıdır.
     */
    public function hasPermission(string $key): bool
    {
        $this->permissionCache ??= $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('key')
            ->unique()
            ->all();

        return in_array($key, $this->permissionCache, true);
    }

    /**
     * @return list<string>
     */
    public function permissionKeys(): array
    {
        $this->permissionCache ??= $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('key')
            ->unique()
            ->all();

        return $this->permissionCache;
    }

    public function flushPermissionCache(): void
    {
        $this->permissionCache = null;
    }
}
