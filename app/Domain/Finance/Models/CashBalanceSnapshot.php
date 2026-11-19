<?php

namespace App\Domain\Finance\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Nakit dengesi snapshot'ı: varsayımlar açıkça saklanır (Prompt 08 deseni).
 * needs_configuration durumunda tüm değerler null'dır.
 *
 * @property Carbon $calculated_at
 */
class CashBalanceSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'as_of_date', 'cash_balance', 'monthly_burn', 'runway_months',
        'currency_code', 'assumptions', 'formula_version', 'status',
        'actor_id', 'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'as_of_date' => 'date',
            'cash_balance' => 'decimal:4',
            'monthly_burn' => 'decimal:4',
            'runway_months' => 'decimal:2',
            'assumptions' => 'array',
            'calculated_at' => 'datetime',
        ];
    }
}
