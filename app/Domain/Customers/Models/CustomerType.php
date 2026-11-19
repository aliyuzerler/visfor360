<?php

namespace App\Domain\Customers\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Müşteri tipi: yönetilebilir referans veri. İçerikleri müşteri kararı
 * (P0) ile kalıcılaştırılır; seed yalnızca örnek set verir.
 */
class CustomerType extends Model
{
    protected $fillable = ['key', 'label', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
