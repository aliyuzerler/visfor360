<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Gelir/gider kategorisi. İçerik müşteri kararı; seed yalnız
 * is_example=true örnek set verir.
 */
class FinancialCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['key', 'label', 'direction', 'is_example', 'is_active'];

    protected function casts(): array
    {
        return ['is_example' => 'boolean', 'is_active' => 'boolean'];
    }
}
