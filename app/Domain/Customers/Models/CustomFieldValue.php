<?php

namespace App\Domain\Customers\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Custom field değeri — typed kolonlarda saklanır:
 * text/textarea/select → value_text; integer/decimal → value_number;
 * date → value_date; boolean → value_boolean; multi_select → value_json.
 */
/**
 * @property CustomFieldDefinition $definition
 * @property Carbon|null $value_date
 */
class CustomFieldValue extends Model
{
    protected $fillable = [
        'definition_id', 'customer_id',
        'value_text', 'value_number', 'value_date', 'value_boolean', 'value_json',
    ];

    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:4',
            'value_date' => 'date',
            'value_boolean' => 'boolean',
            'value_json' => 'array',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'definition_id');
    }

    /**
     * Tipe göre normalleştirilmiş değeri döndürür.
     */
    public function typedValue(): string|int|float|bool|array|null
    {
        return match ($this->definition->type) {
            'integer' => $this->value_number !== null ? (int) $this->value_number : null,
            'decimal' => $this->value_number !== null ? (float) $this->value_number : null,
            'date' => $this->value_date !== null ? $this->value_date->toDateString() : null,
            'boolean' => $this->value_boolean,
            'multi_select' => $this->value_json,
            default => $this->value_text,
        };
    }
}
