<?php

namespace App\Domain\Checkups\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Check-Up şablonu (versiyonlu). Müşteri soru seti gelmediği için
 * is_example=true prototipler dışında kayıt üretilmez (ADR-0010).
 *
 * @property Collection<int, CheckupQuestion> $questions
 * @property Collection<int, CheckupRule> $rules
 * @property Carbon|null $archived_at
 */
class CheckupTemplate extends Model
{
    use Auditable, SoftDeletes;

    public const STATUS_ALLOWLIST = ['draft', 'published'];

    protected $attributes = ['version' => 1];

    protected $fillable = ['key', 'title', 'version', 'status', 'is_example', 'published_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'is_example' => 'boolean',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<CheckupQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(CheckupQuestion::class, 'template_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CheckupRule, $this>
     */
    public function rules(): HasMany
    {
        return $this->hasMany(CheckupRule::class, 'template_id')->orderBy('priority');
    }
}
