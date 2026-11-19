<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * İzin: "module.action" formatında anahtar (örn. customers.view).
 * Kayıt kapsamı (kurum/departman/müşteri/proje/kendi görevi) izinden ayrı,
 * Policy katmanında modül bazlı modellenir.
 */
class Permission extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
