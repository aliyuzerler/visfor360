<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Finance\Models\CashBalanceSnapshot;
use App\Domain\Finance\Models\FinanceSetting;
use App\Domain\Finance\Models\FinancialEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Finans hesaplama servisi (ADR-0005).
 * P&L ve nakit akışı AYRI metriklerdir; ayrı raporlarda sunulur.
 * Formül onayı yoksa gerçek toplam/runway üretilmez — needs_configuration
 * snapshot'ı yazılır (uydurma finansal sayı koruması). Onaylıyorsa yalnız
 * DEMO formül (baseline: gelir-gider; burn = son 3 ay gider ortalaması)
 * fixture testleri ile doğrulanır; müşteri örnek hesapları gelince değişir.
 */
final class FinanceCalculationService
{
    public const DEMO_PNL_VERSION = 'baseline_pnl.v1';

    public const DEMO_CASH_VERSION = 'baseline_runway.v1';

    public function pnlApproved(): bool
    {
        return (FinanceSetting::get('pnl_formula_approved')['approved'] ?? false) === true;
    }

    public function cashApproved(): bool
    {
        return (FinanceSetting::get('cash_formula_approved')['approved'] ?? false) === true;
    }

    /**
     * P&L özeti (ayrı rapor). Onaysızsa null değerler + needs_configuration.
     *
     * @return array<string, mixed>
     */
    public function pnlSummary(): array
    {
        if (! $this->pnlApproved()) {
            return [
                'status' => 'needs_configuration',
                'total_income' => null,
                'total_expense' => null,
                'net' => null,
                'currency_code' => 'TRY',
                'formula_version' => null,
            ];
        }

        $income = FinancialEntry::query()
            ->where('direction', 'income')
            ->where('entry_type', 'actual')
            ->whereIn('status', ['approved'])
            ->sum('amount');
        $expense = FinancialEntry::query()
            ->where('direction', 'expense')
            ->where('entry_type', 'actual')
            ->whereIn('status', ['approved'])
            ->sum('amount');

        return [
            'status' => 'calculated',
            'total_income' => (float) $income,
            'total_expense' => (float) $expense,
            'net' => (float) $income - (float) $expense,
            'currency_code' => 'TRY',
            'formula_version' => self::DEMO_PNL_VERSION,
        ];
    }

    /**
     * Nakit akışı + runway (ayrı rapor). Varsayımlar açıkça saklanır.
     */
    public function cashSnapshot(?User $actor): CashBalanceSnapshot
    {
        return DB::transaction(function () use ($actor): CashBalanceSnapshot {
            if (! $this->cashApproved()) {
                return CashBalanceSnapshot::create([
                    'as_of_date' => now()->toDateString(),
                    'cash_balance' => null,
                    'monthly_burn' => null,
                    'runway_months' => null,
                    'currency_code' => 'TRY',
                    'assumptions' => ['reason' => 'Nakit formülü müşteri onayı bekliyor (ADR-0005)'],
                    'formula_version' => null,
                    'status' => 'needs_configuration',
                    'actor_id' => $actor?->id,
                    'calculated_at' => now(),
                ]);
            }

            // DEMO formül (fixture testli): başlangıç nakit ayar + gerçekleşen net akış
            $settings = FinanceSetting::get('cash_formula_config') ?? [];
            $startingCash = (float) ($settings['starting_cash'] ?? 0);
            $lookbackMonths = max(1, min(12, (int) ($settings['burn_lookback_months'] ?? 3)));
            $horizonMonths = max(1, min(24, (int) ($settings['forecast_horizon_months'] ?? 12)));
            $assumptions = [
                'starting_cash' => $startingCash,
                'burn_lookback_months' => $lookbackMonths,
                'forecast_horizon_months' => $horizonMonths,
                'currency' => 'TRY',
                'note' => 'DEMO formül — müşteri örnek hesaplarıyla doğrulanır',
            ];

            $income = (float) FinancialEntry::query()
                ->where('direction', 'income')->where('entry_type', 'actual')
                ->whereIn('status', ['approved'])
                ->whereBetween('entry_date', [now()->subMonths($lookbackMonths)->toDateString(), now()->toDateString()])
                ->sum('amount');
            $expense = (float) FinancialEntry::query()
                ->where('direction', 'expense')->where('entry_type', 'actual')
                ->whereIn('status', ['approved'])
                ->whereBetween('entry_date', [now()->subMonths($lookbackMonths)->toDateString(), now()->toDateString()])
                ->sum('amount');

            $monthlyBurn = round($expense / $lookbackMonths, 4);
            $cashBalance = round($startingCash + $income - $expense, 4);
            $runway = $monthlyBurn > 0 ? round($cashBalance / $monthlyBurn, 2) : null;

            return CashBalanceSnapshot::create([
                'as_of_date' => now()->toDateString(),
                'cash_balance' => $cashBalance,
                'monthly_burn' => $monthlyBurn,
                'runway_months' => $runway,
                'currency_code' => 'TRY',
                'assumptions' => $assumptions,
                'formula_version' => self::DEMO_CASH_VERSION,
                'status' => 'calculated',
                'actor_id' => $actor?->id,
                'calculated_at' => now(),
            ]);
        });
    }
}
