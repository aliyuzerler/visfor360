<?php

namespace App\Domain\Checkups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Soru: tip allowlist'i müşteri kararına göre genişletilir. config jsonb:
 * options (choices), min/max (number), unit.
 *
 * @property CheckupTemplate $template
 */
class CheckupQuestion extends Model
{
    public const TYPE_ALLOWLIST = ['text', 'number', 'boolean', 'single_choice', 'multi_choice'];

    protected $fillable = ['template_id', 'key', 'label', 'type', 'required', 'sort_order', 'config'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'config' => 'array'];
    }

    /**
     * @return BelongsTo<CheckupTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CheckupTemplate::class, 'template_id');
    }
}
