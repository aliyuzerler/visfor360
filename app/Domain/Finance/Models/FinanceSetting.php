<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finans ayarı: formül onayları ve varsayımlar (ADR-0005).
 * pnl_formula_approved=false iken P&L toplamları üretilmez;
 * cash_formula_approved=false iken runway hesaplanmaz.
 */
/**
 * @property array<string, mixed>|null $value
 */
class FinanceSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function get(string $key): ?array
    {
        $setting = self::query()->where('key', $key)->first();

        return $setting?->value;
    }

    public static function put(string $key, array $value): self
    {
        $setting = self::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        return $setting;
    }
}
