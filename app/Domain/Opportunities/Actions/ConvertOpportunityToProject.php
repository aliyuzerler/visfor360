<?php

namespace App\Domain\Opportunities\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStageHistory;
use App\Domain\Projects\Models\ServiceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Onaylı fırsat → proje (Prompt 13, ADR-0012).
 * Ön koşullar: status=approved + mutabakat notu dolu + henüz dönmemiş +
 * service_key eşleşen aktif ServiceType (departman/pipeline kaynağı).
 * Tüm işlem tek DB transaction; hata → rollback, fırsat yarım kalmaz.
 * Idempotent: converted_project_id UNIQUE; ikinci istek mevcut projeyi döner.
 */
final class ConvertOpportunityToProject
{
    public function convert(Opportunity $opportunity, ?User $actor): Project
    {
        // Idempotency: zaten dönüştürülmüşse aynı projeyi döner (çift tıklama/retry)
        if ($opportunity->converted_project_id !== null) {
            return Project::query()->findOrFail($opportunity->converted_project_id);
        }

        if ($opportunity->status !== 'approved') {
            throw ValidationException::withMessages(['convert' => __('Yalnız onaylı fırsat projeye dönüştürülebilir.')]);
        }

        if (! $opportunity->hasApprovedAgreement()) {
            throw ValidationException::withMessages(['convert' => __('Müşteri mutabakat kaydı eksik; onay mutabakatın yerine geçmez.')]);
        }

        $serviceType = ServiceType::query()->where('key', $opportunity->service_key)->where('is_active', true)->first();
        if ($serviceType === null) {
            throw ValidationException::withMessages([
                'convert' => __('Fırsatın hizmet anahtarına karşılık gelen aktif hizmet tanımı yok (mapping müşteri kararı, ADR-0012).'),
            ]);
        }

        $firstStage = $serviceType->pipeline->stages->first(fn ($stage) => $stage->is_active);

        return DB::transaction(function () use ($opportunity, $actor, $serviceType, $firstStage): Project {
            $project = Project::create([
                'name' => $opportunity->title,
                'description' => __('Fırsat #:id dönüşümü — tespit: :reason', [
                    'id' => $opportunity->id,
                    'reason' => (string) $opportunity->detection_reason,
                ]),
                'customer_id' => $opportunity->customer_id,
                'service_type_id' => $serviceType->id,
                'department_id' => $serviceType->department_id,
                'responsible_user_id' => $opportunity->responsible_user_id,
                'pipeline_stage_id' => $firstStage?->id,
                'start_date' => now()->toDateString(),
            ]);

            if ($firstStage !== null) {
                ProjectStageHistory::create([
                    'project_id' => $project->id,
                    'from_stage_id' => null,
                    'to_stage_id' => $firstStage->id,
                    'actor_id' => $actor?->id,
                    'actor_name' => $actor?->name,
                    'transitioned_at' => now(),
                    'meta' => ['initial' => true, 'source' => 'opportunity_conversion'],
                ]);
            }

            $opportunity->update([
                'converted_project_id' => $project->id,
                'converted_at' => now(),
                'converted_by' => $actor?->id,
            ]);

            app(AuditLogger::class)->log('opportunity_converted', $opportunity, ['status' => 'approved'], [
                'project_id' => $project->id,
                'converted_by' => $actor?->id,
            ]);

            return $project;
        });
    }
}
