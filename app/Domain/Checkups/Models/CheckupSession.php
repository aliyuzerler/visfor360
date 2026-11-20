<?php

namespace App\Domain\Checkups\Models;

use App\Domain\Customers\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Check-Up oturumu. Tamamlanınca questions/rules/answers snapshot'ları
 * saklanır: şablon sonradan değişse bile sonuç değişmez (Prompt 11 madde 3).
 *
 * @property CheckupTemplate $template
 * @property Carbon|null $completed_at
 */
class CheckupSession extends Model
{
    public const STATUS_ALLOWLIST = ['draft', 'completed'];

    protected $fillable = [
        'template_id', 'template_version', 'customer_id', 'status',
        'answers_snapshot', 'questions_snapshot', 'rules_snapshot',
        'started_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers_snapshot' => 'array',
            'questions_snapshot' => 'array',
            'rules_snapshot' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CheckupTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CheckupTemplate::class, 'template_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<CheckupResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(CheckupResult::class, 'session_id');
    }
}
