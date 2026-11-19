<?php

namespace App\Domain\Customers\Models;

use App\Domain\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Custom field tanımı (EAV definition tarafı, ADR-0004).
 * Tip değişikliği: mevcut değer varsa uyumsuz tip kabul edilmez —
 * Action katmanı karar verir; tanımda sessiz bozulma olmaz.
 */
class CustomFieldDefinition extends Model
{
    use Auditable;

    public const TYPE_ALLOWLIST = ['text', 'textarea', 'integer', 'decimal', 'date', 'boolean', 'select', 'multi_select'];

    protected $fillable = [
        'key', 'label', 'type', 'required', 'default_value', 'options',
        'searchable', 'filterable', 'reportable', 'visible_to_roles', 'display_order', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'options' => 'array',
            'searchable' => 'boolean',
            'filterable' => 'boolean',
            'reportable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CustomFieldValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'definition_id');
    }
}
