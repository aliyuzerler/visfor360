<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Rol: kullanıcıya atanır; permission_role üzerinden izin alır.
 * Rol matrisi müşteri onayı beklediği için seed içeriği öneridir (is_system roller).
 */
class Role extends Model
{
    protected $fillable = ['key', 'label', 'description', 'is_system'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
