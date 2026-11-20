<?php

namespace App\Domain\Checkups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sonuç: öneriler + kural izi (explainability) + deterministik hash.
 * Yalnız Opportunity ÖNERİSİ içerir; proje/task üretmez (Prompt 11 sınırı).
 *
 * @property CheckupSession $session
 * @property array<int, array<string, mixed>> $recommendations
 * @property array<int, array<string, mixed>> $rule_trace
 */
class CheckupResult extends Model
{
    protected $fillable = [
        'session_id', 'recommendations', 'rule_trace', 'engine_version', 'deterministic_hash',
    ];

    protected function casts(): array
    {
        return ['recommendations' => 'array', 'rule_trace' => 'array'];
    }

    /**
     * @return BelongsTo<CheckupSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CheckupSession::class, 'session_id');
    }
}
