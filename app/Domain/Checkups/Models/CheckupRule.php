<?php

namespace App\Domain\Checkups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Deklaratif kural: condition_groups = OR of groups; her group = AND of
 * conditions {question_key, operator, value}. Operator allowlist'i motor
 * tarafında zorlanır; kullanıcı girdisi hiçbir koşulda kod/SQL olarak çalışmaz.
 *
 * @property CheckupTemplate $template
 */
class CheckupRule extends Model
{
    protected $fillable = [
        'template_id', 'label', 'priority', 'condition_groups',
        'recommendation_service_key', 'recommendation_label', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['condition_groups' => 'array', 'is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<CheckupTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CheckupTemplate::class, 'template_id');
    }
}
