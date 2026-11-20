<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Partners\Models\PartnerAgreement;
use App\Domain\Partners\Models\PartnerCommission;
use App\Domain\Partners\Models\PartnerWork;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Komisyon hesabı (Prompt 14, ADR-0013).
 * - YALNIZ approved agreement ile hesaplanır; onaysızsa red.
 * - Örnek formüller (fixture testli): fixed_amount (tutar), percent_of_value
 *   (matrah * oran/100, 2 hane round). KDV/matrah/vade uydurulmaz.
 * - financial_entries ÜRETMEZ (çift kayıt engeli).
 */
final class CommissionService
{
    public const ENGINE_VERSION = 'partner_commission.v1';

    public function calculate(PartnerWork $work, ?User $actor): PartnerCommission
    {
        $agreement = PartnerAgreement::query()
            ->where('partner_id', $work->partner_id)
            ->orderByDesc('id')
            ->first();

        if ($agreement === null || ! $agreement->isApproved()) {
            throw ValidationException::withMessages([
                'commission' => __('Onaylı komisyon anlaşması yok; hesaplama engellendi.'),
            ]);
        }

        if (! in_array($agreement->commission_type, PartnerAgreement::TYPE_ALLOWLIST, true)) {
            throw ValidationException::withMessages(['commission' => __('Geçersiz komisyon tipi.')]);
        }

        $base = (float) $work->value;
        $rate = (float) $agreement->commission_rate;

        // Allowlist kontrolü yapıldı; yalnız iki örnek formül (ADR-0013)
        $amount = $agreement->commission_type === 'fixed_amount'
            ? round($rate, 2)
            : round($base * $rate / 100, 2);

        $commission = PartnerCommission::create([
            'partner_work_id' => $work->id,
            'agreement_id' => $agreement->id,
            'base_amount' => $base,
            'rate' => $rate,
            'calculated_amount' => $amount,
            'currency_code' => $agreement->currency_code,
            'status' => 'calculated',
            'formula_version' => self::ENGINE_VERSION,
            'meta' => [
                'formula' => $agreement->commission_type,
                'note' => 'Örnek formül — müşteri örnek hesaplarıyla kalıcılaşır (ADR-0013)',
            ],
            'created_by' => $actor?->id,
        ]);

        app(AuditLogger::class)->log('commission_calculated', $commission, null, [
            'amount' => $amount,
            'formula_version' => self::ENGINE_VERSION,
        ]);

        return $commission;
    }
}
