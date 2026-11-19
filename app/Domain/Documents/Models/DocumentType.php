<?php

namespace App\Domain\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Evrak tipi: zorunluluk ve uyarı periyodu yönetilebilir referans veri
 * (içerik müşteri P0 kararı ile kalıcılaşır; seed yalnızca örnek).
 */
class DocumentType extends Model
{
    use SoftDeletes;

    protected $fillable = ['key', 'label', 'required', 'validity_warn_days', 'is_active', 'description'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'is_active' => 'boolean'];
    }
}
